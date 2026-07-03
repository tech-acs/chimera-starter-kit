---
type: indicator
title: Population by Broad Age Group
description: Population distribution across broad age categories (0–14, 15–64, 65+)
---

Shows the population breakdown by broad age groups (children 0–14, working age 15–64,
elderly 65+) across administrative areas.

Calculated by classifying persons into age brackets and counting per bracket, grouped
by `area_code`. Render as a stacked bar chart showing the age composition across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
