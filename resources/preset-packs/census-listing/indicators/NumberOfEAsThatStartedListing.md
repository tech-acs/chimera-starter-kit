---
type: indicator
title: Number of EAs That Started Listing
description: Count of enumeration areas that have begun the listing exercise
---

Shows how many enumeration areas (EAs) have started the listing process.

Calculated by counting distinct EAs that have at least one listed structure or household
record, grouped by administrative area or shown as a daily cumulative trend. Render as a
bar chart or line chart showing EA uptake over time.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
