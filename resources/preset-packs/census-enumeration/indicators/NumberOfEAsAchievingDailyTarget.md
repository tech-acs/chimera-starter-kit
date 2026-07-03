---
type: indicator
title: Number of EAs Achieving Daily Target
description: Count of enumeration areas that met their daily enumeration target
---

Shows how many enumeration areas (EAs) achieved their daily household enumeration target.

Calculated by comparing the number of households enumerated in each EA per day against
the EA's assigned daily target, then counting the EAs that met or exceeded the target.
Render as a bar chart showing the count of achieving EAs per day or per area.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.

**Requires a reference value.** This indicator compares against a per-EA daily target. Use the MCP tool to discover available reference values; examine each indicator's `description` to select the correct one defining the per-EA daily enumeration target. Pass the chosen reference value name as `referenceValueToInclude` when calling `lastlyAreaLeftJoinData()`. This injects the target value as a `ref_value` column alongside `area_name` and `area_path`.
