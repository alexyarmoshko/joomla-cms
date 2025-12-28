<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_ystides
 *
 * @copyright   (C) 2025 YSTides
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Ystides\Site\Helper;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handles fetching tide data from ERDDAP and caching it into SQLite.
 *
 * @since  1.0.1
 */
class TideDataFetcher
{
    private const BASE_URL = 'https://erddap.marine.ie/erddap/tabledap/IMI-TidePrediction.csv';

    /**
     * Ensure tide data is cached for the given station and date range.
     *
     * @param   DatabaseInterface  $db         Database connection.
     * @param   string             $stationId  Station identifier.
     * @param   Date               $startDate  Start date (UTC, inclusive, start of day).
     * @param   Date               $endDate    End date (UTC, inclusive, start of day).
     *
     * @return  void
     *
     * @since   1.0.1
     */
    public function ensureRange(DatabaseInterface $db, string $stationId, Date $startDate, Date $endDate): void
    {
        if ($this->isRangeCached($db, $stationId, $startDate, $endDate)) {
            return;
        }

        $rangeStart = $startDate->format('Y-m-d') . 'T00:00:00Z';
        $rangeEnd   = $endDate->format('Y-m-d') . 'T23:59:59Z';

        $rows = $this->fetchRange($stationId, $startDate, $endDate);
        $rows = $this->assignCategories($rows);
        $rows = $this->filterToRange($rows, $rangeStart, $rangeEnd);

        if (!empty($rows)) {
            $this->storeRows($db, $rows);
        }
    }

    /**
     * Check if range is already cached (start and end day present).
     *
     * @param   DatabaseInterface  $db         Database connection.
     * @param   string             $stationId  Station identifier.
     * @param   Date               $startDate  Start date.
     * @param   Date               $endDate    End date.
     *
     * @return  bool
     *
     * @since   1.0.1
     */
    private function isRangeCached(DatabaseInterface $db, string $stationId, Date $startDate, Date $endDate): bool
    {
        $checkDay = function (Date $day) use ($db, $stationId) {
            $dayLabel = $day->format('Y-m-d');

            $query = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('TideData'))
                ->where($db->quoteName('StationID') . ' = ' . $db->quote($stationId))
                ->where('substr(' . $db->quoteName('DateTime') . ',1,10) = ' . $db->quote($dayLabel))
                ->setLimit(1);

            $db->setQuery($query);

            return (bool) $db->loadResult();
        };

