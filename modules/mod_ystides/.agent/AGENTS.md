# AGENT Instructions for PHP/Joomla/YSTide module Work

- **Scope**: This folder contains Joomla 5.X code for "YSTide" module. Changes should be limited to this module when possible, chaanges to any other part of this Joomla project must be confirmed. Analyze current repository and plan design and implementation of a new Joomla 5 module that will display tidal informationn for a station ID that is selected when module is configured. Plan will be multi staged with gradual increase of complexity, e.g. initial implementation of a minimal module wireframe, then adding data fetching and storage and later showing results. The module name is YSTides. It should fetch the information from the API service at "https://erddap.marine.ie/erddap/tabledap/IMI-TidePrediction.html" and store/cache the data in a SQLLite database described below. Only the data that is not cached yet should be retrievev from the given API. Each request should retrieve a whole days range Current Date + DaysRange. Upon retieval, data should be analised and rows marked (field TideCategory described below) as "h" for hiest values for that day, "l" for lowest values for that day, "f" for values increasing in value compared to a previous one and "e" for values decreasing. This module should have a configuration dropdown parameter StationID->Station Name and DaysRange (default value is 7). The output should be as a table with a first row showing a Station Name, second row showing a dates range (from - to inclusive both ends) and further rows showing columns with time, water lelel in meters (WLM described below) and a simbol triangle pointing down for low tide and triangle pointing up for high tide.
- **Coding style**: Follow Joomla style coding and entities names that can be found in other Joomla modules located in "/modules/*" folder.
- **Database & services**: SQLLite database is to be used to store the cached data before it being send to UI. SQLLite database location should be configurable via module parameter with the default value "/cache/ystide/". This is approximate data scheme description: 

Database name: "YSTides"

Table name: TideStations

StationName, TEXT -- Station Name
StationID, TEXT -- Unique Station ID
LonDegE, TEXT -- Longitude Degrees East
LatDegN, TEXT -- Latitude Degrees North 
RefStationID, TEXT -- Reference (Main Port) Station ID, empty for Main Ports
RefStationHWTimeOffset, TEXT -- High Water Time offset from Main Port
RefStationLWTimeOffset, TEXT -- Low Water Time offset from Main Port
RefStationHWLOffset, REAL -- High Water Level offset from Main Port
RefStationLWLOffset, REAL -- Low Water Level offset from Main Port
Primary key: StationID

Table name: TideData

StationID, TEXT -- Station ID
TideDT, TEXT -- Tide data point date and time in UTC
TideCategory, TEXT -- Tide data point category: [l|h|f|e]. "l" for low water, "h" for hight water, "f" for flooding, "e" for ebbing
TideCoefficient, INTEGER -- Tidal coefficient
WLM, REAL -- Tide data point water level in meters
WLODMM, REAL -- Tide data point water level OD Malin
Primary key: StationID, TideDT
Foreign key: StationID -> TideStations.StationID

- **Testing & checks**: If you touch PHP logic, add/adjust tests where they exist (PHPUnit/system). Run available linters/tests locally when practical; keep changes minimal if tests aren’t present.
- **Config & secrets**: Never commit real credentials or local configuration. Do not modify `configuration.php` or other user-specific files. 
