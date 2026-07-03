---
type: indicator
title: Structures by Status
description: Distribution of structures by their listing status
---

Shows the breakdown of structures by listing status (e.g. completed, pending, not found,
partial) across administrative areas.

Calculated by counting structures grouped by status code and `area_code`. Render as a
stacked bar chart showing the status composition across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
