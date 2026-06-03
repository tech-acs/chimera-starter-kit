# Chimera Dashboard Starter Kit

A Laravel package that provides a starting point for census and survey management dashboards with reactive charts, area-based filters, map indicators, and more.

## Language

### Artefact Types

**Indicator**:
A data element depicted graphically as a Plotly chart (bar, line, pie, etc.). Extends the `Chart` Livewire component base class. Stored in both the database (`indicators` table — metadata, traces, layout) and the filesystem (`app/Livewire/` — component class with `getData()`).
_Avoid_: Chart, visualization

**Scorecard**:
A numeric summary displayed as a card with a value, an optional diff indicator, and a colored background. Extends `ScorecardComponent`. Stored in both the database and filesystem.
_Avoid_: Summary card, KPI card

**Gauge**:
A visual gauge/threshold indicator shown on the Area Insights page. Extends `GaugeComponent`. Stored in both the database and filesystem.

**Map Indicator**:
A map-based representation of data with boundaries colored by value bins. Extends `MapIndicatorBaseClass`. Stored in both the database and filesystem.

**Report**:
A compiled tabular dataset exported as Excel. Uses `ReportBaseClass`. Scheduled for automatic generation and can be emailed to users.

**DataSource**:
A database connection originating from a census/survey questionnaire (often CSPro). Each DataSource has a name, start/end dates, and metadata. Also referred to interchangeably as a questionnaire.

### Infrastructure

**BreakoutQueryBuilder**:
A fluent SQL builder that constructs queries against census/survey data sources. Handles table joins, area-level filtering, partial/deleted record exclusion, and area-based left/right/cross joins.

**CSPro Dictionary (DCF)**:
A JSON file that describes the structure of a CSPro questionnaire's data: levels, records, items (fields) with types, lengths, labels, and value sets. This is the source-of-truth for the database schema behind each DataSource.

**Dashboard Artefact**:
A generic term covering all artefact types (Indicator, Scorecard, Gauge, MapIndicator, Report). An artefact always lives in two places: a database record (for metadata) and a filesystem class (for logic).

**MCP (Model Context Protocol)**:
A protocol that exposes tools to AI coding agents (opencode, Claude, etc.). This project uses `laravel/mcp` to build an MCP server called "Dashboard Artefact Generator" that helps create and edit dashboard artefacts.

### Workflow

**getData()**:
The method that every artefact component must implement. It receives a `$filterPath` (area hierarchy path) and returns a `Collection`. Uses `BreakoutQueryBuilder` to query the DataSource's database.

**Plotly Traces (data)**:
The JSON array stored in an indicator's `data` column. Each trace defines one series in the chart (e.g., bars for 'Male', line for 'Female'). Uses the Plotly trace format (type, x, y, name, etc.).

**Plotly Layout**:
The JSON object stored in an indicator's `layout` column. Defines chart layout properties (title, axis config, legend, margins, colorway, etc.).

## Flagged ambiguities

- **"Data source"** vs **"questionnaire"**: These are used interchangeably in this project. Both refer to the same concept — a survey/census database connection with its metadata.
- **"Artefact"** vs **"component"**: An artefact is the domain concept (Indicator, Scorecard, etc.). A component is its filesystem Livewire class or Blade view. They refer to the same thing from different angles.

## Example dialogue

**Developer**: I need to add an "Age by Sex" indicator to the dashboard.

**Domain Expert**: You'll create an Indicator artefact. That means: (1) a new record in the `indicators` table with title, data source, and Plotly data/layout, and (2) a Livewire component in `app/Livewire/` that extends `Chart` and implements `getData()`.

**Developer**: What SQL should I write in getData()?

**Domain Expert**: Check the CSPro dictionary file for your data source. It will show you the levels (e.g., Household, Person), records (e.g., POPULATION_RECORD), and items (e.g., HH_SEX, HH_AGE). Use `BreakoutQueryBuilder` to build the query — it handles the joins and area filtering for you.

**Developer**: And the chart itself?

**Domain Expert**: The Plotly traces and layout are stored as JSON in the database. The visual editor (plotly-chart-editor) helps configure them, but you can also set them programmatically. The `data` column holds an array of trace objects, the `layout` column holds the chart configuration.

**Developer**: What about the Area Insights page?

**Domain Expert**: That uses Gauges (visual thresholds) and Scorecards (numeric values). They follow the same pattern — create a model record and a component file with getData().
