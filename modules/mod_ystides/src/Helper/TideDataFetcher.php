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
        $missingDays = $this->findMissingDays($db, $stationId, $startDate, $endDate);

        if (empty($missingDays)) {
            return;
        }

        foreach ($missingDays as $day) {
            $rows = $this->fetchDay($stationId, $day);

            if (!empty($rows)) {
                $this->storeRows($db, $rows);
            }
        }
    }

    /**
     * Determine which days in the range are missing any cached records.
     *
     * @param   DatabaseInterface  $db         Database connection.
     * @param   string             $stationId  Station identifier.
     * @param   Date               $startDate  Start date (UTC, inclusive).
     * @param   Date               $endDate    End date (UTC, inclusive).
     *
     * @return  array<int, Date>
     *
     * @since   1.0.1
     */
    private function findMissingDays(DatabaseInterface $db, string $stationId, Date $startDate, Date $endDate): array
    {
        $missing = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $dayLabel = $current->format('Y-m-d');

            $query = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('TideData'))
                ->where($db->quoteName('StationID') . ' = ' . $db->quote($stationId))
                ->where('substr(' . $db->quoteName('DateTime') . ',1,10) = ' . $db->quote($dayLabel))
                ->setLimit(1);

            $db->setQuery($query);
            $hasRow = (bool) $db->loadResult();

            if (!$hasRow) {
                $missing[] = clone $current;
            }

            $current->modify('+1 day');
        }

        return $missing;
    }

    /**
     * Fetch a day's data from ERDDAP.
     *
     * @param   string  $stationId  Station identifier.
     * @param   Date    $day        Day (UTC) to fetch.
     *
     * @return  array<int,array<string,mixed>>
     *
     * @since   1.0.1
     */
    private function fetchDay(string $stationId, Date $day): array
    {
        $dayStart = $day->format('Y-m-d') . 'T00:00:00Z';
        $dayEnd   = $day->format('Y-m-d') . 'T23:59:59Z';

        $query = [
            'time',
            'stationID',
            'longitude',
            'latitude',
            'Water_Level',
            'Water_Level_ODM',
        ];

        $queryString = self::BASE_URL . '?' . rawurlencode(sprintf(
            '%s&stationID=%s&time>=%s&time<=%s&orderBy("time")',
            implode(',', $query),
            '"' . $stationId . '"',
            $dayStart,
            $dayEnd
        ));

        $http     = HttpFactory::getHttp();

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

        return $this->parseCsvBody($response->body, $stationId);
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
                'StationID'      => $columns[1] ?: $stationId,
                'DateTime'       => $columns[0],
                'TideCategory'   => 'f', // Placeholder until categorisation step.
                'TideCoefficient'=> null,
                'WLM'            => is_numeric($columns[4]) ? (float) $columns[4] : null,
                'WLODMM'         => is_numeric($columns[5]) ? (float) $columns[5] : null,
            ];
        }

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
