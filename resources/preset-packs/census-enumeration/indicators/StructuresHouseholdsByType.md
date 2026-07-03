---
type: indicator
title: Structures/Households by Type
description: Distribution of structures and households by building type
---

Shows the breakdown of structures and households by type (e.g. residential, commercial,
institutional) across administrative areas.

Calculated by counting records grouped by `structure_type` and `area_code`. Render as
a stacked bar chart showing the composition of structure types across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
