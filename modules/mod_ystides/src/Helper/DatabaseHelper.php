<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_ystides
 *
 * @copyright   (C) 2025 YSTides
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Ystides\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\Module\Ystides\Site\Helper\StationCatalog;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * SQLite data layer helper for YSTides.
 *
 * @since  1.0.1
 */
class DatabaseHelper
{
    /**
     * Prepare the SQLite database: ensure path, connect, enable FK, and create schema.
     *
     * @param   Registry  $params  Module parameters.
     *
     * @return  array{driver: DatabaseInterface, path: string}
     *
     * @since   1.0.1
     */
    public function prepareDatabase(Registry $params): array
    {
        $fullPath = $this->buildDatabasePath();

        if (!extension_loaded('sqlite3')) {
            Log::add(Text::_('MOD_YSTIDES_ERR_SQLITE_MISSING'), Log::ERROR, 'mod_ystides');
            throw new RuntimeException(Text::_('MOD_YSTIDES_ERR_SQLITE_MISSING'));
        }

        $this->ensureDatabaseFile($fullPath);

        $options = [
            'driver' => 'sqlite',
            'database' => $fullPath,
            'prefix' => '',
        ];

        $db = DatabaseDriver::getInstance($options);

        $this->enableForeignKeys($db);
        $this->createSchema($db);
        $this->seedStations($db);

        return [
            'driver' => $db,
            'path' => $fullPath,
        ];
    }

    /**
     * Build database path under Joomla tmp path.
     *
     * @return  string
     *
     * @since   1.0.1
     */
    private function buildDatabasePath(): string
    {
        $tmpPath = (string) Factory::getApplication()->get('tmp_path') ?: (string) Factory::getConfig()->get('tmp_path');

        if ($tmpPath === '') {
            $tmpPath = JPATH_ROOT . '/tmp';
        }

        $dir = Path::clean($tmpPath . '/ystides');
        $file = Path::clean($dir . '/ystides.sqlite');

        return $file;
    }

    /**
     * Ensure the database file exists, creating directories if needed.
     *
     * @param   string  $fullPath  Absolute path to the DB file.
     *
     * @return  void
     *
     * @since   1.0.1
     */
    private function ensureDatabaseFile(string $fullPath): void
    {
        $dir = Path::clean(dirname($fullPath));

        if (!Folder::exists($dir)) {
            Folder::create($dir);
        }

        if (!File::exists($fullPath)) {
            File::write($fullPath, '');
        }
    }

    /**
     * Turn on foreign key constraints.
     *
     * @param   DatabaseInterface  $db  Database connection.
     *
     * @return  void
     *
     * @since   1.0.1
     */
    private function enableForeignKeys(DatabaseInterface $db): void
    {
        $db->setQuery('PRAGMA foreign_keys = ON;');
        $db->execute();
    }

    /**
     * Create required tables and indices if missing.
     *
     * @param   DatabaseInterface  $db  Database connection.
     *
     * @return  void
     *
     * @since   1.0.1
     */
    private function createSchema(DatabaseInterface $db): void
    {
        $stationSql = <<<SQL
CREATE TABLE IF NOT EXISTS TideStations (
    StationID TEXT PRIMARY KEY,
    StationName TEXT,
    LonDegE TEXT,
    LatDegN TEXT,
    RefStationID TEXT DEFAULT NULL,
    RefStationHWTimeOffset TEXT DEFAULT NULL,
    RefStationLWTimeOffset TEXT DEFAULT NULL,
    RefStationHWLOffset REAL DEFAULT NULL,
    RefStationLWLOffset REAL DEFAULT NULL
);
SQL;

        $dataSql = <<<SQL
CREATE TABLE IF NOT EXISTS TideData (
    StationID TEXT NOT NULL,
    TideDT TEXT NOT NULL,
    TideCategory TEXT NOT NULL,
    TideCoefficient INTEGER DEFAULT NULL,
    WLM REAL,
    WLODMM REAL,
    TideRange REAL DEFAULT NULL,
    PRIMARY KEY (StationID, TideDT),
    FOREIGN KEY (StationID) REFERENCES TideStations(StationID) ON DELETE CASCADE ON UPDATE CASCADE
);
SQL;

        $indexSql = 'CREATE INDEX IF NOT EXISTS idx_tidedata_station_date ON TideData (StationID, TideDT);';

        $moonPhasesSql = <<<SQL
CREATE TABLE IF NOT EXISTS TideMoonPhases (
    PhaseDT TEXT NOT NULL PRIMARY KEY,
    Phase TEXT NOT NULL
);
SQL;

        foreach ([$stationSql, $dataSql, $indexSql, $moonPhasesSql] as $sql) {
            $db->setQuery($sql);
            $db->execute();
        }
    }

