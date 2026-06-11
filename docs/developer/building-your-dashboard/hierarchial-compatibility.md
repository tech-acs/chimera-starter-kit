---
---

# Hierarchical compatibility (inapplicable levels)

Not every KPI is relevant at every geographic resolution. For example, while "Death Rates" might be statistically significant at the National or County level, the data may be too sparse or unavailable at the Sub-county or EA level.

To maintain data integrity, the kit allows components (Indicators, Scorecards and Gauges) to declare which levels they do not support.

![Inapplicable levels](/img/developer/building-your-dashboard/level-discrimination.png)

When a level is selected in the filter bar that is set as "unsupported" by a component, it will displays a generic, message (*The current area level is inapplicable to this indicator*)