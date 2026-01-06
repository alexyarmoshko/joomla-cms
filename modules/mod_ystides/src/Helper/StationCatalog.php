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
        // West coast - Mayo
        ['StationID' => 'Achill_Island_MODELLED', 'StationName' => 'Achill Island', 'LonDegE' => '-10.1016', 'LatDegN' => '53.9522', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI20,EI819'],
        // Northwest - Donegal
        ['StationID' => 'Aranmore', 'StationName' => 'Aranmore', 'LonDegE' => '-8.49562', 'LatDegN' => '54.9896', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI06,EI820,EI821'],
        // East coast - Wicklow
        ['StationID' => 'Arklow', 'StationName' => 'Arklow', 'LonDegE' => '-6.145231', 'LatDegN' => '52.79205', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI31,EI811'],
        // South coast - Cork
        ['StationID' => 'Ballycotton', 'StationName' => 'Ballycotton', 'LonDegE' => '-8.0007', 'LatDegN' => '51.82776', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI04,EI814'],
        // West coast - Mayo
        ['StationID' => 'Ballyglass', 'StationName' => 'Ballyglass', 'LonDegE' => '-9.89', 'LatDegN' => '54.253', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI20,EI819,EI820'],
        // East coast - Wicklow
        ['StationID' => 'Bray_Harbour_MODELLED', 'StationName' => 'Bray Harbour', 'LonDegE' => '-6.0901', 'LatDegN' => '53.2191', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI31,EI810,EI811'],
        // Northwest - Donegal
        ['StationID' => 'Buncranna', 'StationName' => 'Buncranna', 'LonDegE' => '-7.464125', 'LatDegN' => '55.12662', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI06,EI805,EI822'],
        // West coast - Clare
        ['StationID' => 'Carrigaholt_MODELLED', 'StationName' => 'Carrigaholt', 'LonDegE' => '-9.6812', 'LatDegN' => '52.5965', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI03,EI817,EI818'],
        // Southwest - Cork
        ['StationID' => 'Castletownbere', 'StationName' => 'Castletownbere', 'LonDegE' => '-9.9034', 'LatDegN' => '51.6496', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI04,EI815,EI816'],
        // West coast - Mayo
        ['StationID' => 'Clare_Island_MODELLED', 'StationName' => 'Clare Island', 'LonDegE' => '-9.9443', 'LatDegN' => '53.8019', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI20,EI819'],
        // South coast - Cork
        ['StationID' => 'Crosshaven_MODELLED', 'StationName' => 'Crosshaven', 'LonDegE' => '-8.2411', 'LatDegN' => '51.7794', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI04,EI814,EI815'],
        // Southwest - Kerry
        ['StationID' => 'Dingle', 'StationName' => 'Dingle', 'LonDegE' => '-10.27732', 'LatDegN' => '52.13924', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI11,EI816,EI817'],
        // East coast - Dublin
        ['StationID' => 'Dublin_Port', 'StationName' => 'Dublin Port', 'LonDegE' => '-6.22166', 'LatDegN' => '53.34574', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI07,EI809,EI810,EI823'],
        // South coast - Waterford
        ['StationID' => 'Dungarvan_MODELLED', 'StationName' => 'Dungarvan', 'LonDegE' => '-7.5521', 'LatDegN' => '52.0672', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI27,EI813,EI814'],
        // South coast - Waterford
        ['StationID' => 'Dunmore', 'StationName' => 'Dunmore', 'LonDegE' => '-6.99166', 'LatDegN' => '52.14754', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI27,EI812,EI813'],
        // Southwest - Kerry
        ['StationID' => 'Fenit', 'StationName' => 'Fenit', 'LonDegE' => '-9.8644', 'LatDegN' => '52.27129', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI11,EI817'],
        // West coast - Galway
        ['StationID' => 'Galway', 'StationName' => 'Galway', 'LonDegE' => '-9.04796', 'LatDegN' => '53.26895', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI10,EI818'],
        // East coast - Dublin
        ['StationID' => 'Howth', 'StationName' => 'Howth', 'LonDegE' => '-6.0683', 'LatDegN' => '53.39148', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI07,EI809,EI810,EI823'],
        // West coast - Galway
        ['StationID' => 'Inishmore', 'StationName' => 'Inishmore', 'LonDegE' => '-9.66', 'LatDegN' => '53.126', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI10,EI818'],
        // West coast - Galway/Mayo border
        ['StationID' => 'Killary_Harbour_MODELLED', 'StationName' => 'Killary Harbour', 'LonDegE' => '-9.9016', 'LatDegN' => '53.6316', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI10,EI20,EI818,EI819'],
        // Northwest - Donegal
        ['StationID' => 'Killybegs', 'StationName' => 'Killybegs', 'LonDegE' => '-8.3949', 'LatDegN' => '54.6364', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI06,EI820,EI821'],
        // West coast - Clare
        ['StationID' => 'Kilrush', 'StationName' => 'Kilrush', 'LonDegE' => '-9.50208', 'LatDegN' => '52.63191', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI03,EI817,EI818'],
        // South coast - Cork
        ['StationID' => 'Kinsale_MODELLED', 'StationName' => 'Kinsale', 'LonDegE' => '-8.446', 'LatDegN' => '51.6777', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI04,EI815'],
        // West coast - Clare
        ['StationID' => 'Lahinch_MODELLED', 'StationName' => 'Lahinch', 'LonDegE' => '-9.3899', 'LatDegN' => '52.911', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI03,EI818'],
        // West coast - Galway
        ['StationID' => 'Letterfrack_MODELLED', 'StationName' => 'Letterfrack', 'LonDegE' => '-10.0388', 'LatDegN' => '53.582', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI10,EI818,EI819'],
        // Northwest - Donegal
        ['StationID' => 'Malin_Head', 'StationName' => 'Malin Head', 'LonDegE' => '-7.33432', 'LatDegN' => '55.37168', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI06,EI805,EI822'],
        // East coast - Louth
        ['StationID' => 'Port_Oriel', 'StationName' => 'Port Oriel', 'LonDegE' => '-6.221713', 'LatDegN' => '53.79899', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI19,EI808,EI809'],
        // South coast - Cork
        ['StationID' => 'Ringaskiddy', 'StationName' => 'Ringaskiddy', 'LonDegE' => '-8.304', 'LatDegN' => '51.84', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI04,EI814,EI815'],
        // West coast - Mayo
        ['StationID' => 'Roonagh', 'StationName' => 'Roonagh', 'LonDegE' => '-9.90442', 'LatDegN' => '53.76235', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI20,EI819'],
        // West coast - Galway
        ['StationID' => 'Rossaveel', 'StationName' => 'Rossaveel', 'LonDegE' => '-9.562056', 'LatDegN' => '53.26693', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI10,EI818'],
        // Southeast - Wexford
        ['StationID' => 'Rosslare', 'StationName' => 'Rosslare', 'LonDegE' => '-6.334861', 'LatDegN' => '52.2546', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI30,EI811,EI812,EI823'],
        // East coast - Dublin
        ['StationID' => 'Skerries', 'StationName' => 'Skerries', 'LonDegE' => '-6.108117', 'LatDegN' => '53.585', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI07,EI808,EI809'],
        // Northwest - Sligo
        ['StationID' => 'Sligo', 'StationName' => 'Sligo', 'LonDegE' => '-8.5689', 'LatDegN' => '54.3046', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI25,EI819,EI820'],
        // East coast - Dublin
        ['StationID' => 'Tom_Clarke_Bridge', 'StationName' => 'Tom Clarke Bridge', 'LonDegE' => '-6.227383', 'LatDegN' => '53.34623', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI07,EI809,EI810,EI823'],
        // Northwest - Donegal
        ['StationID' => 'Tory_Island_MODELLED', 'StationName' => 'Tory Island', 'LonDegE' => '-8.1962', 'LatDegN' => '55.2508', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI06,EI821,EI822'],
        // South coast - Cork
        ['StationID' => 'Union_Hall', 'StationName' => 'Union Hall', 'LonDegE' => '-9.1335', 'LatDegN' => '51.559', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI04,EI815,EI816'],
        // Southeast - Wexford
        ['StationID' => 'Wexford', 'StationName' => 'Wexford', 'LonDegE' => '-6.4589', 'LatDegN' => '52.33852', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI30,EI811,EI812'],
        // East coast - Wicklow
        ['StationID' => 'Wicklow_MODELLED', 'StationName' => 'Wicklow', 'LonDegE' => '-6.0127', 'LatDegN' => '52.9889', 'RefStationID' => null, 'RefStationHWTimeOffset' => null, 'RefStationLWTimeOffset' => null, 'RefStationHWLOffset' => null, 'RefStationLWLOffset' => null, 'AreaCodes' => 'EI31,EI810,EI811'],
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
