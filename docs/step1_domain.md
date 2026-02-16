# STEP 1 — Define the Core Domain

## Concept
We established the foundational data entities for the Axiom Scrumban system. The design prioritizes **multi-tenancy** (via `organization_id` on all major tables) and **scalability** (via UUIDs and JSONB).

## Deliverables

### SQL Schema
The complete SQL schema is available in: `database/schema.sql`.

### Logic & Design Decisions
1.  **Multi-Tenancy**:
    *   **Strategy**: Shared Database, Shared Schema.
    *   **Implementation**: Every table (Board, Column, Task, Sprint) explicitly includes `organization_id`.
    *   **Why**: Simplifies migrations and operations compared to "Schema per Tenant" for Day 1. Indexes on `organization_id` ensure queries remain fast as tenants grow.

2.  **Scalability**:
    *   **UUIDs**: We use `uuid-ossp` (v4). This allows generating IDs on the client or application side, facilitating offline-first capabilities or distributed writes in the future.
    *   **JSONB**: Used for `tasks.metadata`. This allows storing flexible, schema-less data (tags, custom fields, frontend-specific config) without altering the main table schema, which is crucial for a Trello-like generic task system.

3.  **Performance**:
    *   **Composite Indexes**: Created indexes like `idx_columns_board_position` and `idx_tasks_column_position` to optimize the most common operation: rendering a board involves querying tasks by column and sorting them by position.

## Future Implications
*   **Microservices**: The explicit `organization_id` means we can later shard data by tenant easily.
*   **Kafka**: The `metadata` JSONB field allows capturing change events rich in context for audit logs.
