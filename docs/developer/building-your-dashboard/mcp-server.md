---
outline: deep
---

# MCP Server

The Dashboard Starter Kit includes a **Model Context Protocol (MCP)** server that enables AI coding assistants to interact with your dashboard programmatically. Through MCP tools, an AI agent can create, read, and manage dashboard artefacts such as indicators, scorecards, gauges, map indicators, and reports — all via structured tool calls.

## Overview

The MCP server acts as a bridge between AI assistants and your dashboard's data layer. It understands your data sources, dictionary structures, and artefact types, enabling it to generate fully functional dashboard components based on natural language instructions.

This is the third path for artefact creation alongside the [CLI](/developer/building-your-dashboard/creating-indicators#method-1-cli-command) and [Web](/developer/building-your-dashboard/creating-indicators#method-2-web-form) interfaces, sharing the same validation rules and underlying Action classes.

## Installation

Connecting the MCP server to your AI coding assistant requires two steps: registering your data dictionaries, then configuring the assistant to launch the server process.

### Prerequisites

- The Dashboard Starter Kit package is installed in your Laravel application
- At least one [active data source](/developer/foundation/core-configuration#data-sourcesquestionnaires) exists
- Your application has active database connections (PostgreSQL + MySQL)
- The `laravel/mcp` package (`^0.7.0`) is included — it ships with the starter kit

### Step 1: Register data dictionaries

The MCP server needs to know where your CSPro dictionary (`.dcf`) files are located for each data source. Run the initialization command:

```bash
php artisan chimera:mcp-init
```

This interactive command walks you through each active data source and prompts for the path to its `.dcf` file. The configuration is saved to `dashboard-starter-kit-mcp.json` in your project root.

The dictionary registration is the **only** manual prerequisite. Without it, most MCP tools will refuse with an "uninitialized" error.

### Step 2: Configure your AI coding assistant

The MCP server communicates over **STDIO** — it reads JSON-RPC messages from standard input and writes responses to standard output. Your coding assistant launches it as a subprocess.

The handle `dashboard-starter-kit` must match the string used in the service provider's `Mcp::local()` call. Every assistant uses the same command + args pattern:

```json
{
  "mcpServers": {
    "dashboard-starter-kit": {
      "command": "php",
      "args": ["artisan", "mcp:start", "dashboard-starter-kit"]
    }
  }
}
```

The working directory should be your Laravel project root (where the `artisan` file lives). Most clients default to the project directory automatically.

#### Client-specific configuration files

| Client | Configuration file |
|---|---|
| Claude Desktop | `~/Library/Application Support/Claude/claude_desktop_config.json` |
| VS Code (Cline) | `.vscode/mcp.json` (project-level) |
| Cursor | `.cursor/mcp.json` (project-level) |
| opencode | `opencode.json` (project root) or `~/.config/opencode/opencode.jsonc` |
| Continue.dev | `~/.continue/config.json` |

### Step 3: Verify the connection

Open your AI assistant's MCP connection status panel. In **opencode**, the MCP section of the TUI lists all configured servers along with their connection status. Other clients show MCP status similarly — look for a connected indicator on the `dashboard-starter-kit` server.

If the server shows as disconnected or returns initialization errors, re-run `php artisan chimera:mcp-init` to register your dictionary paths, then restart the connection.

## Initialization

Before using the MCP server, you must initialize it by registering your CSPro data dictionaries:

```bash
php artisan chimera:mcp-init
```

This command walks you through registering the `.dcf` (data dictionary) files for your data sources. The MCP server needs these dictionaries to understand the structure of your data — which records and items are available, their types, and value sets.

## Available Tools

The MCP server exposes 20 tools organized by function:

### Discovery Tools

| Tool | Description |
|---|---|
| `get-data-sources` | Lists all active data sources with their names, titles, and available reference value indicators |
| `read-dictionary` | Parses a registered CSPro dictionary and returns its structure — records, items (with types), and value sets |
| `get-reference-values` | Lists available reference value indicators that can be used for comparisons or reference lines in charts |
| `get-artefact-examples` | Lists or reads example implementations of artefact types (API documentation for implementation patterns) |
| `get-preset-packs` | Lists available preset packs and their artefact counts |

### Preset Pack Tools

Preset packs let you deploy a **complete collection** of related artefacts — indicators, scorecards, and reports — from pre-built markdown specs stored in `resources/preset-packs/`. Two packs ship with the starter kit:

| Pack | Indicators | Scorecards | Reports | What it covers |
|---|---|---|---|---|
| `census-enumeration` | 22 | 7 | 8 | Enumeration progress, household completions, population counts, demographics, time-to-complete, case status |
| `census-listing` | 14 | 10 | 8 | Structure listing, household identification, occupancy status, daily listing rates |

You don't call these tools yourself — just ask the assistant to deploy a pack:

| Tool | What the assistant does |
|---|---|
| `get-preset-packs` | Lists available packs and their artefact counts |
| `stage-preset-pack` | Reads every artefact spec in the pack — name, type, title, description, implementation hints — and constructs a feasibility matrix against your dictionary's records and items |
| `deploy-preset-pack` | Creates all selected artefacts in a single batch call, using the same `CreateArtefactAction` as the individual create tools. Each artefact is prefixed with the data source title (e.g. `KenyaCensus/AverageHouseholdSize`). |

> **Tip:** After deployment, ask the assistant to validate key artefacts with `validate-artefact` and configure their charts with `edit-chart`.

**Example prompts:**

> Deploy the census enumeration preset pack using the 2023 Kenya Census data source.

> I want to deploy the census listing pack — stage it first so I can review the artefacts.

> Deploy just the scorecards and indicators from the census-enumeration pack (skip the reports).

> We're using the enumeration pack. After deploying, validate the PopulationCount scorecard and configure the PopulationPyramid indicator as a bar chart.

#### Publishing and customizing packs

The built-in packs live inside `vendor/uneca/dashboard-starter-kit/resources/preset-packs/`. To customize them or add your own, publish them to your app:

```bash
php artisan vendor:publish --tag=chimera-preset-packs
```

This copies the `census-enumeration` and `census-listing` directories into `resources/preset-packs/` in your consumer app. Once published, you can:

- **Edit** existing artefact specs — change descriptions, add `data` hints, or adjust implementation notes
- **Add new artefacts** to a pack — drop `.md` files into the right type subdirectory (`indicators/`, `scorecards/`, `reports/`)
- **Create your own packs** — make a new directory under `resources/preset-packs/` with a `pack.md` manifest and artefact files in type subdirectories

The `PresetPackService` checks `resources/preset-packs/` in the consumer app first, so your custom versions override the built-in ones. Remove the published directory to fall back to the package defaults.

### Creation Tools

| Tool | Description |
|---|---|
| `create-scorecard` | Creates a new scorecard artefact (Livewire component file + DB record) |
| `create-indicator` | Creates a new indicator (Plotly chart) artefact with default layout |
| `create-gauge` | Creates a new gauge artefact |
| `create-map-indicator` | Creates a new map indicator artefact |
| `create-report` | Creates a new report artefact |

### Editing Tools

| Tool | Description |
|---|---|
| `edit-chart` | Configures Plotly traces and layout for an indicator, with validation against `getData()` results |
| `edit-scorecard` | Updates scorecard metadata (title, scope) |
| `edit-indicator` | Updates indicator metadata (title, description, help text, scope) |
| `edit-gauge` | Updates gauge metadata (title, subtitle) |
| `edit-map-indicator` | Updates map indicator metadata (title, description) |
| `edit-report` | Updates report metadata (title, description) |

### Validation Tools

| Tool | Description |
|---|---|
| `validate-artefact` | Validates an artefact by running its `getData()` method in a subprocess and returning the results |
| `manage-page-assignment` | Attaches or detaches artefacts to/from dashboard pages |

## Available Resources

In addition to tools, the MCP server exposes documentation resources that AI assistants can read:

| Resource | URI | Description |
|---|---|---|
| `BreakoutQueryBuilderDoc` | `docs://breakout-query-builder` | Full API reference for the BreakoutQueryBuilder — method chain, join strategies, usage patterns |
| `PlotlyPatternsDoc` | `docs://plotly-patterns` | Guide for constructing Plotly traces — chart types, `meta.columnNames` mapping, hovertemplate formatting, layout overrides |
| `ArtefactExampleIndex` | `examples://artefact/{type}` | Lists available example implementations for a given artefact type |
| `ArtefactExampleFile` | `examples://artefact/{type}/{name}` | Returns the full PHP source of a single example implementation |

## Tool Workflow (6 Steps)

**Preset pack alternative:** Instead of the step-by-step workflow below, you can deploy a complete collection of artefacts in one go — just ask the assistant to deploy the pack you want.

The MCP server is designed around a structured workflow:

1. **Discover data sources** — Call `get-data-sources` to find available data sources
2. **Understand the dictionary** — Call `read-dictionary` to parse the data dictionary for the target data source
3. **Read examples** — Call `get-artefact-examples` to see implementation patterns for the desired artefact type
4. **Create the artefact** — Call `create-indicator` (or the appropriate creation tool) with the artefact name, title, data source, and optional chart configuration
5. **Configure the chart** — Call `edit-chart` to set up Plotly traces (bar, line, pie, etc.), data column mappings, and layout
6. **Validate** — Call `validate-artefact` to confirm the artefact returns data correctly

## Example Prompts

Here are some prompts you can give your AI coding assistant once the MCP server is connected:

> Create an enumerated households scorecard

> I want an indicator showing the distribution of household sizes across areas. It should be a box chart. Enable the dynamic axis titles so that the x-axis will show the area names.

> Create an indicator showing the population broken into broad age groups.

> Create a population enumerated map indicator.

> What fieldwork quality-control fields exist? Is there a way to flag incomplete or problematic interviews?

> What disabilities data does the census collect, and which age groups does it cover?

> I want to see if households with better housing quality (roof, wall, floor materials) have higher education levels among adult members. Which records and fields would I need to join, and what are the conceptual challenges?
