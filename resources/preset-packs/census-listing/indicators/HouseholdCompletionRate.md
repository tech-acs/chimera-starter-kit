---
type: indicator
title: Household Completion Rate
description: Percentage of households listed against the listing target
---

Shows the percentage of households listed against the target across administrative areas.

Calculated by counting listed households divided by the EA or area listing target,
grouped by `area_code`. Render as a bar chart or gauge showing listing progress by region.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.

**Requires a reference value.** This indicator compares against a per-EA target. You must discover available reference values and select the one defining the per-EA listing target. Pass the chosen reference value name as `referenceValueToInclude` when calling `lastlyAreaLeftJoinData()`. This injects the target value as a `ref_value` column alongside `area_name` and `area_path`.
