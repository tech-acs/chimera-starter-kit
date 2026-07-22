<?php

namespace Uneca\Chimera\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Description('Plotly chart patterns for indicators — trace configurations, meta.columnNames, hovertemplate formatting, layout adjustments, and worked patterns (stacked bars, reference lines, time series, text labels, aggregate-appended traces) for the supported chart types (bar, scatter, pie, histogram, line, area, box). Not an exhaustive Plotly API reference — consult the official Plotly reference for properties not covered here.')]
#[Uri('docs://plotly-patterns')]
class PlotlyPatternsDoc extends Resource
{
    public function handle(Request $request): Response
    {
        return Response::text(self::CONTENT);
    }

    private const CONTENT = <<<'DOC'
PLOTLY CHART PATTERNS FOR INDICATORS
=====================================

This doc covers the patterns our indicator examples produce plus base-class
behaviors you can't infer from Plotly alone. It is NOT an exhaustive API
reference. For any property/option not covered below, consult the official
reference: https://plotly.com/javascript/reference/index/

HOW meta.columnNames WORKS
  The edit-chart tool maps SQL column aliases to trace properties via
  meta.columnNames. The keys are trace property paths, the values are SQL aliases
  from your getData() SELECT.

  Values can be:
    - A single string:        "x": "area_name"
    - A single-element array: "y": ["total"]
    - A multi-element array (multicategory x):  "x": ["sex", "education_level"]

  The edit-chart tool validates all elements against your getData() SQL aliases.
  At render time:
    - Single-value properties → resolved as a flat Plotly data array
    - Multi-value properties (2+ columns) → resolved as an ARRAY OF ARRAYS
      (one inner array per column), which Plotly renders as a nested
      multicategory axis. See the MULTICATEGORY (NESTED) X-AXIS section below.

  For multi-trace charts (e.g. grouped bars for males and females), create
  **separate trace objects** in the data array, each with its own
  meta.columnNames pointing to a single SQL alias.

  For pie charts, use "labels" and "values" instead of "x" and "y".

──────────────────────────────────

PICKING A CHART TYPE BY DATA SHAPE
  Match the chart type to the shape your getData() returns. Each row cites an
  example file to study via get-artefact-examples.

  | Data shape                                  | Chart type            | Example to study          |
  |---------------------------------------------|-----------------------|---------------------------|
  | single category → value                     | bar or pie            | HouseholdsByWallMaterial  |
  | area → multiple y-columns                   | stacked/grouped bar   | Refusals, PopulationByAgeGroup |
  | date → value (time series)                  | line or bar           | HouseholdsByDay           |
  | date → value + target column                | line + target line    | ListedHouseholdsPerDay    |
  | area → rate + constant benchmark            | bar + constant ref line | MaternalMortalityRatio  |
  | continuous variable distribution            | histogram or box      | (none yet)                |

──────────────────────────────────

CHART TYPES

1. BAR (type: "bar")
  Vertical bars comparing values across categories.

  Trace structure:
    {
      "type": "bar",
      "meta": { "columnNames": { "x": ["area_name"], "y": ["total"] } },
      "name": "Population",
      "marker": { "color": "#2563eb" },
      "hovertemplate": "%{x}<br>%{y:,.0f}<extra></extra>"
    }

  Key properties:
    orientation: "v" (default) | "h" (horizontal)
    marker.color: single color or array
    text: labels on bars
    textposition: "inside" | "outside" | "auto"

  Multi-trace (grouped bars):
    Two traces sharing the same x, different y aliases.
    Layout barmode: "group" (default) | "stack" | "relative"

  With reference line:
    Trace 1: bar with y from data
    Trace 2: scatter/line with y from reference_value column

  Stacked bar (multiple y-columns per area):
    One trace per column, all sharing the same x alias. Set barmode: "stack".
    See the Refusals and PopulationByAgeGroup examples.

    [
      { "type": "bar", "meta": { "columnNames": { "x": ["area_name"], "y": ["children"] } }, "name": "Children" },
      { "type": "bar", "meta": { "columnNames": { "x": ["area_name"], "y": ["adults"] } }, "name": "Adults" },
      { "type": "bar", "meta": { "columnNames": { "x": ["area_name"], "y": ["elderly"] } }, "name": "Elderly" }
    ]
    Layout: { "barmode": "stack" }

