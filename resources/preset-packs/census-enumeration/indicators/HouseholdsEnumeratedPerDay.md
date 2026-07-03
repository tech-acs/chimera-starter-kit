---
type: indicator
title: Households Enumerated per Day
description: Daily count of households enumerated across administrative areas
---

Shows the number of households enumerated per day, tracking daily enumeration progress.

Calculated by counting household records grouped by enumeration date and `area_code`.
Render as a line chart showing daily enumeration volume, or a stacked bar chart broken
down by region.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
