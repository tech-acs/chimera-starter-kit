---
type: indicator
title: Occupied vs Unoccupied Structures
description: Proportion of occupied versus unoccupied structures across administrative areas
---

Shows the ratio of occupied to unoccupied structures across administrative areas.

Calculated by counting structures grouped by occupancy status and `area_code`.
Render as a stacked bar chart or pie chart showing the occupancy composition by region.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
