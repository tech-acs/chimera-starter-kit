---
type: scorecard
title: Estimated Days to Complete Listing
description: Estimated days remaining to complete listing based on current daily performance
---

Displays the projected number of days needed to complete the remaining listing work.

Calculated by dividing the remaining structures to list by the average daily listing rate over recent days. Renders as a big-number scorecard with a trend indicator.

**Requires a reference value.** You must discover available reference values and select the one defining the per-EA listing target. Pass the chosen reference value name as `referenceValueToInclude` when joining area data. This injects the target value as a `ref_value` column alongside area information.
