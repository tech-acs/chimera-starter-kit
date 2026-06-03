<?php

namespace Uneca\Chimera\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Uneca\Chimera\Mcp\Tools\CreateGauge;
use Uneca\Chimera\Mcp\Tools\CreateIndicator;
use Uneca\Chimera\Mcp\Tools\CreateMapIndicator;
use Uneca\Chimera\Mcp\Tools\CreateReport;
use Uneca\Chimera\Mcp\Tools\CreateScorecard;
use Uneca\Chimera\Mcp\Tools\EditGauge;
use Uneca\Chimera\Mcp\Tools\EditIndicator;
use Uneca\Chimera\Mcp\Tools\EditMapIndicator;
use Uneca\Chimera\Mcp\Tools\EditReport;
use Uneca\Chimera\Mcp\Tools\EditScorecard;
use Uneca\Chimera\Mcp\Tools\ParseDictionary;

#[Name('Dashboard Artefact Generator')]
#[Version('0.1.0')]
#[Instructions(<<<'MARKDOWN'
This MCP server helps AI coding agents create and edit dashboard artefacts for the Chimera Dashboard Starter Kit.

## Artefacts

The dashboard has five artefact types: **Indicator** (Plotly chart), **Scorecard** (numeric card), **Gauge** (visual threshold), **MapIndicator** (colored map), and **Report** (Excel export).

Every artefact lives in **two places**:
1. **Database** — an Eloquent model record with metadata
2. **Filesystem** — a PHP class that extends a base component and implements `getData()`

## Workflow

1. **Parse the dictionary** — Use `parse_dictionary` to read a CSPro `.dcf` file (JSON format) and learn the available levels, records, and items (fields) for a data source. Items have a `name`, `contentType` (numeric/alpha), `length`, and optional `valueSets` (coded values).

2. **Create the artefact** — Use `create_indicator`, `create_scorecard`, `create_gauge`, `create_report`, or `create_map_indicator`. Each tool creates both the DB record and the filesystem component class from a stub. For indicators, you can also pass Plotly traces (`data`) and layout (`layout`).

3. **Implement `getData()`** — Each artefact component must implement a `getData(string $filterPath): Collection` method. Use `BreakoutQueryBuilder` to build queries against the data source database. See `https://github.com/tech-acs/chimera-starter-kit` for the BreakoutQueryBuilder API.

4. **Edit artefacts** — Use the `edit_*` tools to update metadata, Plotly traces/layout (for indicators), or other properties.

## Plotly Reference

For indicator traces and layout, use the Plotly JavaScript charting library format:
https://plotly.com/javascript/

## CSPro Dictionary Files

CSPro dictionary files (`.dcf`) are a basically the census or survey questionnaire in JSON or ini file format. They have this structure:
- `levels[]`: Questionnaire hierarchy levels (e.g., Household, Person)
- `levels[].records[]`: Data entry records (e.g., POPULATION_RECORD)
- `levels[].records[].items[]`: Fields with name, contentType, length, labels, valueSets

## Breakout Database
All the responses from the questionnaires get transformed into a relational database (MySQL) and it is this database that is the main data source for the dashboard starter kit.

The structure of the breakout database is dictated by the dictionary. Records become tables and items become columns. For the data itself, the values of the valueSets are stored rather than the labels.

To access this database use Laravel Boost's database query tool

## Area Hierarchy and $filterPath

Areas in the dashboard are stored in PostgreSQL using the **ltree** extension — a dotted-path hierarchy like `"africa.ethiopia.addis_ababa.gulele"`. Each segment is an area code.

The `$filterPath` parameter passed to every artefact's `getData()` controls the geographic scope dynamically:
- `""` (empty string) → national level, includes all areas
- `"country_code"` → first admin level, scoped to that area
- `"country_code.region_code"` → second admin level, scoped within that region
- Each dot (`.`) descends one level deeper in the hierarchy

`BreakoutQueryBuilder` consumes `$filterPath` automatically in its constructor — it generates the SQL WHERE clauses that restrict the query to the given area and its descendants. You never need to build area filters manually.

### Typical getData() pattern

```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select(["COUNT(*) AS household_count", "AVG(HH_AGE) AS avg_age"])
        ->from(['POPULATION_RECORD'])
        ->where(["HH_SEX = '1'"])
        ->groupBy(['HH_DISTRICT'])
        ->orderBy(['household_count DESC'])
        ->get();
}
```

### Chart type selection and data preparation

The plotly-chart-editor in the dashboard supports these chart types: **bar, line, scatter, pie, histogram, area, box, sunburst**.

Use your knowledge of statistics and chart design to choose the right type for the data:
- **Bar** — compare values across categories
- **Line** — show trends over time
- **Pie** — show proportions of a whole
- **Histogram** — show distribution of a continuous variable
- **Scatter** — show relationship between two variables
- **Area** — emphasize magnitude of change over time
- **Box** — show spread, quartiles, and outliers
- **Sunburst** — show hierarchical proportions

Prepare **all the data** the chart needs in `getData()`. The Plotly traces stored in the indicator's `data` column reference the output columns via `meta.columnNames` — the `Chart` base class's `getTraces()` method resolves these references at render time, mapping column names to trace properties (x, y, etc.).

### Level applicability

Artefacts can declare they are not applicable at certain hierarchy levels via the `HasLevelDiscrimination` trait. The `supportsLevel($filterPath)` method checks this automatically — if it returns false, the artefact shows "Not applicable" instead of data.

MARKDOWN)]
class DashboardArtefactGenerator extends Server
{
    protected array $tools = [
        ParseDictionary::class,
        CreateIndicator::class,
        EditIndicator::class,
        CreateScorecard::class,
        EditScorecard::class,
        CreateGauge::class,
        EditGauge::class,
        CreateReport::class,
        EditReport::class,
        CreateMapIndicator::class,
        EditMapIndicator::class,
    ];
}



