---
type: indicator
title: Average Interview Time
description: Average time spent enumerating each household
---

Shows the average interview duration per household across administrative areas.

Calculated from the difference between interview start and end timestamps on the
household record, averaged and grouped by `area_code`. Render as a bar chart or
distribution showing interview efficiency across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
