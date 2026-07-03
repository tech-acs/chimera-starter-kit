---
type: indicator
title: Whipple Index (Age 5)
description: Age heaping index measuring preference for ages ending in 0 or 5, calculated using the 5-year method
---

Shows the Whipple Index measuring age heaping (digit preference) for ages ending in 0
or 5, using the standard 5-year age group method.

Calculated by comparing the number of persons reporting ages ending in 0 or 5 to the
expected number in a uniformly distributed population, for the 23–62 age range. Values
close to 100 indicate no heaping; higher values indicate stronger digit preference.
Render as a bar chart comparing index values across administrative areas.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
