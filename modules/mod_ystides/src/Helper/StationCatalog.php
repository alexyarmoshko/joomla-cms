<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_ystides
 *
 * @copyright   (C) 2025 YSTides
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Ystides\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides a static catalog of stations and coordinates.
 *
 * @since  1.0.1
 */
class StationCatalog
{
    /**
     * Cached stations.
     *
     * @var  array<int,array<string,mixed>>
     * @since 1.0.1
     */
    private static array $stations = [
        ['StationID' => 'Achill_Island_MODELLED', 'StationName' => 'Achill Island', 'LonDegE' => '-10.1016', 'LatDegN' => '53.9522', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Aranmore', 'StationName' => 'Aranmore', 'LonDegE' => '-8.49562', 'LatDegN' => '54.9896', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Arklow', 'StationName' => 'Arklow', 'LonDegE' => '-6.145231', 'LatDegN' => '52.79205', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Ballycotton', 'StationName' => 'Ballycotton', 'LonDegE' => '-8.0007', 'LatDegN' => '51.82776', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Ballyglass', 'StationName' => 'Ballyglass', 'LonDegE' => '-9.89', 'LatDegN' => '54.253', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Bray_Harbour_MODELLED', 'StationName' => 'Bray Harbour', 'LonDegE' => '-6.0901', 'LatDegN' => '53.2191', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Buncranna', 'StationName' => 'Buncranna', 'LonDegE' => '-7.464125', 'LatDegN' => '55.12662', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Carrigaholt_MODELLED', 'StationName' => 'Carrigaholt', 'LonDegE' => '-9.6812', 'LatDegN' => '52.5965', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Castletownbere', 'StationName' => 'Castletownbere', 'LonDegE' => '-9.9034', 'LatDegN' => '51.6496', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Clare_Island_MODELLED', 'StationName' => 'Clare Island', 'LonDegE' => '-9.9443', 'LatDegN' => '53.8019', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Crosshaven_MODELLED', 'StationName' => 'Crosshaven', 'LonDegE' => '-8.2411', 'LatDegN' => '51.7794', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Dingle', 'StationName' => 'Dingle', 'LonDegE' => '-10.27732', 'LatDegN' => '52.13924', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Dublin_Port', 'StationName' => 'Dublin Port', 'LonDegE' => '-6.22166', 'LatDegN' => '53.34574', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Dungarvan_MODELLED', 'StationName' => 'Dungarvan', 'LonDegE' => '-7.5521', 'LatDegN' => '52.0672', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Dunmore', 'StationName' => 'Dunmore', 'LonDegE' => '-6.99166', 'LatDegN' => '52.14754', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Fenit', 'StationName' => 'Fenit', 'LonDegE' => '-9.8644', 'LatDegN' => '52.27129', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Galway', 'StationName' => 'Galway', 'LonDegE' => '-9.04796', 'LatDegN' => '53.26895', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Howth', 'StationName' => 'Howth', 'LonDegE' => '-6.0683', 'LatDegN' => '53.39148', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Inishmore', 'StationName' => 'Inishmore', 'LonDegE' => '-9.66', 'LatDegN' => '53.126', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Killary_Harbour_MODELLED', 'StationName' => 'Killary Harbour', 'LonDegE' => '-9.9016', 'LatDegN' => '53.6316', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Killybegs', 'StationName' => 'Killybegs', 'LonDegE' => '-8.3949', 'LatDegN' => '54.6364', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Kilrush', 'StationName' => 'Kilrush', 'LonDegE' => '-9.50208', 'LatDegN' => '52.63191', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Kinsale_MODELLED', 'StationName' => 'Kinsale', 'LonDegE' => '-8.446', 'LatDegN' => '51.6777', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Lahinch_MODELLED', 'StationName' => 'Lahinch', 'LonDegE' => '-9.3899', 'LatDegN' => '52.911', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Letterfrack_MODELLED', 'StationName' => 'Letterfrack', 'LonDegE' => '-10.0388', 'LatDegN' => '53.582', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Malin_Head', 'StationName' => 'Malin Head', 'LonDegE' => '-7.33432', 'LatDegN' => '55.37168', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Port_Oriel', 'StationName' => 'Port Oriel', 'LonDegE' => '-6.221713', 'LatDegN' => '53.79899', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Ringaskiddy', 'StationName' => 'Ringaskiddy', 'LonDegE' => '-8.304', 'LatDegN' => '51.84', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Roonagh', 'StationName' => 'Roonagh', 'LonDegE' => '-9.90442', 'LatDegN' => '53.76235', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Rossaveel', 'StationName' => 'Rossaveel', 'LonDegE' => '-9.562056', 'LatDegN' => '53.26693', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Rosslare', 'StationName' => 'Rosslare', 'LonDegE' => '-6.334861', 'LatDegN' => '52.2546', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Skerries', 'StationName' => 'Skerries', 'LonDegE' => '-6.108117', 'LatDegN' => '53.585', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Sligo', 'StationName' => 'Sligo', 'LonDegE' => '-8.5689', 'LatDegN' => '54.3046', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Tom_Clarke_Bridge', 'StationName' => 'Tom Clarke Bridge', 'LonDegE' => '-6.227383', 'LatDegN' => '53.34623', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Tory_Island_MODELLED', 'StationName' => 'Tory Island', 'LonDegE' => '-8.1962', 'LatDegN' => '55.2508', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Union_Hall', 'StationName' => 'Union Hall', 'LonDegE' => '-9.1335', 'LatDegN' => '51.559', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Wexford', 'StationName' => 'Wexford', 'LonDegE' => '-6.4589', 'LatDegN' => '52.33852', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
        ['StationID' => 'Wicklow_MODELLED', 'StationName' => 'Wicklow', 'LonDegE' => '-6.0127', 'LatDegN' => '52.9889', 'MTR' => null, 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null],
    ];

    /**
     * Return all stations.
     *
     * @return  array<int,array<string,mixed>>
     *
     * @since   1.0.1
     */
    public static function getStations(): array
    {
        return self::$stations;
    }

    /**
     * Find a station by ID.
     *
     * @param   string  $stationId  Station identifier.
     *
     * @return  array<string,mixed>|null
     *
     * @since   1.0.1
     */
    public static function findStation(string $stationId): ?array
    {
        foreach (self::$stations as $station) {
            if ($station['StationID'] === $stationId) {
                return $station;
            }
        }

        return null;
    }

    /**
     * Get a user-friendly station name by ID.
     *
     * @param   string  $stationId  Station identifier.
     *
     * @return  string
     *
     * @since   1.0.1
     */
    public static function getStationLabel(string $stationId): string
    {
        $station = self::findStation($stationId);

        return $station['StationName'] ?? $stationId;
    }
}
