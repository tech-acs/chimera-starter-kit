---
type: indicator
title: Population Count
description: Total enumerated population across all listing areas
---

Shows the population breakdown by administrative area.

Uses `COUNT(*)` or `SUM(total_household_members)` on the person or household record,
grouped by `area_code`. Render as a bar chart or geographic distribution.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
