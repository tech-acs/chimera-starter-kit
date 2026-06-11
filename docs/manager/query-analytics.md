---
---

# Query Analytics

The Query Analytics module is a specialized performance monitoring tool designed for developers and system administrators. It tracks the efficiency of the dashboard's database interactions by logging slow-running queries, allowing for targeted optimization of complex indicators and reports.

This module provides a deep-dive into the backend performance of the platform. It specifically targets "bottlenecks"—queries that take an excessive amount of time to execute and could potentially degrade the user experience.

## Performance Thresholds
The system does not log every database interaction to avoid cluttering the analytics. Instead, it uses a configurable threshold to capture only the most resource-intensive tasks.

- **Log Logic**: By default, the system is configured to log only queries that take longer than 10 seconds to execute. This threshold can be adjusted within the system's .env file. A red status message at the top of the table explicitly states the current logging threshold, ensuring administrators know exactly which queries are being captured.

## Analytics Data Points
When a query exceeds the time threshold, the system captures a detailed record:

- **User**: Identifies the specific account that triggered the slow query.

- **From**: An icon-supported link showing the specific indicator or component name (e.g., Average interview time) that initiated the database request.

- **Path**: The geographic or organizational scope of the request (e.g., National).

- **Started At**: A precise timestamp of when the execution began.

- **Query Time (seconds)**: The total duration of the execution, allowing developers to see exactly how far over the threshold the query ran.

## Operational Utility

The table is ordered by query time, placing the slowest and most resource-intensive queries at the top of the list. This enables a "top-down" approach to system optimization, ensuring that the components causing the most significant delays are addressed first.

- **Database Optimization**: By identifying specific paths (like a complex population pyramid) that trigger slow queries, developers can optimize the underlying SQL logic or add necessary database indexing.

- **Infrastructure Scaling**: If many queries are appearing in this log simultaneously, it may indicate that the server infrastructure needs more resources to handle the current data load.