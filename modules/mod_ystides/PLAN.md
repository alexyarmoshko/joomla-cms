# YSTides Module Plan

## Context & Clarifications
- Data source: ERDDAP IMI-TidePrediction via CSV with explicit columns and filters only on `stationID` and date range (current date through current date + DaysRange inclusive). Columns used now: `time`, `stationID`, `longitude`, `latitude`, `Water_Level`, `Water_Level_ODM`. `TideCoefficient`/`MTR` will be added later. StationName comes from the ERDDAP dropdown list.
- Caching: SQLite path configurable, default `/cache/ystides` relative to Joomla root; module will create missing folders/files. No historical backfill beyond configured forward window.
- Display: Times shown in UTC. Use simple HTML symbols (▲/▼) for high/low markers. Equal successive water-level values keep the same category as the previous point.

## Phased Approach
1) **Scaffold module - Completed**: Create manifest, services/dispatcher, helper/model, default layout, and language strings. Add params for StationID dropdown (populated from ERDDAP station list, also used to seed TideStations), DaysRange (default 7), and SQLite path. Render basic table shell using configured values.
2) **SQLite data layer - Completed**: Build helper to open/create DB at configured path, ensure directory creation under `/cache/ystides`, and create tables `TideStations` and `TideData` per schema with keys/indices. Seed TideStations using the ERDDAP station dropdown data (StationID, StationName, coords).
3) **Fetch & cache - Completed**: Request ERDDAP CSV selecting only needed columns for the chosen station and date window (inclusive today → today+DaysRange), ordered by time. Stream-parse to minimize memory, cast types, insert missing TideData rows, and upsert station metadata (Lon/Lat). Leave `TideCoefficient`/`MTR` placeholders for later.
4) **Categorization - Completed**: For each day, mark highest WLM as `h` and lowest as `l`. For other rows, compare to previous value: rising → `f`, falling → `e`; equal successive values keep the prior category. Apply deterministic handling for ties within the day.
5) **Render output - Completed**: Load cached data for the station/date window; format date/time with Joomla helpers in UTC. Show table with StationName header, date range row, then time/WLM/triangle marker rows. Add minimal CSS/JS via asset manager; all labels via language strings.
6) **Refactoring and improvements** 
7) **Admin UX & tests**: Validate params (filters, path handling), add helpful logging/errors. Where feasible, add unit/feature tests for CSV parsing and categorization logic. Confirm CSRF handling for any actions beyond display.