  Bar with column-mapped text labels (value labels on bars):
    Map a computed column to the trace "text" property via meta.columnNames,
    then render it with texttemplate. See the PopulationByAgeGroup example
    (children_pct column).

    {
      "type": "bar",
      "meta": { "columnNames": { "x": ["area_name"], "y": ["children"], "text": ["children_pct"] } },
      "name": "Children",
      "texttemplate": "%{text}%",
      "textposition": "outside"
    }

MULTICATEGORY (NESTED) X-AXIS

  When each data point belongs to two or more category levels (e.g. Sex +
  Education Level, or County + Year), set meta.columnNames.x to an array
  of two or more column aliases:

  Trace configuration:
    {
      "type": "bar",
      "meta": {
        "columnNames": {
          "x": ["sex", "education_level"],
          "y": ["percentage"]
        }
      },
      "name": "% of Population",
      "hovertemplate": "%{x}<br>%{y:.1f}%<extra></extra>"
    }

  This produces a Plotly x value as an ARRAY OF ARRAYS — one inner array
  per category level:

    x: [['Male', 'Male', 'Male', 'Female', 'Female', 'Female'],
        ['None', 'Primary', 'Secondary', 'None', 'Primary', 'Secondary']]
    y: [21.3, 37.6, 19.2, 23.3, 37.7, 18.7]

  The chart renders nested tick labels:

         ┌─────────────────────┬─────────────────────┐
         │        Male         │       Female        │
         │ None  Pri  Sec  Trt │ None  Pri  Sec  Trt │
         └─────────────────────┴─────────────────────┘

  Requirements:
    - getData() must return separate columns for each category level
      (e.g. both "sex" and "education_level" are in the SELECT)
    - Every alias in the meta.columnNames.x array must match a SQL alias
      in getData() — edit-chart validates all of them
    - The layout MUST override xaxis.type to "multicategory" (Plotly v3+
      requirement). Without this, the default "category" from
      DEFAULT_LAYOUT renders the x-axis as a flat array instead of nested
      axis labels:
        layout: { xaxis: { type: "multicategory", tickangle: -45 } }
    - Data rows must be ordered so that the inner category cycles within
      the outer category (e.g. all Male rows grouped together, then all
      Female rows) to produce correct pairwise pairing

  Data ordering tip:
    Data rows must be ordered so that the inner category cycles within
    the outer category (e.g. all Male rows grouped together, then all
    Female rows). If you re-sort in PHP after the query (e.g. with
    ->sortBy()), ensure the outer category is the primary sort key:
      ->sortBy(fn($row) => array_search($row->sex, $sexOrder) * 10
                         + array_search($row->education_level, $educationOrder))
    The numeric weighting (* 10) ensures the outer category dominates
    the sort.

  Limitations:
    - Multi-column "x" works with bar, scatter, and line charts
    - Pie charts cannot use multicategory (they use labels/values)
    - Do NOT combine multicategory x with aggregateAppendedTraces

──────────────────────────────────

2. SCATTER (type: "scatter")
  Points with optional connecting lines.

  Trace structure:
    {
      "type": "scatter",
      "mode": "markers",
      "meta": { "columnNames": { "x": ["area_name"], "y": ["rate"] } },
      "name": "Literacy Rate",
      "marker": { "color": "#2563eb", "size": 8 },
      "hovertemplate": "%{x}<br>%{y:.1f}%<extra></extra>"
    }

  mode values:
    "markers" — points only
    "lines" — lines only
    "lines+markers" — both

  Key properties:
    marker.size: point size
    marker.color: point color
    line.shape: "linear" | "spline" | "hv" | "vh" | "hvh" | "vhv"

──────────────────────────────────

3. PIE (type: "pie")
  Proportional slices (uses labels + values instead of x + y).

  Trace structure:
    {
      "type": "pie",
      "meta": { "columnNames": { "labels": ["area_name"], "values": ["total"] } },
      "name": "Distribution",
      "hole": 0.4,
      "textinfo": "label+percent",
      "hovertemplate": "%{label}<br>%{value:,.0f} (%{percent})<extra></extra>"
    }

