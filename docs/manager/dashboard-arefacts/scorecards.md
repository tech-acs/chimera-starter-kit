---
---

# Scorecards

Scorecards provide high-level, at-a-glance summaries of key metrics, such as totals, averages, or rates. Unlike detailed charts, scorecards are designed to highlight a single critical value (e.g., Total households or Death rate) to provide immediate context for the user.

## Scorecards List

The management screen offers a centralized directory of all summary components active within the system.

- **Search and Discovery**: A real-time Search bar allows administrators to filter scorecards by title, system name, or data source.

- **Summary Attributes**:

    - **Title**: The display name and its underlying system namespace (e.g., Households/TotalHouseholds).

    - **Data Source**: Indicates which database connection provides the raw numbers for the scorecard (e.g., Households).

    - **Scope**: Defines the possible target views/pages of the scorecard. Values include Everywhere (displayed on the home page and all area insights pages) or Area insights only (appearing only on area insights pages).

    - **Published Status**: A visual "Yes" badge confirms if the scorecard is live on the dashboard.

- **Operational Actions**:

    - **Create New**: Initiates the setup for a new scorecard component.

    - **Edit**: Allows for modifications to the title, data source, or geographic scope.

    - **Flush**: Clears the cached calculation for the scorecard, forcing the system to re-query the database for the most up-to-date value.

## Creating Scorecards
The Create New Scorecard interface is used to initialize a high-level summary component. This form establishes the backend connection and the display identity for a single metric that will be featured prominently on the dashboard.

### Data Source Selection

- **Target Database**: You must select the specific Data Source from which the scorecard will pull its information.

- **Connection Context**: Ensure the selected source contains the relevant census or survey data required for the metric (e.g., selecting the "Households" source to calculate total household counts).

### Component Naming (Backend)

- **Scorecard Name**: This field defines the internal system-level component name.

    - **Requirement**: The name must be in CamelCase (e.g., TotalHouseholds or Household/BirthRate).

    - **Function**: This string serves as the unique identifier for the underlying code component that executes the data query.

### Public Display Identity

- **Reader Friendly Title**: Enter the localized, official title that will be visible to end-users on the dashboard (e.g., Total Households Enumerated).

- **Multilingual Support**: This field supports entries for different languages (indicated by the "EN" toggle) to ensure the scorecard is accessible to a diverse user base.