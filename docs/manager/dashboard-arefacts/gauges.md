---
---

# Gauges

Gauges are specialized visual indicators used to track progress toward a specific goal or to visualize performance against a standardized scale (e.g., 0% to 100%). They provide a rapid, intuitive way for stakeholders to see "how much is done" or to evaluate an "overall score" at a glance.

## Gauges List

The management interface provides a summary of all active gauge components within the application.

- **Search and Discovery**: A real-time Search bar allows administrators to find specific gauges by title, subtitle, name, or their associated data source.

- **Metric Overview**: The table lists key identifying information for each gauge:

    - **Title**: The primary label (e.g., Completion or Overall score).

    - **Subtitle**: A secondary label providing more context (e.g., How much is done or Total score out of 100).

    - **Data Source**: The database connection providing the underlying values (e.g., Households).

    - **Published Status**: A visual "Yes" badge confirms if the gauge is live on the dashboard.

- **Operational Actions**:

    - **Create New**: A primary button to initiate the configuration of a new gauge component.

    - **Edit**: Used to modify titles, subtitles, visibility, etc.

    - **Flush**: Clears the current cached calculation, forcing the system to re-query the data source to update the progress indicator.