        return $checkDay($startDate) && $checkDay($endDate);
    }

    /**
     * Fetch the range (with padding) from ERDDAP and filter to requested window.
     *
     * @param   string  $stationId  Station identifier.
     * @param   Date    $startDate  Start date (UTC) inclusive.
     * @param   Date    $endDate    End date (UTC) inclusive.
     *
     * @return  array<int,array<string,mixed>>
     *
     * @since   1.0.1
     */
    private function fetchRange(string $stationId, Date $startDate, Date $endDate): array
    {
        $startPad = (clone $startDate)->modify('-1 day');
        $endPad   = (clone $endDate)->modify('+1 day');

        $dayStart = $startPad->format('Y-m-d') . 'T00:00:00Z';
        $dayEnd   = $endPad->format('Y-m-d') . 'T23:59:59Z';

        $query = [
            'time',
            'stationID',
            'longitude',
            'latitude',
            'Water_Level',
            'Water_Level_ODM',
        ];

        $columns      = implode(',', $query);
        $stationParam = 'stationID=' . '"' . $stationId . '"';
        $startParam   = 'time>=' . $dayStart;
        $endParam     = 'time<=' . $dayEnd;
        $orderParam   = 'orderBy("time")';

        $queryString = self::BASE_URL . '?' . rawurlencode($columns . '&' . implode('&', [$stationParam, $startParam, $endParam, $orderParam]));

        $http     = HttpFactory::getHttp();
        
        # Logging the request URL
        Log::add(
                Text::sprintf('MOD_YSTIDES_FETCHING', $queryString),
                Log::INFO,
                'mod_ystides'
            );

        $response = $http->get($queryString, ['Accept' => 'text/csv', 'Accept-Encoding' => 'gzip']);

        if ($response->code < 200 || $response->code >= 300) {
            Log::add(
                Text::sprintf('MOD_YSTIDES_ERR_FETCH', $response->code) . ' URL: ' . $queryString . ' Response Body: ' . $response->body,
                Log::ERROR,
                'mod_ystides'
            );
            throw new RuntimeException(Text::sprintf('MOD_YSTIDES_ERR_FETCH', $response->code));
        }

        $rows = $this->parseCsvBody($response->body, $stationId);

        return $rows;
    }

    /**
     * Parse CSV response body into rows.
     *
     * @param   string  $body       CSV body.
     * @param   string  $stationId  Station identifier.
     *
     * @return  array<int,array<string,mixed>>
     *
     * @since   1.0.1
     */
    private function parseCsvBody(string $body, string $stationId): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($body));

        if (!$lines || count($lines) < 3) {
            return [];
        }

        // Skip header and units lines.
        $lines = array_slice($lines, 2);

        $rows = [];

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $columns = str_getcsv($line);

            if (count($columns) < 6) {
                continue;
            }

            $rows[] = [
                'StationID'       => $columns[1] ?: $stationId,
                'DateTime'        => $columns[0],
                'TideCategory'    => null,
                'TideCoefficient' => null,
                'WLM'             => is_numeric($columns[4]) ? (float) $columns[4] : null,
                'WLODMM'          => is_numeric($columns[5]) ? (float) $columns[5] : null,
            ];
        }

        return $rows;
    }

    /**
     * Filter rows to the requested date range only.
     *
     * @param   array   $rows        Parsed rows.
     * @param   string  $rangeStart  Range start datetime (Y-m-d H:i:sZ).
     * @param   string  $rangeEnd    Range end datetime.
     *
     * @return  array
     *
     * @since   1.0.1
     */
    private function filterToRange(array $rows, string $rangeStart, string $rangeEnd): array
    {
        return array_values(array_filter($rows, static function ($row) use ($rangeStart, $rangeEnd) {
            $dt = $row['DateTime'] ?? '';

            return $dt !== '' && $dt >= $rangeStart && $dt <= $rangeEnd;
        }));
    }

    /**
     * Assign tide categories in memory before persisting.
     *
     * @param   array<int,array<string,mixed>>  $rows  Rows sorted by DateTime.
     *
     * @return  array<int,array<string,mixed>>
     *
     * @since   1.0.1
     */
    private function assignCategories(array $rows): array
    {
        usort($rows, static function ($a, $b) {
            return strcmp($a['DateTime'] ?? '', $b['DateTime'] ?? '');
        });

        $previousCategory = '';
        $previousValue    = null;

        foreach ($rows as $index => &$row) {
            $value = $row['WLM'];

            if ($index === 0 || $previousValue === null || $value === null) {
                $row['TideCategory'] = $previousCategory;
            } elseif ($value < $previousValue) {
                $row['TideCategory'] = 'e';
            } elseif ($value > $previousValue) {
                $row['TideCategory'] = 'f';
            } else {
                $row['TideCategory'] = $previousCategory;
            }

            $previousCategory = $row['TideCategory'];
            $previousValue    = $value;
        }

        unset($row);

        return $rows;
    }

    /**
     * Store rows into TideData, ignoring duplicates.
     *
     * @param   DatabaseInterface  $db    Database connection.
     * @param   array              $rows  Parsed rows.
     *
     * @return  void
     *
     * @since   1.0.1
     */
    private function storeRows(DatabaseInterface $db, array $rows): void
    {
        $db->transactionStart();

        try {
            foreach ($rows as $row) {
                $sql = sprintf(
                    'INSERT OR IGNORE INTO TideData (StationID, DateTime, TideCategory, TideCoefficient, WLM, WLODMM) VALUES (%s, %s, %s, %s, %s, %s)',
                    $db->quote($row['StationID']),
                    $db->quote($row['DateTime']),
                    $db->quote($row['TideCategory']),
                    $row['TideCoefficient'] === null ? 'NULL' : (int) $row['TideCoefficient'],
                    $row['WLM'] === null ? 'NULL' : $db->quote($row['WLM']),
                    $row['WLODMM'] === null ? 'NULL' : $db->quote($row['WLODMM'])
                );

                $db->setQuery($sql);
                $db->execute();
            }

            $db->transactionCommit();
        } catch (\Throwable $exception) {
            $db->transactionRollback();

            throw $exception;
        }
    }
}