  Key properties:
    hole: 0–1 (0 = pie, 0.4–0.6 = donut)
    textinfo: "label" | "percent" | "value" | "label+percent" | "none"
    pull: explode slices (array of 0–1)
    sort: true (sort by value descending, default)
    direction: "clockwise" | "counterclockwise"

──────────────────────────────────

4. HISTOGRAM (type: "histogram")
  Distribution of a continuous variable.

  Trace structure:
    {
      "type": "histogram",
      "meta": { "columnNames": { "x": ["interview_time"] } },
      "name": "Interview Duration",
      "marker": { "color": "#2563eb" },
      "hovertemplate": "%{x}<br>Count: %{y}<extra></extra>"
    }

  Key properties:
    nbinsx: number of bins (auto if omitted)
    histnorm: "" (count) | "percent" | "probability" | "density"
    cumulative.enabled: true (cumulative histogram)

──────────────────────────────────

5. LINE (type: "scatter" with mode: "lines")
  Trend/sequence data.

  Trace structure:
    {
      "type": "scatter",
      "mode": "lines",
      "meta": { "columnNames": { "x": ["area_name"], "y": ["rate"] } },
      "name": "Literacy Rate",
      "line": { "color": "#2563eb", "width": 2, "shape": "spline" },
      "hovertemplate": "%{x}<br>%{y:.1f}<extra></extra>"
    }

  Notes:
    Plotly has no native "line" type — always use scatter with mode: "lines".
    For multiple series, add more traces with different y aliases.

──────────────────────────────────

6. AREA (type: "scatter" with fill)
  Area under a line (fill to baseline or to another trace).

  Trace structure:
    {
      "type": "scatter",
      "mode": "lines",
      "fill": "tozeroy",
      "meta": { "columnNames": { "x": ["area_name"], "y": ["total"] } },
      "name": "Population",
      "line": { "color": "#2563eb", "width": 2 },
      "hovertemplate": "%{x}<br>%{y:,.0f}<extra></extra>"
    }

  fill values:
    "tozeroy" — fill to y=0 baseline
    "tonexty" — fill to the previous trace's y values (stacked areas)
    "tozerox" — fill to x=0

  For stacked areas:
    Two traces, both with fill: "tonexty", ordered bottom→top.

──────────────────────────────────

7. BOX (type: "box")
  Statistical distribution showing quartiles and outliers.

  Trace structure:
    {
      "type": "box",
      "meta": { "columnNames": { "y": ["value"] } },
      "name": "Distribution",
      "boxmean": "sd",
      "hovertemplate": "%{y}<br>%{x}<extra></extra>"
    }

  Key properties:
    boxmean: true (mean line) | "sd" (mean ± std dev line)
    notched: true (notched boxplot)
    boxpoints: "outliers" (default) | "all" | "suspectedoutliers" | false
    With x grouping: { "x": ["group_col"], "y": ["value_col"] }

──────────────────────────────────

REFERENCE LINE OVERLAY (bar + line)
  When getData() includes a reference_value column (via
  lastlyAreaLeftJoinData with referenceValueToInclude):

  Trace 1 (bars — main data):
    { "type": "bar", "meta": { "columnNames": { "x": ["area_name"], "y": ["value"] } } }

  Trace 2 (line — reference):
    { "type": "scatter", "mode": "lines+markers",
      "meta": { "columnNames": { "x": ["area_name"], "y": ["reference_value"] } },
      "line": { "color": "red", "width": 2, "dash": "dash" },
      "name": "Reference" }

──────────────────────────────────

CONSTANT REFERENCE LINE (horizontal benchmark)
  When getData() produces a column holding the same constant value in every
  row (e.g. an expected/benchmark figure added in PHP), a scatter line mapped
  to that column renders as a flat horizontal dashed line. This is distinct
  from the reference_value overlay above (which varies per area). See the
  MaternalMortalityRatio example (the `expected` column = 354 for every row).

  Trace 1 (bars — observed values):
    { "type": "bar", "meta": { "columnNames": { "x": ["area_name"], "y": ["rate"] } }, "name": "Rate" }

  Trace 2 (line — constant benchmark):
    { "type": "scatter", "mode": "lines",
      "meta": { "columnNames": { "x": ["area_name"], "y": ["expected"] } },
      "line": { "color": "#dc2626", "width": 2, "dash": "dash" },
      "name": "Expected" }

──────────────────────────────────

MULTI-AXIS LAYOUT (two y-axes)
  When one trace should use the right axis:

