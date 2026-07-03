<?php

namespace Uneca\Chimera\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Uneca\Chimera\Mcp\Resources\ArtefactExampleFile;
use Uneca\Chimera\Mcp\Resources\ArtefactExampleIndex;
use Uneca\Chimera\Mcp\Resources\BreakoutQueryBuilderDoc;
use Uneca\Chimera\Mcp\Resources\PlotlyPatternsDoc;
use Uneca\Chimera\Mcp\Tools\CreateGauge;
use Uneca\Chimera\Mcp\Tools\CreateIndicator;
use Uneca\Chimera\Mcp\Tools\CreateMapIndicator;
use Uneca\Chimera\Mcp\Tools\CreateReport;
use Uneca\Chimera\Mcp\Tools\CreateScorecard;
use Uneca\Chimera\Mcp\Tools\EditChart;
use Uneca\Chimera\Mcp\Tools\EditGauge;
use Uneca\Chimera\Mcp\Tools\EditIndicator;
use Uneca\Chimera\Mcp\Tools\EditMapIndicator;
use Uneca\Chimera\Mcp\Tools\EditReport;
use Uneca\Chimera\Mcp\Tools\EditScorecard;
use Uneca\Chimera\Mcp\Tools\DeployPresetPack;
use Uneca\Chimera\Mcp\Tools\GetArtefactExamples;
use Uneca\Chimera\Mcp\Tools\GetDataSources;
use Uneca\Chimera\Mcp\Tools\GetPresetPacks;
use Uneca\Chimera\Mcp\Tools\GetReferenceValues;
use Uneca\Chimera\Mcp\Tools\ManagePageAssignment;
use Uneca\Chimera\Mcp\Tools\ReadDictionary;
use Uneca\Chimera\Mcp\Tools\StagePresetPack;
use Uneca\Chimera\Mcp\Tools\ValidateArtefact;

#[Name('Dashboard Starter Kit')]
#[Version('0.0.1')]
#[Instructions(<<<'MARKDOWN'
This MCP server helps AI coding agents create and edit dashboard artefacts.

## Failure protocol

Every tool depends on a running MCP server, the database, and (for artefact
tools) the `chimera:make-artefact` generator command. If any tool call fails —
whether from a connection error, validation error, missing dictionary, or
runtime exception — report the error verbatim to the user and stop. Do NOT fall
back to Artisan commands, manual file/Database writes, `laravel-boost_database-schema`,
file exploration, or any other workaround. Bypassing the tools skips permission
creation, transaction safety, and stub generation, leaving the project in an
inconsistent state.

If `read-dictionary` reports that no dictionary is registered, abort the workflow
and ask the user to run `php artisan chimera:mcp-init` in the consumer app. The
dictionary is the only source of truth for record→table and item→column mappings.

## Prerequisites

Before any artefact work, the consumer app must have run
`php artisan chimera:mcp-init` to register dictionary files for each data
source. Until this command has been run, every tool except `get-data-sources`
will refuse with an "MCP server has not been initialized" error.

If any tool returns that error, stop and ASK THE USER to run
`php artisan chimera:mcp-init`. Do NOT attempt to run the command yourself —
it prompts the user interactively for dictionary file paths that only the user
can provide. Do NOT use `get-artefact-examples`,
`laravel-boost_database-schema`, database queries, file exploration, or any
other workaround to gather data structure information while uninitialized.
Ask the user and wait.

## Artefacts

Five types:
- **Indicator** (Plotly chart — "indicator" alone in a user request means this specific artefact type, not the generic concept of a dashboard metric)
- **Scorecard** (numeric summary card)
- **Gauge** (visual threshold card)
- **MapIndicator** (colored map area)
- **Report** (Excel export)

Every artefact lives in two places: a database record and a PHP class file.

## Available Resources

This server exposes several MCP Resources for reference material:

| URI Pattern | Description |
|-------------|-------------|
	| `docs://breakout-query-builder` | Full BreakoutQueryBuilder API reference — method chain, join strategies, gotchas, and usage patterns for all artefact types. |
	| `docs://plotly-patterns` | Plotly chart patterns — trace configurations, meta.columnNames reference, hovertemplate formatting, layout adjustments for bar, scatter, pie, histogram, line, area, and box charts. |
	| `examples://artefact/{type}` | Lists available examples for a type (`scorecard`, `gauge`, `indicator`, `map-indicator`, `report`). Returns JSON with name and description. |
	| `examples://artefact/{type}/{name}` | Returns the complete PHP source of a single example as `text/x-php`. |

## Critical Rules

