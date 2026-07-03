---
type: indicator
title: Urban/Rural Population
description: Population distribution between urban and rural areas
---

Shows the population breakdown by urban and rural classification across administrative
areas.

Calculated by counting persons grouped by urban/rural classification and `area_code`.
Render as a stacked bar chart or pie chart showing the urban/rural composition by
region.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
