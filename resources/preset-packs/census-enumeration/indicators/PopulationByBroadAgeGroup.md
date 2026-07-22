---
type: indicator
title: Population by Broad Age Group
description: Population distribution across broad age categories (0–14, 15–64, 65+)
---

Shows the population breakdown by broad age groups (children 0–14, working age 15–64,
elderly 65+) across administrative areas.

Calculated by classifying persons into age brackets and counting per bracket, grouped by area_code. Render as a normalized stacked bar chart where each bar sums to 100%. Keep raw counts in getData() and normalize using Plotly's barnorm: 'percent' layout property on the chart — do not compute percentages in the query or PHP.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for x-axis labels.
