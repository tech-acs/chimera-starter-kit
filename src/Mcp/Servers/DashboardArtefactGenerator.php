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
use Uneca\Chimera\Mcp\Tools\ListDataSources;
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

## Required Workflow — ALWAYS follow this order

### Step 0: List data sources and ask the user
Call `list-data-sources` first to see what questionnaires are available.
The response shows each data source's ID, name, title, active status, and date range.
If multiple are listed, tell the user which ones are available and ask which one to use.
If only one exists, confirm with the user anyway.
Always let the user choose — never assume a data source.

The data source's **name** (from the `data_sources` table, case-sensitive) is what
you pass as the `data_source` parameter to create_* tools. For example, if the
name is "households", use exactly `"households"` — not "Households" or "Household".

### Step 1: Parse the dictionary
Call `parse-dictionary` with the chosen data source name (the `data_source`
parameter) to learn the questionnaire structure: levels, records (tables), items
(columns), types, lengths, and value sets. Records become tables and items become
columns in the breakout database. The value sets tell you what coded values mean
(e.g. P11=1 means "Male", P11=2 means "Female").

To avoid overwhelming output (some INI dictionaries can produce 475K+ lines):
1. First use `summary: true` to see just the record and item names/types
2. Then drill into specific fields with `record_name` and/or `item_name`

Each record in the output has a `breakoutTable` property — the table name in the
breakout database (e.g. `"POP_REC"` → `"pop_rec"`). Use this in your
`BreakoutQueryBuilder->from()` calls.

Use the parsed structure to design your queries and Plotly traces.

### Step 2: Create the artefact
Call `create_indicator`, `create_scorecard`, `create_gauge`, `create_report`,
or `create_map_indicator` with the chosen data source.

**IMPORTANT: Do NOT explore other indicator/scorecard/etc files in the
codebase before or after creating.** The template and the examples in these
instructions are complete. Looking at other files wastes time and is not
needed — every artefact follows the same pattern.

For indicators, pass a `chart_type` if the user specified one (bar, line, scatter,
pie, histogram, area, box, sunburst). The tool auto-generates sensible Plotly
traces and layout with correct `meta.columnNames`. If the user didn't specify a
chart type, recommend one based on the data and ask them to confirm.

The auto-generated traces are a single-trace chart: a simple bar has
`meta.columnNames: {x: ["x"], y: ["y"]}`. This is sufficient for simple
charts — just name your `getData()` columns `x` and `y` and leave the
traces alone.

If you need a **multi-trace stacked chart** (one trace per category, e.g.
PopulationByReligion with one trace per religion), the auto-generated
single trace won't suffice. In that case you MUST edit the indicator
after creation (Step 4) with explicit traces — one per category — each
pointing to a `{category}_pct` column. See the existing indicator examples
for the exact pattern. Only pass explicit `data` or `layout` if you need
customisation the auto-generated defaults don't cover.

### Step 3: Implement getData()
The `create_*` tools generate a stub file with an empty `getData()`. You MUST
implement it by editing the file:
  a. Read the generated file at `app/Livewire/{Name}.php`
  b. Use `BreakoutQueryBuilder` to build your query against the breakout database
     (see examples below)
   c. The columns your query returns must match the `meta.columnNames` in your
      Plotly traces. If a trace has `"meta": {"columnNames": {"y": ["total_males", "total_females"]}}"`,
      your SELECT must include columns named `total_males` and `total_females`.
      Using `chart_type` on `create-indicator` pre-configures these columnNames
      for you — review them in the generated file and ensure your query matches.
   d. For SQL CASE statements, there are two distinct patterns:
      - **Bad pattern — code-to-label mapping:** `CASE WHEN h30 = 5 THEN 'Iron sheets' END`.
        Do NOT do this. It is fragile and wastes tool calls debugging it.
        Instead return raw codes and use Plotly's `tickvals`/`ticktext` layout
        or PHP `->map()` post-processing. The dictionary's value sets (from
        Step 1) contain the code-to-label mapping.
      - **Good pattern — boolean per-category columns:**
        `SUM(CASE WHEN h30 = 5 THEN 1 ELSE 0 END) AS iron_sheets`.
        This is the correct approach for multi-trace stacked charts (one
        trace per category). Every existing indicator in this codebase
        follows this pattern. Use it when creating a stacked percentage
        chart with separate traces per category.
   e. If the artefact should compare data across areas (e.g. show chart by county),
      add `->groupBy(['area_code'])->lastlyAreaLeftJoinData()` to the query

