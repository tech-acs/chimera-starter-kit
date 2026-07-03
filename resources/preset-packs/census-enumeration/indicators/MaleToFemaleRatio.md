---
type: indicator
title: Male to Female Ratio
description: Number of males per 100 females in the enumerated population
---

Shows the sex ratio (males per 100 females) across administrative areas.

Calculated by dividing the male population count by the female population count,
multiplied by 100. Grouped by `area_code` and sex. Render as a bar chart or
distribution showing sex imbalances across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