    /**
     * Seed TideStations with the static catalog when empty.
     *
     * @param   DatabaseInterface  $db  Database connection.
     *
     * @return  void
     *
     * @since   1.0.1
     */
    private function seedStations(DatabaseInterface $db): void
    {
        $db->setQuery('SELECT COUNT(*) FROM TideStations');
        $count = (int) $db->loadResult();

        if ($count > 0) {
            return;
        }

        $stations = StationCatalog::getStations();
        $this->upsertStations($db, $stations);
    }

    /**
     * Seed or update station metadata from an array.
     *
     * @param   DatabaseInterface  $db        Database connection.
     * @param   array              $stations  Array of associative arrays with keys: StationID, StationName, LonDegE, LatDegN, RefStationID, RefStationHWTimeOffset, RefStationLWTimeOffset, RefStationHWLOffset, RefStationLWLOffset.
     *
     * @return  void
     *
     * @since   1.0.1
     */
    public function upsertStations(DatabaseInterface $db, array $stations): void
    {
        if (empty($stations)) {
            return;
        }

        $baseSql = <<<SQL
INSERT INTO TideStations (
    StationID,
    StationName,
    LonDegE,
    LatDegN,
    RefStationID,
    RefStationHWTimeOffset,
    RefStationLWTimeOffset,
    RefStationHWLOffset,
    RefStationLWLOffset
) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
ON CONFLICT(StationID) DO UPDATE SET
    StationName = excluded.StationName,
    LonDegE = excluded.LonDegE,
    LatDegN = excluded.LatDegN,
    RefStationID = excluded.RefStationID,
    RefStationHWTimeOffset = excluded.RefStationHWTimeOffset,
    RefStationLWTimeOffset = excluded.RefStationLWTimeOffset,
    RefStationHWLOffset = excluded.RefStationHWLOffset,
    RefStationLWLOffset = excluded.RefStationLWLOffset;
SQL;

        foreach ($stations as $station) {
            $values = [
                $this->quoteNullable($db, $station['StationID'] ?? null),
                $this->quoteNullable($db, $station['StationName'] ?? null),
                $this->quoteNullable($db, $station['LonDegE'] ?? null),
                $this->quoteNullable($db, $station['LatDegN'] ?? null),
                $this->quoteNullable($db, $station['RefStationID'] ?? null),
                $this->quoteNullable($db, $station['RefStationHWTimeOffset'] ?? null),
                $this->quoteNullable($db, $station['RefStationLWTimeOffset'] ?? null),
                $this->quoteNullable($db, $station['RefStationHWLOffset'] ?? null),
                $this->quoteNullable($db, $station['RefStationLWLOffset'] ?? null),
            ];

            $sql = sprintf($baseSql, ...$values);
            $db->setQuery($sql);
            $db->execute();
        }
    }

    /**
     * Quote a value, allowing NULL.
     *
     * @param   DatabaseInterface  $db     Database connection.
     * @param   mixed              $value  Value to quote.
     *
     * @return  string
     *
     * @since   1.0.1
     */
    private function quoteNullable(DatabaseInterface $db, $value): string
    {
        if ($value === null || $value === '') {
            return 'NULL';
        }

        return $db->quote($value);
    }

}
