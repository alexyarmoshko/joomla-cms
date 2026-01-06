<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_ystides
 *
 * @copyright   (C) 2025 YSTides
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Ystides\Site\Helper;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for fetching and caching Met Éireann weather warnings.
 *
 * @since  1.0.3
 */
class WeatherWarningHelper
{
    /**
     * Met Éireann warnings RSS feed URL.
     *
     * @var    string
     * @since  1.0.3
     */
    private const RSS_URL = 'https://www.met.ie/warningsxml/rss.xml';

    /**
     * Meta key for storing Last-Modified header.
     *
     * @var    string
     * @since  1.0.3
     */
    private const META_KEY_LAST_MODIFIED = 'LastModified';

    /**
     * Map of severity keywords to awareness levels and icons.
     * Higher level = more severe.
     *
     * @var    array<string, array{level: int, icon: string}>
     * @since  1.0.3
     */
    private const SEVERITY_MAP = [
        'Minor' => ['level' => 1, 'icon' => 'green'],
        'Advisory' => ['level' => 1, 'icon' => 'green'],
        'Moderate' => ['level' => 2, 'icon' => 'yellow'],
        'Severe' => ['level' => 3, 'icon' => 'orange'],
        'Extreme' => ['level' => 4, 'icon' => 'red'],
    ];

    /**
     * Ensure weather warnings are updated if the feed has changed.
     *
     * @param   DatabaseInterface  $db  Database connection.
     *
     * @return  void
     *
     * @since   1.0.3
     */
    public function ensureWarningsUpdated(DatabaseInterface $db): void
    {
        try {
            $http = HttpFactory::getHttp();

            // Check if feed has been modified since last fetch
            $cachedLastModified = $this->getCachedLastModified($db);
            $headers = [];

            if ($cachedLastModified !== null) {
                $headers['If-Modified-Since'] = $cachedLastModified;
            }

            // HEAD request to check Last-Modified
            $headResponse = $http->head(self::RSS_URL, $headers, 10);

            if ($headResponse->code === 304) {
                // Not modified, use cached data
                return;
            }

            if ($headResponse->code !== 200) {
                Log::add(
                    Text::sprintf('MOD_YSTIDES_ERR_WARNING_API', 'HEAD HTTP ' . $headResponse->code),
                    Log::WARNING,
                    'mod_ystides'
                );
                return;
            }

            // Get Last-Modified from response (headers can be arrays)
            $lastModifiedHeader = $headResponse->headers['Last-Modified'] ?? $headResponse->headers['last-modified'] ?? null;
            $lastModified = is_array($lastModifiedHeader) ? ($lastModifiedHeader[0] ?? null) : $lastModifiedHeader;

            // If we have a cached value and it matches, skip fetch
            if ($cachedLastModified !== null && $lastModified === $cachedLastModified) {
                return;
            }

            // Fetch the actual RSS feed
            $this->fetchAndCacheWarnings($db, $http, $lastModified);
        } catch (\Exception $e) {
            Log::add(
                Text::sprintf('MOD_YSTIDES_ERR_WARNING_API', $e->getMessage()),
                Log::ERROR,
                'mod_ystides'
            );
        }
    }

    /**
     * Get cached Last-Modified value.
     *
     * @param   DatabaseInterface  $db  Database connection.
     *
     * @return  string|null
     *
     * @since   1.0.3
     */
    private function getCachedLastModified(DatabaseInterface $db): ?string
    {
        $query = $db->getQuery(true)
            ->select($db->quoteName('Value'))
            ->from($db->quoteName('WeatherWarningsMeta'))
            ->where($db->quoteName('Key') . ' = ' . $db->quote(self::META_KEY_LAST_MODIFIED));

        $db->setQuery($query);
        $result = $db->loadResult();

        return $result ?: null;
    }

    /**
     * Store Last-Modified value in cache.
     *
     * @param   DatabaseInterface  $db             Database connection.
     * @param   string|null        $lastModified   Last-Modified header value.
     *
     * @return  void
     *
     * @since   1.0.3
     */
    private function setCachedLastModified(DatabaseInterface $db, ?string $lastModified): void
    {
        if ($lastModified === null) {
            return;
        }

        $sql = sprintf(
            'INSERT INTO WeatherWarningsMeta (Key, Value) VALUES (%s, %s) ON CONFLICT(Key) DO UPDATE SET Value = excluded.Value',
            $db->quote(self::META_KEY_LAST_MODIFIED),
            $db->quote($lastModified)
        );

        $db->setQuery($sql);
        $db->execute();
    }

