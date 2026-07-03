---
type: indicator
title: Average Interview Time
description: Average time spent listing each household or structure
---

Shows the average listing duration per household or structure across administrative areas.

Calculated from the difference between listing start and end timestamps, averaged and
grouped by `area_code`. Render as a bar chart or distribution showing listing efficiency
across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
