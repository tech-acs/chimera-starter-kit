---
type: indicator
title: Population Enumerated per Day
description: Daily count of persons enumerated across administrative areas
---

Shows the number of persons enumerated per day, tracking daily enumeration progress.

Calculated by summing household members from records grouped by enumeration date and
`area_code`. Render as a line chart showing daily population enumeration volume, or a
stacked bar chart broken down by region.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
