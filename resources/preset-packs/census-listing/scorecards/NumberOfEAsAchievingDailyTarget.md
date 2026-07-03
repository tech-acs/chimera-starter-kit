---
type: scorecard
title: Number of EAs Achieving Daily Target
description: Count of enumeration areas that met their daily listing target
---

Displays the number of enumeration areas (EAs) that achieved their assigned daily listing target.

Calculated by comparing structures or households listed per EA against their daily target and counting those that met or exceeded it. Renders as a big-number scorecard with daily trend indicator.

**Requires a reference value.** Use the MCP tool to discover available reference values; examine each indicator's `description` to select the correct one defining the per-EA daily listing target. Pass the chosen reference value name as `referenceValueToInclude` when joining area data. This injects the target value as a `ref_value` column alongside area information.
