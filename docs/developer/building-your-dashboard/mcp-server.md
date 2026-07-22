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

## Available Tools

The tools and resources below are not called directly by human users — they are consumed by AI coding assistants. To use the MCP server, open your connected assistant and describe what you want in natural language (e.g. "create a scorecard for enumerated households"). The assistant will call the appropriate tools in sequence: discovering data sources, reading dictionaries, creating artefacts, configuring charts, and validating results. The 6-step workflow described further down is what the agent follows internally; you only need to provide the intent.

The MCP server exposes 15 tools organized by function:

### Discovery Tools

| Tool | Description |
|---|---|
| `get-data-sources` | Lists all active data sources with their names, titles, and available reference value indicators |
| `read-dictionary` | Parses a registered CSPro dictionary and returns its structure — records, items (with types), and value sets |
| `get-reference-values` | Lists available reference value indicators that can be used for comparisons or reference lines in charts |
| `get-artefact-examples` | Lists or reads example implementations of artefact types (API documentation for implementation patterns) |

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
