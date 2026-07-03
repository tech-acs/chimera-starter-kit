---
type: indicator
title: Population by Five-Year Age Group
description: Population distribution across five-year age cohorts
---

Shows the population breakdown by five-year age groups (0–4, 5–9, 10–14, etc.) across
administrative areas.

Calculated by classifying persons into five-year age cohorts and counting per cohort,
grouped by `area_code`. Render as a bar chart or population pyramid showing age
structure across regions.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
