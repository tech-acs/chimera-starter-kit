---
type: indicator
title: Population Pyramid
description: Age-sex population pyramid showing the demographic structure
---

Displays a population pyramid (age distribution by sex) for the selected administrative
area.

Requires person-level data with age and sex fields. Render as a classic population
pyramid chart: males on the left, females on the right, age groups on the vertical axis.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
