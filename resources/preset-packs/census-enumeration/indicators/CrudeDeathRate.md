---
type: indicator
title: Crude Death Rate
description: Number of deaths per 1,000 population in the enumeration area
---

Shows the crude death rate (deaths per 1,000 population) across administrative areas.

Calculated by dividing the total number of deaths recorded by the total enumerated
population, multiplied by 1,000. Grouped by `area_code`. Render as a bar chart
comparing death rates across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
