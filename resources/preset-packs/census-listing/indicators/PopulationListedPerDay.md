---
type: indicator
title: Population Listed per Day
description: Daily count of persons listed across administrative areas
---

Shows the number of persons listed per day, tracking daily listing progress.

Calculated by summing household members from records grouped by listing date and
`area_code`. Render as a line chart showing daily population listing volume, or a
stacked bar chart broken down by region.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