**After implementing getData(), STOP IMMEDIATELY.** Do NOT run any commands —
no tests, no Pint, no syntax checks, no Artisan commands. Do NOT explore or
read any other files in the codebase. The template and examples above are
complete. There is nothing else to verify. Your work on this artefact is done.

### Step 4: Edit artefacts
Use the `edit_*` tools to update metadata, Plotly traces/layout (for indicators),
or other properties after creation.

## BreakoutQueryBuilder Examples

BreakoutQueryBuilder is a fluent SQL builder that handles area filtering, table joins,
and database differences automatically. Always construct it with the data source
name and filter path:

```php
new BreakoutQueryBuilder($this->indicator->data_source, $filterPath)
```

### Example 1: Simple count of records

```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select([DB::raw('COUNT(*) AS total')])
        ->from(['pop_rec'])
        ->get();
}
```

### Example 2: Count by category (bar/pie chart)

```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select(['P17', DB::raw('COUNT(*) AS total')])
        ->from(['pop_rec'])
        ->groupBy(['P17'])
        ->orderBy(['total DESC'])
        ->get();
}
```
This returns rows like `[{P17: 1, total: 500}, {P17: 2, total: 300}]`.
Map the P17 codes to labels (from dictionary value sets) in the Plotly trace x values.

### Example 3: Conditional counts (male/female breakdown)

```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select([
            DB::raw('COUNT(*) AS total'),
            DB::raw('SUM(CASE WHEN P11 = 1 THEN 1 ELSE 0 END) AS males'),
            DB::raw('SUM(CASE WHEN P11 = 2 THEN 1 ELSE 0 END) AS females'),
        ])
        ->from(['pop_rec'])
        ->get();
}
```

### Example 4: Age grouping (population pyramid style)

```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select([
            DB::raw("CONCAT(FLOOR(P12/5) * 5, '-', FLOOR(P12/5) * 5 + 4) AS age_range"),
            DB::raw('FLOOR(P12/5) * 5 AS range_start'),
            DB::raw('SUM(CASE WHEN P11 = 1 THEN 1 ELSE 0 END) AS males'),
            DB::raw('SUM(CASE WHEN P11 = 2 THEN 1 ELSE 0 END) AS females'),
        ])
        ->from(['pop_rec'])
        ->groupBy(['range_start'])
        ->orderBy(['range_start'])
        ->get();
}
```

### Example 5: Household-level aggregation from housing record

```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select(['H30', DB::raw('COUNT(*) AS total')])
        ->from(['housing_rec'])
        ->groupBy(['H30'])
        ->orderBy(['total DESC'])
        ->get();
}
```

### Example 6: With area breakdown

```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select(['P17', DB::raw('COUNT(*) AS total')])
        ->from(['pop_rec'])
        ->groupBy(['area_code', 'P17'])
        ->lastlyAreaLeftJoinData()
        ->get();
}
```
`lastlyAreaLeftJoinData()` attaches area names to the result so the chart can
display area labels. Requires `groupBy(['area_code', ...])`.

### Example 7: Post-processing with percentages

```php
public function getData(string $filterPath): Collection
{
    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select([
            DB::raw('COUNT(*) AS total'),
            DB::raw('SUM(CASE WHEN P11 = 1 THEN 1 ELSE 0 END) AS males'),
            DB::raw('SUM(CASE WHEN P11 = 2 THEN 1 ELSE 0 END) AS females'),
        ])
        ->from(['pop_rec'])
        ->get()
        ->map(function ($item) {
            $item->male_percentage = round(($item->males / $item->total) * 100, 1);
            $item->female_percentage = round(($item->females / $item->total) * 100, 1);
            return $item;
        });
}
```

## Complete Walkthrough: RoofMaterial Bar Chart

This example shows every step for creating a bar chart indicator, from parsing
the dictionary to implementing `getData()`. Follow this template for your own
indicators — do NOT look at other files in the codebase; this is the full template.

### Step-by-step

**1. Parse the dictionary (summary mode):**
```
parse-dictionary(dictionary_name: "Households", summary: true)
```
Response shows the record names. Find the record containing your field.

**2. Drill into the specific record and item:**
```
parse-dictionary(dictionary_name: "Households", record_name: "HOUSING_REC", item_name: "H30")
```
Response shows H30 is an Alpha(1) field inside HOUSING_REC. The record's
`breakoutTable` is `"housing_rec"`. The value sets show the coded values
(e.g. 1=Corrugated iron sheets, 2=Tiles, 3=Concrete, ...).

