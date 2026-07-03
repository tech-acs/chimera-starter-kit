---
type: indicator
title: Estimated Days to Complete Enumeration
description: Projected number of days to complete enumeration based on recent performance
---

Shows the estimated days remaining to complete enumeration based on recent daily performance.

Calculated by dividing the remaining households to enumerate by the average daily enumeration
rate over the previous days. Grouped by `area_code`. Render as a bar chart showing projected
completion time per region, or a line chart tracking the estimate over time.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.

**Requires a reference value.** This indicator projects against a remaining target. You must discover available reference values and select the one defining the per-EA enumeration target. Pass the chosen reference value name as `referenceValueToInclude` when calling `lastlyAreaLeftJoinData()`. This injects the target value as a `ref_value` column alongside `area_name` and `area_path`.
