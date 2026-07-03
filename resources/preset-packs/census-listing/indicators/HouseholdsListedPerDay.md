---
type: indicator
title: Households Listed per Day
description: Daily count of households listed across administrative areas
---

Shows the number of households listed per day, tracking daily listing progress.

Calculated by counting household records grouped by listing date and `area_code`.
Render as a line chart showing daily listing volume, or a stacked bar chart broken
down by region.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
