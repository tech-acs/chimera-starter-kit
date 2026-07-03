---
type: scorecard
title: Estimated Days to Complete Enumeration
description: Estimated days remaining to complete enumeration based on current daily performance
---

Displays the projected number of days needed to complete the remaining enumeration work.

Calculated by dividing the remaining households to enumerate by the average daily
enumeration rate over recent days. Renders as a big-number scorecard with a trend
indicator showing whether the projection is improving or worsening.

**Requires a reference value.** Use the MCP tool to discover available reference values; examine each indicator's `description` to select the correct one defining the per-EA enumeration target. Pass the chosen reference value name as `referenceValueToInclude` when joining area data. This injects the target value as a `ref_value` column alongside area information.