  Trace 1 (bars — left axis):
    { ... no yaxis attribute ... }

  Trace 2 (line — right axis):
    { ..., "yaxis": "y2" }

  Layout:
    {
      "yaxis": { "title": { "text": "Left Axis Label" } },
      "yaxis2": { "title": { "text": "Right Axis Label" }, "overlaying": "y", "side": "right" }
    }

──────────────────────────────────

TIME-SERIES X-AXIS
  When getData() returns one row per date (e.g. daily listing), format the
  date column as "YYYY-MM-DD" in SQL and order by it so rows arrive in
  sequence. The default xaxis.type "category" then renders dates in order;
  pass { "xaxis": { "type": "date" } } only if you need Plotly's date axis
  features (tick formatting, range slider). See the HouseholdsByDay and
  ListedHouseholdsPerDay examples.

  Line + target line (observed value vs. a per-row target column):
    Trace 1: scatter mode "lines+markers", y → observed column (e.g. total)
    Trace 2: scatter mode "lines", y → target column (e.g. daily_target),
             dashed line for visual contrast

──────────────────────────────────

LAYOUT ADJUSTMENTS
  The Chart base class always injects `PlotlyDefaults::DEFAULT_LAYOUT` as the
  starting layout. The agent only needs to pass the properties it wants to
  **override** in the `layout` field of `edit-chart`. Values already set by
  default (omit unless overriding with a different value):

    showlegend: true
    legend: { orientation: "h", x: 0, y: 1.12 }
    xaxis: { type: "category", tickmode: "auto", automargin: true }
    yaxis2: { side: "right", overlaying: "y", showgrid: false }
    margin: { l: 60, r: 30, t: 15, b: 40 }
    modebar: { orientation: "v", color: "white", bgcolor: "darkgray" }
    dragmode: "pan"

  Override reference (pass any of these to change the default):

  Title:        { "title": { "text": "Chart Title" } }
  X axis:       { "xaxis": { "title": { "text": "Category" }, "tickangle": -45 } }
  Y axis:       { "yaxis": { "title": { "text": "Value" }, "tickprefix": "$" } }
  2nd Y axis:   { "yaxis2": { "title": { "text": "Rate" }, "overlaying": "y", "side": "right" } }
  Legend:       { "showlegend": true, "legend": { "orientation": "h", "x": 0, "y": 1.12 } }
  Margins:      { "margin": { "l": 60, "r": 30, "t": 15, "b": 40 } }
  Barmode:      { "barmode": "group" } | "stack" | "relative" | "overlay"

  Dynamic X-Axis Title (PHP property, not a Plotly layout field)
    Set `public bool $useDynamicAreaXAxisTitles = true;` in your indicator
    class to automatically update the x-axis label based on the current
    area filter level. At national level it reads "Counties", at county
    level it reads "Subcounty of {County Name}", and so on.

    Do NOT set this via edit-chart — it is a PHP class property. Edit the
    generated indicator file at `app/Livewire/Indicator/...` to add the
    property.

  Aggregate-Appended Traces (PHP property, not a Plotly layout field)
    Set `public array $aggregateAppendedTraces = ['<trace name>' => 'sum'];`
    in your indicator class to have the Chart base class auto-append an
    aggregate row ("All {area level}") to the matching trace at render time.
    The trace `name` in your edit-chart data MUST exactly match the property
    key. Supported ops: sum, count, min, max, mode, median, avg.

    Behavior at render (handled by the base class — do NOT configure manually):
      - sum: appends a SEPARATE trace on the right y-axis (yaxis2) named
        "<trace name> (across <area level>)" with x = "All <area level>" and
        y = the summed values. yaxis2 is auto-enabled.
      - other ops: appends an extra x/y point to the SAME trace.

