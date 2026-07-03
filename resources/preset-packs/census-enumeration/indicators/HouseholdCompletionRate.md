---
type: indicator
title: Household Completion Rate
description: Percentage of households enumerated against the enumeration target
---

Shows the percentage of households completed against the target across administrative areas.

Calculated as `COUNT(enumct` or `SUM(enumstat` on the household record, divided by the
EA or area household target, grouped by `area_code`. Render as a bar chart or gauge
showing completion progress by region.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.

**Requires a reference value.** This indicator compares against a per-EA target. Use the MCP tool to discover available reference values; examine each indicator's `description` to select the correct one defining the per-EA enumeration target. Pass the chosen reference value name as `referenceValueToInclude` when calling `lastlyAreaLeftJoinData()`. This injects the target value as a `ref_value` column alongside `area_name` and `area_path`.
