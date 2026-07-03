---
type: indicator
title: Structure Listing Completion Rate
description: Percentage of structures listed against the listing target
---

Shows the percentage of structures listed against the target across administrative areas.

Calculated by counting listed structures divided by the EA or area structure target,
grouped by `area_code`. Render as a bar chart or gauge showing listing progress by region.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.

**Requires a reference value.** This indicator compares against a per-EA target. Use the MCP tool to discover available reference values; examine each indicator's `description` to select the correct one defining the per-EA structure target. Pass the chosen reference value name as `referenceValueToInclude` when calling `lastlyAreaLeftJoinData()`. This injects the target value as a `ref_value` column alongside `area_name` and `area_path`.
