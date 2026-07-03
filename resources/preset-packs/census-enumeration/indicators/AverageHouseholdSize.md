---
type: indicator
title: Average Household Size
description: Average number of persons per household across administrative areas
---

Shows the average household size (persons per household) broken down by administrative area.

Calculated as `SUM(total_household_members) / COUNT(households)` on the household record,
grouped by `area_code`. Render as a bar chart comparing average household size across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
