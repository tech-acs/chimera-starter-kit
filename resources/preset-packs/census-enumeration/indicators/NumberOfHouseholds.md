---
type: indicator
title: Number of Households
description: Household count distribution across administrative areas
---

Shows the household count breakdown by administrative area.

Uses `COUNT(*)` on the household record table, grouped by `area_code`.
Can render as a bar chart showing household distribution across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