    /**
     * Fetch RSS feed and cache warnings.
     *
     * @param   DatabaseInterface  $db            Database connection.
     * @param   object             $http          HTTP client.
     * @param   string|null        $lastModified  Last-Modified header value.
     *
     * @return  void
     *
     * @since   1.0.3
     */
    private function fetchAndCacheWarnings(DatabaseInterface $db, $http, ?string $lastModified): void
    {
        $response = $http->get(self::RSS_URL, [], 15);

        if ($response->code !== 200) {
            Log::add(
                Text::sprintf('MOD_YSTIDES_ERR_WARNING_API', 'GET HTTP ' . $response->code),
                Log::WARNING,
                'mod_ystides'
            );
            return;
        }

        $rssContent = $response->body;

        // Fix encoding issue - Met.ie declares UTF-16 but sends UTF-8
        $rssContent = preg_replace('/encoding="[^"]*"/', 'encoding="UTF-8"', $rssContent);

        $rssItems = $this->parseRssFeed($rssContent);

        if (empty($rssItems)) {
            // No warnings in feed, clear all cached warnings
            $db->setQuery('DELETE FROM WeatherWarnings');
            $db->execute();
            $this->setCachedLastModified($db, $lastModified);
            return;
        }

        // Collect current identifiers
        $currentIdentifiers = [];
        $retrievedAt = gmdate('Y-m-d\TH:i:s\Z');

        foreach ($rssItems as $item) {
            $capUrl = $item['link'] ?? null;

            if (empty($capUrl)) {
                continue;
            }

            $capData = $this->fetchCapFile($http, $capUrl);

            if ($capData === null) {
                continue;
            }

            $currentIdentifiers[] = $capData['Identifier'];
            $this->upsertWarning($db, $capData, $retrievedAt);
        }

        // Delete warnings not in current feed
        if (!empty($currentIdentifiers)) {
            $this->deleteOldWarnings($db, $currentIdentifiers);
        }

        $this->setCachedLastModified($db, $lastModified);
    }

    /**
     * Parse RSS feed and extract items.
     *
     * @param   string  $rssContent  RSS XML content.
     *
     * @return  array<int, array<string, string>>
     *
     * @since   1.0.3
     */
    private function parseRssFeed(string $rssContent): array
    {
        $items = [];

        try {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($rssContent);

            if ($xml === false) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                Log::add(
                    Text::sprintf('MOD_YSTIDES_ERR_WARNING_API', 'RSS parse error: ' . ($errors[0]->message ?? 'unknown')),
                    Log::WARNING,
                    'mod_ystides'
                );
                return [];
            }

            foreach ($xml->channel->item as $item) {
                $items[] = [
                    'title' => (string) $item->title,
                    'link' => (string) $item->link,
                    'description' => (string) $item->description,
                    'category' => (string) $item->category,
                    'guid' => (string) $item->guid,
                    'pubDate' => (string) $item->pubDate,
                ];
            }
        } catch (\Exception $e) {
            Log::add(
                Text::sprintf('MOD_YSTIDES_ERR_WARNING_API', 'RSS exception: ' . $e->getMessage()),
                Log::WARNING,
                'mod_ystides'
            );
        }

