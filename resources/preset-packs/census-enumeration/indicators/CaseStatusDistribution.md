---
type: indicator
title: Case Status Distribution
description: Distribution of enumeration cases by status (interviewed, refusal, absent)
---

Shows the distribution of enumeration cases by their final status across administrative areas.

Calculated by counting cases grouped by status code (e.g. interviewed, refusal, absent,
partial) and `area_code`. Render as a stacked bar chart or pie chart showing case
outcome composition across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