    See the MaternalMortalityRatio example (aggregateAppendedTraces for the
    "deaths" trace). Do NOT set this via edit-chart — it is a PHP class
    property; edit the generated indicator file to add it.

──────────────────────────────────

HOVERTEMPLATE FORMATTING
  %{y}             raw value
  %{y:.1f}         one decimal
  %{y:,.0f}        thousands separator, no decimals
  %{y:$.2f}        currency format
  %{x}             x value
  %{text}          value of the column mapped to the trace "text" property
  %{label}         pie label
  %{percent}       pie percentage
  %{fullData.name} trace name (shows in secondary box)
  <extra></extra>  hide secondary grey box
  <extra>Label</extra> replace the secondary box content with custom text

──────────────────────────────────

WORKED EXAMPLES
  Each mirrors an enriched example file — fetch the full getData() source via
  get-artefact-examples to see how the data shape is produced. Only the
  trace/layout essentials are shown; add marker/hovertemplate styling as
  needed and consult the Plotly reference for anything beyond these patterns.

  1. Stacked bar with text labels (mirrors PopulationByAgeGroup)
     {
       "data": [
         { "type": "bar", "meta": { "columnNames": { "x": ["area_name"], "y": ["children"], "text": ["children_pct"] } }, "name": "Children", "texttemplate": "%{text}%", "textposition": "outside", "hovertemplate": "%{x}<br>%{y:,.0f} (%{text}%)<extra></extra>" },
         { "type": "bar", "meta": { "columnNames": { "x": ["area_name"], "y": ["adults"] } }, "name": "Adults", "hovertemplate": "%{x}<br>%{y:,.0f}<extra></extra>" },
         { "type": "bar", "meta": { "columnNames": { "x": ["area_name"], "y": ["elderly"] } }, "name": "Elderly", "hovertemplate": "%{x}<br>%{y:,.0f}<extra></extra>" }
       ],
       "layout": { "barmode": "stack", "yaxis": { "title": { "text": "Population" } } }
     }

  2. Bar + constant reference line (mirrors MaternalMortalityRatio)
     {
       "data": [
         { "type": "bar", "meta": { "columnNames": { "x": ["area_name"], "y": ["rate"] } }, "name": "MMR", "hovertemplate": "%{x}<br>%{y:,.0f}<extra></extra>" },
         { "type": "scatter", "mode": "lines", "meta": { "columnNames": { "x": ["area_name"], "y": ["expected"] } }, "name": "Expected", "line": { "color": "#dc2626", "width": 2, "dash": "dash" }, "hovertemplate": "Expected %{y:,.0f}<extra></extra>" }
       ],
       "layout": { "yaxis": { "title": { "text": "Deaths per 100k live births" } } }
     }

  3. Time-series line + target line (mirrors ListedHouseholdsPerDay)
     {
       "data": [
         { "type": "scatter", "mode": "lines+markers", "meta": { "columnNames": { "x": ["enumeration_date"], "y": ["total"] } }, "name": "Listed", "line": { "color": "#2563eb", "width": 2 }, "hovertemplate": "%{x}<br>%{y:,.0f}<extra></extra>" },
         { "type": "scatter", "mode": "lines", "meta": { "columnNames": { "x": ["enumeration_date"], "y": ["daily_target"] } }, "name": "Target", "line": { "color": "#dc2626", "width": 2, "dash": "dash" }, "hovertemplate": "Target %{y:,.0f}<extra></extra>" }
       ],
       "layout": { "xaxis": { "title": { "text": "Date" } }, "yaxis": { "title": { "text": "Households" } } }
     }

──────────────────────────────────

EXAMPLE: COMPLETE BAR CHART
  {
    "data": [
      {
        "type": "bar",
        "meta": { "columnNames": { "x": ["area_name"], "y": ["value"] } },
        "name": "Population",
        "marker": { "color": "#2563eb" },
        "hovertemplate": "%{x}<br>%{y:,.0f}<extra></extra>"
      }
    ],
    "layout": {
      "title": { "text": "Population by County" },
      "xaxis": { "title": { "text": "County" }, "tickangle": -45 },
      "yaxis": { "title": { "text": "Population" } },
      "margin": { "l": 60, "r": 30, "t": 15, "b": 40 },
      "showlegend": false
    }
  }

  Note: `margin` and the base `xaxis` properties (`type`, `tickmode`,
  `automargin`) are already part of the default layout — they are shown here
  for clarity but can be omitted in your edit-chart call if you don't need to
  override them. The indicator title is rendered by the card component above
  the chart, so `layout.title` is only shown here as an example — omit it
  unless the user explicitly asks for a chart subtitle.
DOC;
}
