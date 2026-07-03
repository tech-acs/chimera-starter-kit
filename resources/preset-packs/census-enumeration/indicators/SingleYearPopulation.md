---
type: indicator
title: Single-Year Population
description: Population count by single year of age
---

Shows the population count for each single year of age across administrative areas.

Calculated by counting persons grouped by exact age in years and `area_code`. Render
as a bar chart showing the age distribution with single-year granularity.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
