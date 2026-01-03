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
 * Helper for fetching and caching moon phase data.
 *
 * @since  1.0.2
 */
class MoonPhaseHelper
{
    /**
     * USNO API base URL for moon phases.
     *
     * @var    string
     * @since  1.0.2
     */
    private const API_URL = 'https://aa.usno.navy.mil/api/moon/phases/year';

    /**
     * Map of API phase names to short codes.
     *
     * @var    array<string, string>
     * @since  1.0.2
     */
    private const PHASE_MAP = [
        'New Moon' => 'new',
        'First Quarter' => '1q',
        'Full Moon' => 'full',
        'Last Quarter' => '2q',
    ];

    /**
     * Ensure moon phases are cached for the given years.
     *
     * @param   DatabaseInterface  $db     Database connection.
     * @param   array<int>         $years  Years to ensure data for.
     *
     * @return  void
     *
     * @since   1.0.2
     */
    public function ensurePhasesForYears(DatabaseInterface $db, array $years): void
    {
        $years = array_unique($years);

        foreach ($years as $year) {
            if ($this->isYearCached($db, (int) $year)) {
                continue;
            }

            $phases = $this->fetchYearFromApi((int) $year);

            if ($phases !== null) {
                $this->storePhasesForYear($db, $phases);
            }
        }
    }

    /**
     * Check if any records for the given year exist in the cache.
     *
     * @param   DatabaseInterface  $db    Database connection.
     * @param   int                $year  Year to check.
     *
     * @return  bool
     *
     * @since   1.0.2
     */
    public function isYearCached(DatabaseInterface $db, int $year): bool
    {
        $startOfYear = $year . '-01-01T00:00:00Z';
        $endOfYear = $year . '-12-31T23:59:59Z';

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('TideMoonPhases'))
            ->where($db->quoteName('PhaseDT') . ' >= ' . $db->quote($startOfYear))
            ->where($db->quoteName('PhaseDT') . ' <= ' . $db->quote($endOfYear));

        $db->setQuery($query);

        return (int) $db->loadResult() > 0;
    }

    /**
     * Fetch moon phases for a year from the USNO API.
     *
     * @param   int  $year  Year to fetch.
     *
     * @return  array<int, array{PhaseDT: string, Phase: string}>|null  Array of phases or null on error.
     *
     * @since   1.0.2
     */
    public function fetchYearFromApi(int $year): ?array
    {
        /*
        If you plan to write your own form or script to access the APIs, 
        we encourage you to use the ID parameter in your API call. 
        Using this parameter is optional. However, the use of user IDs 
        allows us to keep track of how many unique users we have, and 
        helps us justify our work on the web.
        */
        $url = self::API_URL . '?ID=YSTideIE' . '&year=' . $year;

        try {
            $http = HttpFactory::getHttp();
            $response = $http->get($url, [], 15);

            if ($response->code !== 200) {
                Log::add(
                    Text::sprintf('MOD_YSTIDES_ERR_MOON_API', 'HTTP ' . $response->code) . ' URL: ' . $url,
                    Log::ERROR,
                    'mod_ystides'
                );

                return null;
            }

            $data = json_decode($response->body, true);

            if (!is_array($data) || empty($data['phasedata'])) {
                Log::add(
                    Text::sprintf('MOD_YSTIDES_ERR_MOON_API', 'Invalid response format') . ' URL: ' . $url,
                    Log::ERROR,
                    'mod_ystides'
                );

                return null;
            }

            return $this->parseApiResponse($data['phasedata']);
        } catch (\Exception $e) {
            Log::add(
                Text::sprintf('MOD_YSTIDES_ERR_MOON_API', $e->getMessage()) . ' URL: ' . $url,
                Log::ERROR,
                'mod_ystides'
            );

            return null;
        }
    }

    /**
     * Parse API response and convert to database format.
     *
     * @param   array  $phaseData  Raw phase data from API.
     *
     * @return  array<int, array{PhaseDT: string, Phase: string}>
     *
     * @since   1.0.2
     */
    private function parseApiResponse(array $phaseData): array
    {
        $phases = [];

        foreach ($phaseData as $entry) {
            $phaseName = $entry['phase'] ?? '';
            $phaseCode = $this->mapApiPhase($phaseName);

            if ($phaseCode === null) {
                continue;
            }

            $year = (int) ($entry['year'] ?? 0);
            $month = (int) ($entry['month'] ?? 0);
            $day = (int) ($entry['day'] ?? 0);
            $time = $entry['time'] ?? '00:00';

            if ($year === 0 || $month === 0 || $day === 0) {
                continue;
            }

            // Build full UTC datetime
            $phaseDT = sprintf('%04d-%02d-%02dT%s:00Z', $year, $month, $day, $time);

            $phases[] = [
                'PhaseDT' => $phaseDT,
                'Phase' => $phaseCode,
            ];
        }

        return $phases;
    }

    /**
     * Map API phase name to short code.
     *
     * @param   string  $apiPhase  Phase name from API.
     *
     * @return  string|null  Short code or null if unknown.
     *
     * @since   1.0.2
     */
    public function mapApiPhase(string $apiPhase): ?string
    {
        return self::PHASE_MAP[$apiPhase] ?? null;
    }

    /**
     * Store phase records into the database.
     *
     * @param   DatabaseInterface  $db      Database connection.
     * @param   array              $phases  Array of phase records.
     *
     * @return  void
     *
     * @since   1.0.2
     */
    public function storePhasesForYear(DatabaseInterface $db, array $phases): void
    {
        if (empty($phases)) {
            return;
        }

        foreach ($phases as $phase) {
            $sql = sprintf(
                'INSERT OR IGNORE INTO TideMoonPhases (PhaseDT, Phase) VALUES (%s, %s)',
                $db->quote($phase['PhaseDT']),
                $db->quote($phase['Phase'])
            );

            $db->setQuery($sql);
            $db->execute();
        }
    }

    /**
     * Get phases for a date range, keyed by date (YYYY-MM-DD).
     *
     * @param   DatabaseInterface  $db         Database connection.
     * @param   string             $startDate  Start date (YYYY-MM-DD).
     * @param   string             $endDate    End date (YYYY-MM-DD).
     *
     * @return  array<string, string>  Associative array [date => phase].
     *
     * @since   1.0.2
     */
    public function getPhasesForRange(DatabaseInterface $db, string $startDate, string $endDate): array
    {
        // Match on date portion of PhaseDT
        $startDT = $startDate . 'T00:00:00Z';
        $endDT = $endDate . 'T23:59:59Z';

        $query = $db->getQuery(true)
            ->select([$db->quoteName('PhaseDT'), $db->quoteName('Phase')])
            ->from($db->quoteName('TideMoonPhases'))
            ->where($db->quoteName('PhaseDT') . ' >= ' . $db->quote($startDT))
            ->where($db->quoteName('PhaseDT') . ' <= ' . $db->quote($endDT))
            ->order($db->quoteName('PhaseDT') . ' ASC');

        $db->setQuery($query);
        $rows = $db->loadAssocList();

        $result = [];

        foreach ($rows as $row) {
            // Extract date portion from PhaseDT
            $date = substr($row['PhaseDT'], 0, 10);
            $result[$date] = $row['Phase'];
        }

        return $result;
    }
}
