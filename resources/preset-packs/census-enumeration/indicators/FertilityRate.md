---
type: indicator
title: Fertility Rate
description: Number of live births per 1,000 women of reproductive age (15–49)
---

Shows the fertility rate across administrative areas.

Calculated by dividing the total number of live births by the number of women aged
15–49, multiplied by 1,000. Grouped by `area_code`. Render as a bar chart comparing
fertility rates across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