- "Indicator" means a Plotly chart.
- **Complete Steps 0-1-2 in order. Do not skip to creation (Step 3) before reading examples (Step 2).**
- **Do NOT explore consumer app files (`app/`, `vendor/`, `config/`) for code patterns.** Call `get-artefact-examples` instead — it is the ONLY source of code patterns.
- **Do NOT use `laravel-boost_database-schema`, database queries, or file exploration to discover the data structure.** `read-dictionary` is the ONLY source of record→table and item→column mappings. If it fails, abort (see Failure protocol).
- Example files show complete `getData()` implementations. Use them as templates and adapt `select`/`from`/`where`/`groupBy` to your specific data.
- `safeDivide()` and `toDataFrame()` are global helper functions autoloaded via Composer — no import needed. `safeDivide($numerator, $denominator, $integerDivision = false)` safely computes a ratio (returns 0 when denominator is 0 or non-numeric); pass `true` for integer division via `intdiv`. `toDataFrame($collection)` converts a row-oriented Collection into a column-oriented array (`['col' => [values...], ...]`) for Plotly chart consumption.

## Required Workflow

### Step 0: List data sources
Call `get-data-sources` first. The `name` field is the exact value you pass as
`data_source` to create tools. Always confirm with the user before proceeding.

### Step 1: Read the dictionary
Call `read-dictionary` with the chosen `data_source`. Use `summary: true` first
to browse the structure, then drill down with `record_name` and/or `item_name`.

Each record has a `breakoutTable` property — the lowercase table name to use in
`BreakoutQueryBuilder->from()` calls (e.g. `"POP_REC"` → `"pop_rec"`). Value
sets tell you what coded values mean (e.g. P11=1 means "Male").

If `read-dictionary` returns an error about no dictionary being registered,
stop the workflow and ask the user to run `php artisan chimera:mcp-init`. Do
not continue to Step 2.

### Step 2: Read example implementations
Before creating an artefact, read example implementations for the matching type.
Call `get-artefact-examples` with `type` (e.g. `"scorecard"`) to list available
examples, then call it again with `type` and `name` (e.g.
`"scorecard"` + `"TotalPopulation"`) to get the full PHP source.

Examples show complete `getData()` implementations. Study the pattern that best
matches the user's query and use it as your template in Step 4.

**Namespace caveat:** Example files live under `App\Livewire\Scorecard\Demographics\…`
(or similar category folders). That `Demographics` directory is just the example's
home — the create tool does NOT reproduce it. Created artefacts are placed under a
directory derived from the data source **title** (e.g. `KenyaCensus`, not
`Demographics`). When calling `validate-artefact`, `edit-*`, `manage-page-assignment`,
or any name-bearing tool after creation, ALWAYS use the full prefixed name returned
by the create tool (e.g. `"KenyaCensus/TotalPopulation"`), never the example's
directory and never the bare name you supplied as input.

### Step 3: Create the artefact
Call `create-indicator`, `create-scorecard`, `create-gauge`, `create-report`,
or `create-map-indicator`.

**`name` parameter:** Provide only the artefact name (e.g. `"BirthRate"`). The
data source title (trimmed of all spaces and converted to title case) is auto-prepended as a directory (`"Households/BirthRate"`).

**Name tracking (critical):** The create tool stores the artefact with this
prefixed name (e.g. `"KenyaCensus/BirthRate"`, NOT the bare `"BirthRate"` you
passed). The success response echoes that full name. **All subsequent tools**
(`edit-chart`, `validate-artefact`, `edit-indicator`, `edit-*`,
`manage-page-assignment`) require this full prefixed name as their `name`
parameter — never the bare name from your input. Copy it verbatim from the
create tool's response.

The create tools generate a stub with an empty `getData()` and a sensible
default chart layout. Do NOT pass `chart_type` — all chart visualization is
configured later in Step 5 via `edit-chart`.

### Step 4: Implement getData()
The create tools generate a stub with an empty `getData()`. You MUST implement it:

1. Read the generated file at the path shown in the creation response
2. Refer to the dictionary structure from Step 1 to map records→table names, items→column names
3. Refer to the example you read in Step 2 for the query pattern
4. Build your query with `BreakoutQueryBuilder`. The return shape depends on the artefact type:

**Indicators and Reports** — return a Collection of rows:
```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select([DB::raw('COUNT(*) AS total')])
        ->from(['pop_rec'])                // lowercase dict record name, always an array
        ->where(["sex = 'Male'"])          // array of raw SQL conditions, joined with AND
        ->groupBy(['area_code'])
        ->lastlyAreaLeftJoinData()         // optional: adds area name column
        ->get();
}
```

**Scorecards** — alias SELECT columns to `value` and `diff`, return `getSingleRow()` directly:
```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->scorecard->data_source, $filterPath))
        ->select([DB::raw('COUNT(*) AS value'), DB::raw('NULL AS diff')])
        ->from(['pop_rec'])
        ->getSingleRow();
}
```
The base class reads `value` and `diff` from the first row (configurable via `$valueField`, `$diffField`).
Set `diff` to `NULL` to hide the trend arrow.

