---
type: indicator
title: Number of EAs That Started Enumeration
description: Count of enumeration areas that have begun household enumeration
---

Shows how many enumeration areas (EAs) have started the enumeration process.

Calculated by counting distinct EAs that have at least one enumerated household record,
grouped by administrative area or shown as a daily cumulative trend. Render as a bar
chart or line chart showing EA uptake over time.

The `getData()` method should join area data via `lastlyAreaLeftJoinData()` so the
area name is available for chart labels.
