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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use Joomla\Module\Ystides\Site\Helper\StationCatalog;
use Joomla\Module\Ystides\Site\Helper\TideDataFetcher;
use Throwable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for the YSTides module.
 *
 * @since  1.0.0
 */
class YstidesHelper
{
    /**
     * Database helper instance.
     *
     * @var    DatabaseHelper
     * @since  1.0.1
     */
    private DatabaseHelper $databaseHelper;

    /**
     * Tide data fetcher.
     *
     * @var    TideDataFetcher
     * @since  1.0.1
     */
    private TideDataFetcher $tideDataFetcher;

    /**
     * Cached display rows.
     *
     * @var    array
     * @since  1.0.1
     */
    private array $displayRows = [];

    /**
     * Constructor.
     *
     * @param   mixed                 $config           Optional config (ignored when called from HelperFactory).
     * @param   DatabaseHelper|null   $databaseHelper   Optional database helper for testing/overrides.
     * @param   TideDataFetcher|null  $tideDataFetcher  Optional fetcher helper for testing/overrides.
     *
     * @since   1.0.1
     */
    public function __construct($config = null, ?DatabaseHelper $databaseHelper = null, ?TideDataFetcher $tideDataFetcher = null)
    {
        if ($config instanceof DatabaseHelper && $databaseHelper === null) {
            $databaseHelper = $config;
        }

        $this->databaseHelper = $databaseHelper ?? new DatabaseHelper();
        $this->tideDataFetcher = $tideDataFetcher ?? new TideDataFetcher();
    }

    /**
     * Prepare data for the module layout.
     *
     * @param   Registry  $params  Module parameters.
     *
     * @return  array
     *
     * @since   1.0.0
     */
    public function getLayoutVariables(Registry $params): array
    {
        $stationId  = (string) $params->get('station_id', '');
        $daysRange  = max(1, (int) $params->get('days_range', 7));

        $startDate = $this->getUtcStartOfDay();
        $endDate   = (clone $startDate)->modify('+' . max(0, $daysRange - 1) . ' days');

        $stationDisplay = $stationId ? StationCatalog::getStationLabel($stationId) : Text::_('MOD_YSTIDES_STATION_PLACEHOLDER');

        $dbReady   = false;
        $dbError   = '';
        $dbPath    = '';
        $fetchError = '';

        try {
            $dbInfo  = $this->databaseHelper->prepareDatabase($params);
            $dbPath  = $dbInfo['path'];
            $dbReady = true;
        } catch (Throwable $exception) {
            $dbError = Text::sprintf('MOD_YSTIDES_ERR_DB_INIT', $exception->getMessage());
            Factory::getApplication()->enqueueMessage($dbError, 'warning');
            Log::add($exception->getMessage(), Log::ERROR, 'mod_ystides');
        }

        if ($dbReady && $stationId !== '') {
            try {
                // Always ensure that data for Dublin Port is available as it's needed for reference.
                $this->tideDataFetcher->ensureRange($dbInfo['driver'], 'Dublin_Port', (clone $startDate)->modify('-2 days'), (clone $startDate)->modify('+14 days'));
                
                // Fetch data for the selected station +- 1 day to ensure proper tide range calculation.
                $this->tideDataFetcher->ensureRange($dbInfo['driver'], $stationId, (clone $startDate)->modify('-1 days'), (clone $endDate)->modify('+1 days'));
                $this->displayRows = $this->loadDisplayRows($dbInfo['driver'], $stationId, $startDate, $endDate);
            } catch (Throwable $exception) {
                $fetchError = Text::sprintf('MOD_YSTIDES_ERR_FETCH', $exception->getMessage());
                Factory::getApplication()->enqueueMessage($fetchError, 'warning');
                Log::add($exception->getMessage(), Log::ERROR, 'mod_ystides');
            }
        }

        return [
            'stationId'      => $stationId,
            'stationName'    => $stationDisplay,
            'daysRange'      => $daysRange,
            'dateRangeStart' => HTMLHelper::_('date', $startDate->toUnix(), Text::_('MOD_YSTIDES_DATE_FORMAT_LC7'), 'UTC'),
            'dateRangeEnd'   => HTMLHelper::_('date', $endDate->toUnix(), Text::_('MOD_YSTIDES_DATE_FORMAT_LC7'), 'UTC'),
            'dbReady'        => $dbReady,
            'dbPath'         => $dbPath,
            'dbError'        => $dbError,
            'fetchError'     => $fetchError,
            'rows'           => $this->displayRows,
        ];
    }

    /**
     * Get the current UTC day at midnight.
     *
     * @return  Date
     *
     * @since   1.0.0
     */
    private function getUtcStartOfDay(): Date
    {
        $now = Factory::getDate('now', 'UTC');

        return new Date($now->format('Y-m-d 00:00:00'), 'UTC');
    }

