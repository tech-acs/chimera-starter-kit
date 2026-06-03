# ADR-0001: MCP Server for Dashboard Artefact Generation

We decided to add an MCP server (using `laravel/mcp` v0.7) inside this starter-kit package, registered via `ChimeraServiceProvider`, that exposes tools for AI coding agents to create and edit dashboard artefacts (indicators, scorecards, gauges, map indicators, reports). The server lives in the `\Uneca\Chimera\Mcp` namespace and is accessible as a local (stdio) server.

## Considered Options

- **Inside this package (chosen):** Tools ship with the starter kit, registered in the service provider. Consuming apps get the MCP server automatically.
- **Separate package:** Creating a second composer dependency. Rejected because the tools are tightly coupled to this package's models, DTOs, and actions — splitting them off adds maintenance overhead with no clear benefit.
- **Granular per-operation tools:** Separate tools for file creation, DB record creation, etc. Rejected in favor of one tool per artefact type (create + edit) because agents naturally think in terms of "create an indicator," not "create a model record then create a file."

## Consequences

- Consuming apps must add the MCP server to their `opencode.json` or other MCP client config to point at `php artisan chimera:mcp`.
- The `parse_dictionary` tool reads CSPro `.dcf` files from a configured path (set during setup) and returns their schema structure so agents understand the available database fields.
- Create tools delegate to existing `Actions` (e.g., `CreateIndicatorAction`) to avoid duplicating artefact-creation logic.
