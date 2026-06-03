## Problem Statement

Developers currently create dashboard artefacts (indicators, scorecards, gauges, map indicators, reports) through a manual two-step process: (1) run an interactive `make:*` command that creates a component file from a stub and a database record, then (2) manually edit the component file to implement the `getData()` method. For indicators, there is a third step where the developer uses a visual Plotly editor to configure chart traces and layout.

This manual process is repetitive and error-prone. AI coding agents (opencode, Claude, Cursor, etc.) could automate much of this work — generating the artefacts from natural-language descriptions, correctly wiring up the BreakoutQueryBuilder queries, and even setting Plotly configuration — but they currently have no structured way to interact with the dashboard's artefact system.

## Solution

Add an MCP (Model Context Protocol) server built with `laravel/mcp` to this package. The server exposes tools that AI agents can call to create, read, and edit dashboard artefacts. The server handles both the filesystem (component class file from stub) and the database (model record) sides of each artefact, and includes a dictionary-parsing tool so agents can understand the CSPro data source schema.

## User Stories

1. As an AI coding agent, I want to read a CSPro `.dcf` dictionary file and get its parsed structure (levels, records, items with types/labels/value sets), so that I know what database fields are available when writing `getData()` queries.

2. As an AI coding agent, I want to create an Indicator (Plotly chart) artefact by providing a name, title, data source, and optional Plotly traces and layout, so that the component file and database record are generated in one step.

3. As an AI coding agent, I want to edit an existing Indicator's metadata and Plotly traces/layout, so that I can update indicators without manually editing the database or files.

4. As an AI coding agent, I want to create a Scorecard (numeric summary card) artefact by providing a name, title, and data source, so that the component file and database record are generated.

5. As an AI coding agent, I want to edit an existing Scorecard's metadata, so that I can update scorecard properties without manual intervention.

6. As an AI coding agent, I want to create a Gauge (visual threshold indicator) artefact by providing a name, title, subtitle, and data source, so that the component file and database record are generated.

7. As an AI coding agent, I want to edit an existing Gauge's metadata, so that I can update gauge properties.

8. As an AI coding agent, I want to create a Report (Excel export) artefact by providing a name, title, data source, and optional schedule, so that the component file and database record are generated.

9. As an AI coding agent, I want to edit an existing Report's metadata and schedule, so that I can update report properties.

10. As an AI coding agent, I want to create a MapIndicator (colored map boundaries) artefact by providing a name, title, and data source, so that the component file and database record are generated.

11. As an AI coding agent, I want to edit an existing MapIndicator's metadata, so that I can update map indicator properties.

12. As a developer setting up the dashboard, I want to configure the MCP server in my opencode.json (or other MCP client) by pointing at a simple artisan command, so that my AI agent can connect to it.

13. As an AI coding agent, I want the server's instructions to document the key domain patterns (BreakoutQueryBuilder API, stub-to-component lifecycle, Plotly reference, area hierarchy), so that I can produce correct artefact implementations.

14. As an AI coding agent, I want the server's instructions to tell me which Plotly chart types are supported (bar, scatter, pie, histogram, line, area, box, sunburst), so that I can design appropriate charts.

15. As an AI coding agent, I want the server's instructions to explain the `$filterPath` / area hierarchy system, so that I can implement `getData()` correctly for multi-level geographic scoping.

## Implementation Decisions

- **Package namespace:** The MCP server lives in `Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator`, registered via `Mcp::local()` in `ChimeraServiceProvider`.
- **Server name:** "Dashboard Artefact Generator" — describes the server's purpose to AI agents.
- **Tool granularity:** One tool per artefact type × operation (create + edit), plus a `parse_dictionary` tool. No granular sub-tools for model vs file creation — each tool handles both.
- **File generation:** Create tools delegate to existing `App\Actions\Maker\*` actions, which create both the DB record and the component file (via `Artisan::call('chimera:make-artefact', ...)`) in a single transaction.
- **Dictionary parsing:** The `parse_dictionary` tool accepts a `.dcf` file's raw JSON string content and returns a structured representation of levels, records, items, and value sets. The agent is responsible for reading the file from the filesystem and passing its content.
- **Plotly integration:** The `create_indicator` tool accepts optional `data` (traces array) and `layout` (object) parameters. The `edit_indicator` tool can update them post-creation. Supported chart types: bar, scatter, pie, histogram, line, area, box, sunburst. The agent should use its statistical knowledge to select appropriate chart types for the data.
- **Area hierarchy:** Areas use PostgreSQL ltree with dotted-path hierarchy. The `$filterPath` parameter to `getData()` controls geographic scope — empty for national, each dot descends one level. `BreakoutQueryBuilder` consumes `$filterPath` automatically.
- **Data flow:** `getData()` prepares all the data columns the chart's traces need. Plotly traces in the `data` column reference those output columns via `meta.columnNames`, which the `Chart` base class's `getTraces()` resolves at render time.
- **Stub reuse:** The MCP tools use the same stub files as the existing `chimera:make-indicator`, `chimera:make-scorecard`, etc. commands, ensuring consistency.
- **Consuming app registration:** The consuming app configures their MCP client to point at `php artisan mcp:start dashboard-artefact-generator`.
- **Server instructions:** The server includes detailed instructions covering the artefact lifecycle, BreakoutQueryBuilder usage patterns, CSPro dictionary structure, area hierarchy / ltree / `$filterPath`, supported Plotly chart types with chart-design guidance, a link to Plotly JS docs, and the `getData()` → traces data flow.

## Testing Decisions

- Each tool's `handle()` method should be tested via the MCP testing utilities provided by `laravel/mcp` (e.g., `DashboardArtefactGenerator::call('create_indicator', [...])`).
- Tests should verify: (1) the database record is created with correct attributes, (2) the component file is written to the expected path, (3) error handling when required fields are missing or references are invalid.
- Stub-based file generation should be tested by verifying the generated file contains the expected namespace and class name.
- Existing tests in the package use Pest PHP; new MCP tests should follow the same conventions.

## Out of Scope

- Refactoring the existing `Make*` commands to delegate to the same internal actions as the MCP tools (future improvement).
- HTTP/SSE transport for the MCP server — only stdio (local) transport is supported initially.
- OAuth-based authorization for the MCP server.
- MCP resources or prompts — only tools are implemented in this initial version.
- Support for creating/editing the "Summary" composite page component.
- Automatic detection of CSPro dictionary file locations — the agent must know the path.
