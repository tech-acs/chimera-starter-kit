---
type: indicator
title: Population by Nationality
description: Population distribution across nationality groups
---

Shows the population breakdown by nationality across administrative areas.

Calculated by counting persons grouped by nationality and `area_code`. Render as a
stacked bar chart showing the nationality composition across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