If PHP post-processing is needed (e.g. `Number::format`, `safeDivide`), use `->map()`:
```php
return (new BreakoutQueryBuilder($this->scorecard->data_source, $filterPath))
    ->select([DB::raw('COUNT(*) AS value')])
    ->from(['pop_rec'])
    ->getSingleRow()
    ->map(fn ($row) => (object) [
        'value' => Number::format($row->value),
        'diff' => null,
    ]);
```

**Gauges** — alias the SELECT column to `value`, return `getSingleRow()` directly:
```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->gauge->data_source, $filterPath))
        ->select([DB::raw('COUNT(*) AS value')])
        ->from(['pop_rec'])
        ->getSingleRow();
}
```
The base class reads `value` from the first row (configurable via `$valueField`).

If PHP post-processing is needed, use `->map()`:
```php
return (new BreakoutQueryBuilder($this->gauge->data_source, $filterPath))
    ->select([DB::raw('COUNT(*) AS value')])
    ->from(['pop_rec'])
    ->getSingleRow()
    ->map(fn ($row) => (object) ['value' => Number::format($row->value)]);
```

**Map Indicators** — return a Collection with `value` and `area_code` columns. Also declare `$bins` and `SELECTED_COLOR_CHART` in the class body:
```php
public array $bins = [0, 50, 75, 100];
public const SELECTED_COLOR_CHART = 'nephritis';

public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->mapIndicator->data_source, $filterPath))
        ->select(['COUNT(*) AS value'])
        ->from(['pop_rec'])
        ->groupBy(['area_code'])
        ->get();
}
```

**BreakoutQueryBuilder reference:**

| Method | Notes |
|--------|-------|
| `select([...])` | You can use any valid SQL functions (aggregate) for expressions. Aliases must match Plotly `meta.columnNames`. |
| `from([...])` | Lowercase dict record name, always an array. You can pass in multiple tables and they will all be inner joined. |
| `where([...])` | Array of raw SQL condition strings joined with `AND`. **NOT** `->where('col', 'value')` like Laravel's query builder. Pass as many conditions as needed in a single array: `->where(["sex = 'Male'", "age > 18"])`. |
| `groupBy([...])` | Required with aggregate functions. |
| `orderBy([...])` | Append `ASC` or `DESC`. |
| `lastlyAreaLeftJoinData()` | Appends area name column. Needs `groupBy(['area_code', ...])` first. |
| `get()` | For indicators, map indicators, reports. Returns Collection. |
| `getSingleRow()` | Returns Collection with 0 or 1 items. Use for scorecards/gauges, then wrap in the expected return format (see above). |

Read the `docs://breakout-query-builder` resource for the complete API reference including join strategies, gotchas, and more.

**Overridable properties (set in the class body):**

| Property | Artefact | Default | Notes |
|----------|----------|---------|-------|
| `$unit` | Scorecard, Gauge | `'%'` | Suffix after the displayed value |
| `$outOf` | Gauge | `100` | Maximum value for the gauge arc |
| `$colorThresholds` | Gauge | `[70 => 'text-red-500', 90 => 'text-amber-500', 101 => 'text-green-500']` | Threshold → Tailwind color class |
| `$bins` | MapIndicator | `[]` | **Required.** Value ranges for map coloring (e.g. `[0, 50, 75, 100]`) |
| `SELECTED_COLOR_CHART` | MapIndicator | none | **Required.** Color palette: `alizarin`, `wisteria`, `peter-river`, `nephritis`, `sunflower`, `pumpkin`, `silver`, `rag` |

5. **Column aliases MUST match `meta.columnNames`** in the Plotly traces.
   If a trace has `"meta": {"columnNames": {"y": ["males"]}}`, your SELECT must
   include `AS males`. You will configure the traces in Step 5 via `edit-chart`.

6. **Code-to-label mapping:** Use dictionary value sets or Plotly's
   `tickvals`/`ticktext` layout. Avoid `CASE WHEN code=5 THEN 'Label'` in SQL.
   For per-category boolean columns, use:
   `SUM(CASE WHEN code = 5 THEN 1 ELSE 0 END) AS label`

7. **Proceed to Step 5 to design the chart.** Do NOT skip to validation.

### Step 5: Design the chart (indicators only)

After implementing getData(), you must configure the Plotly visualization before
it can render. Call `edit-chart` with the indicator name and your hand-crafted
Plotly trace definitions:

- **`name`**: The FULL indicator name including the data source prefix directory
  (e.g. `"KenyaCensus/BirthRate"`), as returned by the create tool in Step 3.
  Do NOT pass the bare name you supplied at creation — that will not be found.
