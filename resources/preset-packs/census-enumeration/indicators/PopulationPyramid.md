---
type: indicator
title: Population Pyramid
description: Age-sex population pyramid showing the demographic structure
---

Displays a population pyramid (age distribution by sex) for the selected administrative
area.

Requires person-level data with age and sex fields. Filter out invalid
or non-roster rows and restrict sex to Male and Female. Exclude unknown
or not-stated ages.

Classify persons into 5-year age buckets (e.g. `FLOOR(age / 5) * 5`),
label as `{min}-{min+4}`. Count males as negative and females as positive.
Group and order by the numeric lower bound, not the string label.

Render as a classic population pyramid with horizontal bars (`orientation: 'h'`),
`barmode: 'relative'`, youngest age group at the top (`yaxis.autorange: 'reversed'`).

**Layout caveats (two critical overrides):**

1. `xaxis.type` must be `'linear'`. The default layout injects `'category'`,
   which treats numeric population counts as discrete labels and breaks
   bar positioning. The edit-chart call **must** explicitly set this to `'linear'`.

2. Set `xaxis.showticklabels: false` — the x-axis carries negative values for
   males (extending left) and positive values for females (extending right).
   Hiding tick labels avoids displaying confusing negative signs. The axis title
   "Population" and per-bar tooltips provide enough context.

The x-axis shows population counts (not area names), so `lastlyAreaLeftJoinData()` is
not required — the area context is conveyed through the chart card's title and scope.
