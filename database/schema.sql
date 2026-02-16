-- Axiom Scrumban - Day 1 Core Domain Schema
-- Database: PostgreSQL
-- Design: Multi-tenant (Shared Database, Shared Schema), UUID Primary Keys, JSONB Metadata

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Organizations (Tenants)
CREATE TABLE organizations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    schema_name VARCHAR(255) UNIQUE NOT NULL, -- Logical separation identifier
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Index for tenant lookup
CREATE INDEX idx_organizations_schema_name ON organizations(schema_name);

-- 2. Users (Belong to an Organization)
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    role VARCHAR(50) DEFAULT 'user', -- 'admin', 'user'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(organization_id, email) -- Email unique per tenant
);

-- 3. Boards
CREATE TABLE boards (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Index for fetching all boards of a tenant
CREATE INDEX idx_boards_org_id ON boards(organization_id);

-- 4. Columns (WIP Limits, Workflow)
CREATE TABLE columns (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    board_id UUID NOT NULL REFERENCES boards(id) ON DELETE CASCADE,
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE, -- Denormalized for easier RLS/filtering
    name VARCHAR(255) NOT NULL,
    position INTEGER NOT NULL, -- Order on the board
    wip_limit INTEGER DEFAULT 0, -- 0 means no limit
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_columns_board_id ON columns(board_id);
-- Composite index for ordering
CREATE INDEX idx_columns_board_position ON columns(board_id, position);

-- 5. Sprints (Time-boxing)
CREATE TABLE sprints (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    board_id UUID NOT NULL REFERENCES boards(id) ON DELETE CASCADE,
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    start_date TIMESTAMP WITH TIME ZONE,
    end_date TIMESTAMP WITH TIME ZONE,
    status VARCHAR(50) DEFAULT 'planned', -- 'active', 'completed', 'planned'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_sprints_board_status ON sprints(board_id, status);

-- 6. Tasks (The Core Unit)
CREATE TABLE tasks (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    column_id UUID NOT NULL REFERENCES columns(id) ON DELETE CASCADE,
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    sprint_id UUID REFERENCES sprints(id) ON DELETE SET NULL, -- Nullable if in backlog/no sprint
    title VARCHAR(255) NOT NULL,
    description TEXT,
    position INTEGER NOT NULL, -- Order in the column
    metadata JSONB DEFAULT '{}', -- Custom fields, tagging, etc.
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Indexes for Tasks
CREATE INDEX idx_tasks_column_id ON tasks(column_id);
CREATE INDEX idx_tasks_org_id ON tasks(organization_id);
CREATE INDEX idx_tasks_sprint_id ON tasks(sprint_id);
-- Index for JSONB queries
CREATE INDEX idx_tasks_metadata ON tasks USING GIN (metadata);
-- Index to help with ordering within a column
CREATE INDEX idx_tasks_column_position ON tasks(column_id, position);

-- Why Production-Ready?
-- 1. UUIDs: Prevents ID enumeration attacks and allows easy merging of databases if needed; ready for distributed systems.
-- 2. Organization_ID everywhere: Ensures every query can be scoped by tenant ID for security + performance partitioning.
-- 3. JSONB: Allows adding fields (e.g., 'priority', 'tags', 'assignees') without schema migrations.
-- 4. Indexes: Covered foreign keys and common query patterns (sorting by position).