- **`data`**: Array of Plotly trace objects. Each trace requires:
  - `type` — chart type. If the user didn't specify one, recommend based on the
    data:
    - **Bar** — compare categories across groups
    - **Line** — show trends over time or along a sequence
    - **Scatter** — show relationship between two variables
    - **Pie** — show proportions of a whole
    - **Histogram** — show distribution of a continuous variable
    - **Area** — emphasize magnitude of change over time
    - **Box** — show spread, quartiles, and outliers
  - `meta.columnNames` — maps trace properties (e.g. `x`, `y`, `labels`, `values`, `text`)
    to the SQL aliases from your `getData()` SELECT. These MUST match exactly.
    For standard charts, each property maps to a single alias (`"y": ["total"]`).
    For **multicategory (nested) x-axes**, set `"x"` to an array of two or more
    aliases (`"x": ["sex", "education_level"]`). This produces an array-of-arrays
    that Plotly renders as nested tick labels at each x position.
    See the **Multicategory (Nested) X-Axis** section in `docs://plotly-patterns`.
  - `name` — display label for the legend
  - `hovertemplate`, `marker`, `text` — optional Plotly styling properties
- **`layout`** (optional): Plotly layout overrides (title, axis titles, margins, etc.)

The tool validates that every column referenced in `meta.columnNames` exists in
the `getData()` result. If validation fails, fix the mismatch and try again.

Refer to the examples from Step 2 and use your Plotly knowledge to craft
appropriate traces for the chart type requested by the user. Read the
`docs://plotly-patterns` resource for complete trace configurations, hovertemplate
formatting, and layout adjustments for every chart type.

Do **NOT** set `layout.title` unless the user explicitly requests it. The
`chart-card` component already renders the indicator title and description
**above** the Plotly chart. Adding a Plotly title creates visual duplication.

After the chart design is saved, proceed to Step 6 to validate.

### Step 6: Validate

After implementing getData(), call `validate-artefact` with the `type` and the
FULL prefixed `name` returned by the create tool (Step 3). This will:

1. Confirm the data source is connectible
2. Instantiate the artefact class from the generated file
3. Execute `getData('')` (national-level scope)
4. Return `{success, rows_returned, columns, sample_data}`

If validation fails, fix the error (column name mismatch, missing import, etc.) and call `validate-artefact` again. The dictionary and your understanding of the data model are sufficient — do not read other generated files for reference.

**After validation passes, stop.** No tests, no Pint, no exploring.

## Editing Artefacts
Use these tools after creation, only if the user requests changes to artefacts:
- `edit-indicator` — title, description, help, scope (use `edit-chart` for traces and layout)
- `edit-scorecard` — title, scope
- `edit-gauge` — title, subtitle
- `edit-map-indicator` — title, description
- `edit-report` — title, description
- `manage-page-assignment` — attach/detach artefacts to/from pages

## Breakout Database
Questionnaire responses live in a MySQL database. Records become tables
(lowercased), items become columns. Value set codes (not labels) are stored.
Access it via `BreakoutQueryBuilder` — use Laravel Boost's `database-query` tool (if available)
for ad-hoc exploration.

## CSPro Dictionary Files
JSON (CSPro 8+) or INI (pre-8.0) format. Use `read-dictionary` with `data_source`
(pre-registered via `chimera:mcp-init` — see Prerequisites), or pass raw
`content`.

Parsed structure: `levels[]` → `records[]` → `items[]` → `valueSets[]`.

## Area Hierarchy & $filterPath
PostgreSQL **ltree** dotted paths: `"africa.ethiopia.addis_ababa"`. Each segment
is an area code. `BreakoutQueryBuilder` consumes `$filterPath` automatically —
no manual area WHERE clauses needed.

`$filterPath` scope:
- `""` = national | `"country"` = first admin level
- `"country.region"` = second admin level | each `.` = one level deeper

MARKDOWN)]
class DashboardStarterKit extends Server
{
    protected array $tools = [
        GetDataSources::class,
        GetReferenceValues::class,
        GetArtefactExamples::class,
        GetPresetPacks::class,
        StagePresetPack::class,
        DeployPresetPack::class,
        ReadDictionary::class,
        CreateScorecard::class,
        CreateGauge::class,
        CreateIndicator::class,
        CreateMapIndicator::class,
        CreateReport::class,
        ValidateArtefact::class,
        EditChart::class,
        EditScorecard::class,
        EditGauge::class,
        EditIndicator::class,
        EditMapIndicator::class,
        EditReport::class,
        ManagePageAssignment::class,
    ];

    protected array $resources = [
        ArtefactExampleIndex::class,
        ArtefactExampleFile::class,
        BreakoutQueryBuilderDoc::class,
        PlotlyPatternsDoc::class,
    ];

    protected array $prompts = [
        //
    ];
}
