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
use Joomla\Module\Ystides\Site\Helper\MoonPhaseHelper;
use Joomla\Module\Ystides\Site\Helper\StationCatalog;
use Joomla\Module\Ystides\Site\Helper\TideDataFetcher;
use Joomla\Module\Ystides\Site\Helper\WeatherWarningHelper;
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
     * Moon phase helper.
     *
     * @var    MoonPhaseHelper
     * @since  1.0.2
     */
    private MoonPhaseHelper $moonPhaseHelper;

    /**
     * Weather warning helper.
     *
     * @var    WeatherWarningHelper
     * @since  1.0.3
     */
    private WeatherWarningHelper $weatherWarningHelper;

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
     * @param   mixed                      $config                 Optional config (ignored when called from HelperFactory).
     * @param   DatabaseHelper|null        $databaseHelper         Optional database helper for testing/overrides.
     * @param   TideDataFetcher|null       $tideDataFetcher        Optional fetcher helper for testing/overrides.
     * @param   MoonPhaseHelper|null       $moonPhaseHelper        Optional moon phase helper for testing/overrides.
     * @param   WeatherWarningHelper|null  $weatherWarningHelper   Optional weather warning helper for testing/overrides.
     *
     * @since   1.0.1
     */
    public function __construct(
        $config = null,
        ?DatabaseHelper $databaseHelper = null,
        ?TideDataFetcher $tideDataFetcher = null,
        ?MoonPhaseHelper $moonPhaseHelper = null,
        ?WeatherWarningHelper $weatherWarningHelper = null
    ) {
        if ($config instanceof DatabaseHelper && $databaseHelper === null) {
            $databaseHelper = $config;
        }

        $this->databaseHelper = $databaseHelper ?? new DatabaseHelper();
        $this->tideDataFetcher = $tideDataFetcher ?? new TideDataFetcher();
        $this->moonPhaseHelper = $moonPhaseHelper ?? new MoonPhaseHelper();
        $this->weatherWarningHelper = $weatherWarningHelper ?? new WeatherWarningHelper();
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
        $stationId = (string) $params->get('station_id', '');
        $daysRange = max(1, (int) $params->get('days_range', 7));

        $startDate = $this->getUtcStartOfDay();
        $endDate = (clone $startDate)->modify('+' . max(0, $daysRange - 1) . ' days');

        $stationDisplay = $stationId ? StationCatalog::getStationLabel($stationId) : Text::_('MOD_YSTIDES_STATION_PLACEHOLDER');

        $dbReady = false;
        $dbError = '';
        $dbPath = '';
        $fetchError = '';

        try {
            $dbInfo = $this->databaseHelper->prepareDatabase($params);
            $dbPath = $dbInfo['path'];
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

                // Ensure moon phases are cached for the date range years.
                $startYear = (int) $startDate->format('Y');
                $endYear = (int) $endDate->format('Y');
                $years = ($startYear === $endYear) ? [$startYear] : [$startYear, $endYear];
                $this->moonPhaseHelper->ensurePhasesForYears($dbInfo['driver'], $years);

                // Get moon phases for display range.
                $moonPhases = $this->moonPhaseHelper->getPhasesForRange(
                    $dbInfo['driver'],
                    (clone $startDate)->modify('-1 days')->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                );

                // Ensure weather warnings are up to date and get warnings for station.
                $this->weatherWarningHelper->ensureWarningsUpdated($dbInfo['driver']);
                $warnings = $this->weatherWarningHelper->getWarningsForStation(
                    $dbInfo['driver'],
                    $stationId,
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                );

                $this->displayRows = $this->loadDisplayRows($dbInfo['driver'], $stationId, $startDate, $endDate, $moonPhases, $warnings);
            } catch (Throwable $exception) {
                $fetchError = Text::sprintf('MOD_YSTIDES_ERR_FETCH', $exception->getMessage());
                Factory::getApplication()->enqueueMessage($fetchError, 'warning');
                Log::add($exception->getMessage(), Log::ERROR, 'mod_ystides');
            }
        }

        // Determine header warning - show if any row has a warning
        $headerWarning = null;
        foreach ($this->displayRows as $row) {
            if (!empty($row['warningIcon'])) {
                $icon = $row['warningIcon'];
                // Track highest severity for header
                if ($headerWarning === null) {
                    $headerWarning = $icon;
                } else {
                    // Priority: red > orange > yellow > green > small-craft
                    $headerWarning = $this->getHigherSeverityIcon($headerWarning, $icon);
                }
            }
        }

        return [
            'stationId' => $stationId,
            'stationName' => $stationDisplay,
            'daysRange' => $daysRange,
            'dbReady' => $dbReady,
            'dbPath' => $dbPath,
            'dbError' => $dbError,
            'fetchError' => $fetchError,
            'rows' => $this->displayRows,
            'headerWarning' => $headerWarning,
        ];
    }

    /**
     * Get the higher severity icon between two icons.
     *
     * @param   string  $icon1  First icon name.
     * @param   string  $icon2  Second icon name.
     *
     * @return  string
     *
     * @since   1.0.3
     */
    private function getHigherSeverityIcon(string $icon1, string $icon2): string
    {
        $priority = [
            'small-craft' => 1,
            'green' => 2,
            'yellow' => 3,
            'orange' => 4,
            'red' => 5,
        ];

        $p1 = $priority[$icon1] ?? 0;
        $p2 = $priority[$icon2] ?? 0;

        return $p1 >= $p2 ? $icon1 : $icon2;
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
     * @param   \Joomla\Database\DatabaseInterface  $db          Database connection.
     * @param   string                              $stationId   Station identifier.
     * @param   Date                                $startDate   Start date (UTC).
     * @param   Date                                $endDate     End date (UTC).
     * @param   array<string, string>               $moonPhases  Moon phases keyed by date.
     * @param   array<string, array>                $warnings    Weather warnings keyed by date.
     *
     * @return  array<int,array<string,mixed>>
     *
     * @since   1.0.1
     */
    private function loadDisplayRows($db, string $stationId, Date $startDate, Date $endDate, array $moonPhases = [], array $warnings = []): array
    {
        // Stored datetimes use ISO format with "T" and "Z" (e.g. 2025-12-28T12:30:00Z)
        // Start date/time is six hours before to capture the last high/low at the previous day.
        $start = (clone $startDate)->modify('-1 days')->format('Y-m-d') . 'T17:00:00Z';
        $end = $endDate->format('Y-m-d') . 'T23:59:59Z';

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
            function ($group) use ($moonPhases, $warnings) {
                $category = $group['category'] ?? '';
                $coef = (isset($group['coef']) && is_numeric($group['coef'])) ? (int) $group['coef'] : null;
                $startDT = $group['start'];
                $endDT = $group['end'];
                $startTS = (new Date($startDT, 'UTC'))->toUnix();
                $endTS = (new Date($endDT, 'UTC'))->toUnix();
                $meanDT = round(($startTS + $endTS) / 2, 0);
                $dateKey = HTMLHelper::_('date', $meanDT, 'Y-m-d', 'UTC');

                // Check for moon phase on this date.
                $moonPhase = $moonPhases[$dateKey] ?? null;

                // Check for weather warning on this date.
                $warningIcon = null;
                if (isset($warnings[$dateKey])) {
                    $warningIcon = WeatherWarningHelper::getPrimaryWarningIcon($warnings[$dateKey]);
                }

                return [
                    'titledt' => HTMLHelper::_('date', $startDT, 'DATE_FORMAT_LC5', 'UTC') . ' - ' . HTMLHelper::_('date', $endDT, 'DATE_FORMAT_LC5', 'UTC'),
                    'startdt' => $startDT,
                    'enddt' => $endDT,
                    'meand' => HTMLHelper::_('date', $meanDT, 'DATE_FORMAT_LC4', 'UTC'),
                    'meandt' => $meanDT,
                    'wlm' => $group['wlm'] !== null ? number_format((float) $group['wlm'], 2) : '',
                    'tidehint' => $this->categorySymbol($category)['label'],
                    'coef' => $coef,
                    'moonPhase' => $moonPhase,
                    'warningIcon' => $warningIcon,
                    'raw' => $group,
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
            $dt = $row['TideDT'];
            $coef = $row['TideCoefficient'] ?? null;

            if ($current === null) {
                $current = [
                    'category' => $cat,
                    'wlm' => $wlm,
                    'start' => $dt,
                    'end' => $dt,
                    'coef' => $coef,
                ];

                continue;
            }

            $isSameGroup = ($current['category'] === $cat) && ($current['wlm'] === $wlm);

            if ($isSameGroup) {
                $current['end'] = $dt;
            } else {
                $grouped[] = $current;
                $current = [
                    'category' => $cat,
                    'wlm' => $wlm,
                    'start' => $dt,
                    'end' => $dt,
                    'coef' => $coef,
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
            'h' => ['symbol' => 'hw', 'label' => Text::_('MOD_YSTIDES_HIGH_WATER')],
            'l' => ['symbol' => 'lw', 'label' => Text::_('MOD_YSTIDES_LOW_WATER')],
            'e' => ['symbol' => 'e', 'label' => Text::_('MOD_YSTIDES_EBBING')],
            'f' => ['symbol' => 'f', 'label' => Text::_('MOD_YSTIDES_FLOODING')],
            default => ['symbol' => '?', 'label' => ''],
        };
    }
}