**3. Create the indicator with chart_type:**
```
create-indicator(
  name: "RoofMaterial",
  title: "Households by Roof Material",
  data_source: "households",
  chart_type: "bar"
)
```
The tool auto-generates this Plotly trace and layout:
```json
{
  "data": [{"type": "bar", "x": [], "y": [], "name": "", "meta": {"columnNames": {"x": ["x"], "y": ["y"]}}}],
  "layout": {"barmode": "relative", "colorway": [...], "font": {...}, "margin": {...}}
}
```

**4. Read the generated file at `app/Livewire/RoofMaterial.php`:**
```php
<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Uneca\Chimera\Livewire\Chart;
use Uneca\Chimera\Services\BreakoutQueryBuilder;

class RoofMaterial extends Chart
{
    public function getData(string $filterPath): Collection
    {
        try {
            // TODO: Implement getData() method.
        } catch (\Exception $exception) {
            return collect();
        }
    }
}
```

**5. Implement getData() — the trace expects columns named "x" and "y":**
```php
public function getData(string $filterPath): Collection
{
    $labels = [
        1 => 'Corrugated iron sheets',
        2 => 'Tiles',
        3 => 'Concrete',
        4 => 'Thatch/grass',
        5 => 'Tin',
        6 => 'Asbestos sheets',
        7 => 'Canvas/tent',
        8 => 'Other',
    ];

    return (new BreakoutQueryBuilder($this->indicator->data_source, $filterPath))
        ->select(['H30', DB::raw('COUNT(*) AS y')])
        ->from(['housing_rec'])
        ->groupBy(['H30'])
        ->orderBy(['y DESC'])
        ->get()
        ->map(fn ($item) => (object) [
            'x' => $labels[(int) $item->H30] ?? $item->H30,
            'y' => $item->y,
        ]);
}
```

The query column aliases match `meta.columnNames`:
- `y` from `COUNT(*) AS y` matches `"y": ["y"]`
- `x` from the map's output property matches `"x": ["x"]`
- `from(['housing_rec'])` matches the record's `breakoutTable` from step 2

### Pattern summary

For bar/line/scatter/area charts:
- SELECT the category column (or a derived value) and alias it to match the trace's `columnNames`
- SELECT the value column (COUNT(*), SUM, etc.) and alias it
- Use `->map()` for code-to-label mapping using dictionary value sets
- Group by the category column if aggregating

For pie charts, alias to `labels` and `values`. For histogram, alias to just `x`.
The auto-generated `chart_type` defaults tell you the exact column names needed.

## Plotly Reference

For indicator traces and layout, use the Plotly JavaScript charting library format:
https://plotly.com/javascript/

### Chart type selection

Use your knowledge of statistics and chart design to choose the right type for the data:
- **Bar** — compare values across categories
- **Line** — show trends over time
- **Pie** — show proportions of a whole
- **Histogram** — show distribution of a continuous variable
- **Scatter** — show relationship between two variables
- **Area** — emphasize magnitude of change over time
- **Box** — show spread, quartiles, and outliers
- **Sunburst** — show hierarchical proportions

Prepare **all the data** the chart needs in `getData()`. The Plotly traces stored
in the indicator's `data` column reference the output columns via `meta.columnNames`
— the `Chart` base class's `getTraces()` method resolves these references at render
time, mapping column names to trace properties (x, y, etc.).

For value set labels (e.g. "Male"/"Female" instead of "1"/"2"), set the trace's
`x` array directly with the labels rather than referencing column names via meta.
Alternatively, use the `tickvals`/`ticktext` layout properties to map codes to labels
on the axis.

## CSPro Dictionary Files

CSPro dictionary files (`.dcf`) represent the census or survey questionnaire structure. Two formats are supported:

- **JSON format** (CSPro 8+): `{ "fileType": "dictionary", "name": "...", "levels": [...] }`
- **INI format** (pre-CSPro 8.0): `[Dictionary]`, `[Level]`, `[Record]`, `[Item]`, `[ValueSet]` sections

Use **your file-reading tool** to read the `.dcf` file content, then pass it to `parse_dictionary`. If the dictionary was pre-registered via `chimera:mcp-install`, you can pass the `dictionary_name` instead and the tool will read the file automatically.

Structure parsed from either format:
- `levels[]`: Questionnaire hierarchy levels (e.g., Household, Person)
- `levels[].records[]`: Data entry records (e.g., POPULATION_RECORD)
- `levels[].records[].items[]`: Fields with name, type, length, labels, valueSets
- `levels[].records[].items[].valueSets[]`: Coded values with names, labels, and value/range pairs

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
        ListDataSources::class,
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