        return $items;
    }

    /**
     * Fetch and parse CAP XML file.
     *
     * @param   object  $http    HTTP client.
     * @param   string  $capUrl  CAP file URL.
     *
     * @return  array<string, mixed>|null
     *
     * @since   1.0.3
     */
    private function fetchCapFile($http, string $capUrl): ?array
    {
        try {
            $response = $http->get($capUrl, [], 15);

            if ($response->code !== 200) {
                return null;
            }

            return $this->parseCapXml($response->body);
        } catch (\Exception $e) {
            Log::add(
                Text::sprintf('MOD_YSTIDES_ERR_WARNING_API', 'CAP fetch error: ' . $e->getMessage()),
                Log::WARNING,
                'mod_ystides'
            );
            return null;
        }
    }

    /**
     * Parse CAP XML content.
     *
     * @param   string  $capContent  CAP XML content.
     *
     * @return  array<string, mixed>|null
     *
     * @since   1.0.3
     */
    private function parseCapXml(string $capContent): ?array
    {
        try {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($capContent);

            if ($xml === false) {
                libxml_clear_errors();
                return null;
            }

            // Register CAP namespace
            $xml->registerXPathNamespace('cap', 'urn:oasis:names:tc:emergency:cap:1.2');

            $identifier = (string) $xml->identifier;
            $info = $xml->info;

            if (empty($identifier) || empty($info)) {
                return null;
            }

            $event = (string) $info->event;
            $severity = (string) $info->severity;
            $onset = (string) $info->onset;
            $expires = (string) $info->expires;
            $headline = (string) $info->headline;
            $description = (string) $info->description;
            $category = (string) $info->category;

            // Extract awareness_level from parameters
            $awarenessLevel = 0;
            foreach ($info->parameter as $param) {
                if ((string) $param->valueName === 'awareness_level') {
                    $value = (string) $param->value;
                    // Format: "2; yellow; Moderate"
                    if (preg_match('/^(\d+);/', $value, $matches)) {
                        $awarenessLevel = (int) $matches[1];
                    }
                    break;
                }
            }

            // Collect area codes (both FIPS and EMMA_ID)
            $areaCodes = [];
            foreach ($info->area->geocode as $geocode) {
                $valueName = (string) $geocode->valueName;
                if ($valueName === 'FIPS' || $valueName === 'EMMA_ID') {
                    $areaCodes[] = (string) $geocode->value;
                }
            }

            // Determine category - use 'Marine' for marine warnings, check event for Small Craft
            $warningCategory = 'Weather';
            if (stripos($event, 'Small Craft') !== false) {
                $warningCategory = 'Marine';
            } elseif ($category === 'Met') {
                // Check if it's a marine warning by looking at area codes (sea areas start with EI8)
                foreach ($areaCodes as $code) {
                    if (preg_match('/^EI8\d{2}$/', $code)) {
                        $warningCategory = 'Marine';
                        break;
                    }
                }
            }

            return [
                'Identifier' => $identifier,
                'Event' => $event,
                'Category' => $warningCategory,
                'Headline' => $headline,
                'Description' => $description,
                'Severity' => $severity,
                'AwarenessLevel' => $awarenessLevel,
                'Onset' => $this->normalizeDateTime($onset),
                'Expires' => $this->normalizeDateTime($expires),
                'AreaCodes' => implode(',', $areaCodes),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Normalize datetime to ISO 8601 format.
     *
     * @param   string  $datetime  Datetime string.
     *
     * @return  string
     *
     * @since   1.0.3
     */
    private function normalizeDateTime(string $datetime): string
    {
        try {
            $dt = new \DateTime($datetime);
            $dt->setTimezone(new \DateTimeZone('UTC'));
            return $dt->format('Y-m-d\TH:i:s\Z');
        } catch (\Exception $e) {
            return $datetime;
        }
    }

    /**
     * Insert or update a warning in the database.
     *
     * @param   DatabaseInterface  $db           Database connection.
     * @param   array              $capData      Parsed CAP data.
     * @param   string             $retrievedAt  Retrieval timestamp.
     *
     * @return  void
     *
     * @since   1.0.3
     */
    private function upsertWarning(DatabaseInterface $db, array $capData, string $retrievedAt): void
    {
        $sql = sprintf(
            'INSERT INTO WeatherWarnings (Identifier, Event, Category, Headline, Description, Severity, AwarenessLevel, Onset, Expires, AreaCodes, RetrievedAt)
             VALUES (%s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %s)
             ON CONFLICT(Identifier) DO UPDATE SET
                Event = excluded.Event,
                Category = excluded.Category,
                Headline = excluded.Headline,
                Description = excluded.Description,
                Severity = excluded.Severity,
                AwarenessLevel = excluded.AwarenessLevel,
                Onset = excluded.Onset,
                Expires = excluded.Expires,
                AreaCodes = excluded.AreaCodes,
                RetrievedAt = excluded.RetrievedAt',
            $db->quote($capData['Identifier']),
            $db->quote($capData['Event']),
            $db->quote($capData['Category']),
            $db->quote($capData['Headline']),
            $db->quote($capData['Description']),
            $db->quote($capData['Severity']),
            (int) $capData['AwarenessLevel'],
            $db->quote($capData['Onset']),
            $db->quote($capData['Expires']),
            $db->quote($capData['AreaCodes']),
            $db->quote($retrievedAt)
        );

        $db->setQuery($sql);
        $db->execute();
    }

    /**
     * Delete warnings not in the current feed.
     *
     * @param   DatabaseInterface  $db                  Database connection.
     * @param   array              $currentIdentifiers  Identifiers in current feed.
     *
     * @return  void
     *
     * @since   1.0.3
     */
    private function deleteOldWarnings(DatabaseInterface $db, array $currentIdentifiers): void
    {
        $quotedIds = array_map([$db, 'quote'], $currentIdentifiers);
        $sql = 'DELETE FROM WeatherWarnings WHERE Identifier NOT IN (' . implode(',', $quotedIds) . ')';
        $db->setQuery($sql);
        $db->execute();
    }

    /**
     * Get warnings applicable to a station for a date range.
     *
     * @param   DatabaseInterface  $db          Database connection.
     * @param   string             $stationId   Station identifier.
     * @param   string             $startDate   Start date (YYYY-MM-DD).
     * @param   string             $endDate     End date (YYYY-MM-DD).
     *
     * @return  array<string, array{smallCraft: string|null, weather: string|null}>  Warnings keyed by date.
     *
     * @since   1.0.3
     */
    public function getWarningsForStation(DatabaseInterface $db, string $stationId, string $startDate, string $endDate): array
    {
        // Get station area codes
        $stationAreaCodes = $this->getStationAreaCodes($db, $stationId);

        if (empty($stationAreaCodes)) {
            return [];
        }

        // Query warnings that overlap with the date range and match station area codes
        $startDT = $startDate . 'T00:00:00Z';
        $endDT = $endDate . 'T23:59:59Z';

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('WeatherWarnings'))
            ->where($db->quoteName('Onset') . ' <= ' . $db->quote($endDT))
            ->where($db->quoteName('Expires') . ' >= ' . $db->quote($startDT))
            ->where('(' . $db->quoteName('Category') . ' = ' . $db->quote('Marine') . ' OR ' .
                $db->quoteName('Event') . ' LIKE ' . $db->quote('%Small Craft%') . ')')
            ->order($db->quoteName('AwarenessLevel') . ' DESC');

        $db->setQuery($query);
        $warnings = $db->loadAssocList();

        if (empty($warnings)) {
            return [];
        }

        // Build result keyed by date
        $result = [];
        $startDateObj = new \DateTime($startDate);
        $endDateObj = new \DateTime($endDate);
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startDateObj, $interval, $endDateObj->modify('+1 day'));

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $result[$dateStr] = [
                'smallCraft' => null,
                'weather' => null,
                'maxLevel' => 0,
            ];
        }

        // Process each warning
        foreach ($warnings as $warning) {
            $warningAreaCodes = explode(',', $warning['AreaCodes']);

            // Check if any station area code matches any warning area code
            $matches = array_intersect($stationAreaCodes, $warningAreaCodes);

            if (empty($matches)) {
                continue;
            }

            // Determine which dates this warning applies to
            $onsetDate = substr($warning['Onset'], 0, 10);
            $expiresDate = substr($warning['Expires'], 0, 10);

            foreach ($result as $dateStr => &$dayData) {
                if ($dateStr >= $onsetDate && $dateStr <= $expiresDate) {
                    $isSmallCraft = stripos($warning['Event'], 'Small Craft') !== false;
                    $icon = $this->getIconForSeverity($warning['Severity']);
                    $level = (int) $warning['AwarenessLevel'];

                    if ($isSmallCraft) {
                        // Small Craft warnings use their own icon
                        if ($dayData['smallCraft'] === null) {
                            $dayData['smallCraft'] = 'small-craft';
                        }
                    } else {
                        // Weather warnings - track highest severity
                        if ($level > $dayData['maxLevel']) {
                            $dayData['weather'] = $icon;
                            $dayData['maxLevel'] = $level;
                        }
                    }
                }
            }
        }

        // Clean up maxLevel from result
        foreach ($result as &$dayData) {
            unset($dayData['maxLevel']);
        }

        return $result;
    }

    /**
     * Get area codes for a station.
     *
     * @param   DatabaseInterface  $db         Database connection.
     * @param   string             $stationId  Station identifier.
     *
     * @return  array<int, string>
     *
     * @since   1.0.3
     */
    private function getStationAreaCodes(DatabaseInterface $db, string $stationId): array
    {
        $query = $db->getQuery(true)
            ->select($db->quoteName('AreaCodes'))
            ->from($db->quoteName('TideStations'))
            ->where($db->quoteName('StationID') . ' = ' . $db->quote($stationId));

        $db->setQuery($query);
        $areaCodes = $db->loadResult();

        if (empty($areaCodes)) {
            return [];
        }

        return array_map('trim', explode(',', $areaCodes));
    }

    /**
     * Get icon name for a severity level.
     *
     * @param   string  $severity  Severity text.
     *
     * @return  string
     *
     * @since   1.0.3
     */
    private function getIconForSeverity(string $severity): string
    {
        return self::SEVERITY_MAP[$severity]['icon'] ?? 'yellow';
    }

    /**
     * Get the highest severity warning icon for display, considering priority.
     * Small Craft is shown if present, otherwise highest weather warning.
     *
     * @param   array  $dayWarning  Day warning data with 'smallCraft' and 'weather' keys.
     *
     * @return  string|null
     *
     * @since   1.0.3
     */
    public static function getPrimaryWarningIcon(array $dayWarning): ?string
    {
        // Small Craft takes priority
        if (!empty($dayWarning['smallCraft'])) {
            return $dayWarning['smallCraft'];
        }

        return $dayWarning['weather'] ?? null;
    }
}