    /**
     * Load display rows from cache for the date range.
     *
     * @param   \Joomla\Database\DatabaseInterface  $db         Database connection.
     * @param   string                              $stationId  Station identifier.
     * @param   Date                                $startDate  Start date (UTC).
     * @param   Date                                $endDate    End date (UTC).
     *
     * @return  array<int,array<string,mixed>>
     *
     * @since   1.0.1
     */
    private function loadDisplayRows($db, string $stationId, Date $startDate, Date $endDate): array
    {
        // Stored datetimes use ISO format with "T" and "Z" (e.g. 2025-12-28T12:30:00Z)
        // Start date/time is six hours before to capture the last high/low at the previous day.
        $start = (clone $startDate)->modify('-1 days')->format('Y-m-d') . 'T17:00:00Z';
        $end   = $endDate->format('Y-m-d') . 'T23:59:59Z';

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('TideDT'),
                $db->quoteName('WLM'),
                $db->quoteName('TideCategory'),
                $db->quoteName('TideCoefficient'),
            ])
            ->from($db->quoteName('TideData'))
            ->where($db->quoteName('StationID') . ' = ' . $db->quote($stationId))
            ->where($db->quoteName('TideDT') . ' BETWEEN ' . $db->quote($start) . ' AND ' . $db->quote($end))
            ->where($db->quoteName('TideCategory') . ' IN (' . $db->quote('h') . ',' . $db->quote('l') . ')')
            ->order($db->quoteName('TideDT') . ' ASC');

        $db->setQuery($query);
        $rows = $db->loadAssocList();

        $grouped = $this->groupByCategoryAndWlm($rows);

        return array_map(
            function ($group) {
                $category = $group['category'] ?? '';
                $symbol   = $this->categorySymbol($category);

                $progress = null;
                if (isset($group['coef']) && is_numeric($group['coef'])) {
                    $progress = (int) $group['coef'];
                }

                $startTime = HTMLHelper::_('date', $group['start'], 'H:i', 'UTC');
                $endTime   = HTMLHelper::_('date', $group['end'], 'H:i', 'UTC');

                $startDate = HTMLHelper::_('date', $group['start'], 'd M', 'UTC');
                $endDate   = HTMLHelper::_('date', $group['end'], 'd M', 'UTC');

                return [
                    'startd'  => $startDate,
                    'endd'    => $endDate,
                    'startt'  => $startTime,
                    'endt'    => $endTime,
                    'wlm'     => $group['wlm'] !== null ? number_format((float) $group['wlm'], 2) : '',
                    'symbol'  => $symbol['symbol'],
                    'hint'    => $symbol['label'],
                    'coef'    => $progress,
                    'raw'     => $group,
                ];
            },
            $grouped
        );
    }

    /**
     * Group consecutive rows with same category and WLM into a single display item.
     *
     * @param   array  $rows  Rows from DB.
     *
     * @return  array<int,array<string,mixed>>
     *
     * @since   1.0.1
     */
    private function groupByCategoryAndWlm(array $rows): array
    {
        $grouped = [];
        $current = null;

        foreach ($rows as $row) {
            $cat = $row['TideCategory'] ?? '';
            $wlm = $row['WLM'];
            $dt  = $row['TideDT'];
            $coef = $row['TideCoefficient'] ?? null;

            if ($current === null) {
                $current = [
                    'category' => $cat,
                    'wlm'      => $wlm,
                    'start'    => $dt,
                    'end'      => $dt,
                    'coef'     => $coef,
                ];

                continue;
            }

            $isSameGroup = ($current['category'] === $cat) && ($current['wlm'] === $wlm);

            if ($isSameGroup) {
                $current['end'] = $dt;
            } else {
                $grouped[] = $current;
                $current   = [
                    'category' => $cat,
                    'wlm'      => $wlm,
                    'start'    => $dt,
                    'end'      => $dt,
                    'coef'     => $coef,
                ];
            }
        }

        if ($current !== null) {
            $grouped[] = $current;
        }

        return $grouped;
    }

    /**
     * Get display symbol and label for a category.
     *
     * @param   string  $category  Tide category.
     *
     * @return  array{symbol:string,label:string}
     *
     * @since   1.0.1
     */
    private function categorySymbol(string $category): array
    {
        return match ($category) {
            'h' => ['symbol' => '▲', 'label' => Text::_('MOD_YSTIDES_HIGH_WATER')],
            'l' => ['symbol' => '▼', 'label' => Text::_('MOD_YSTIDES_LOW_WATER')],
            'e' => ['symbol' => '↘', 'label' => ''],
            'f' => ['symbol' => '↗', 'label' => ''],
            default => ['symbol' => '?', 'label' => ''],
        };
    }
}
