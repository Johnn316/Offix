-- =============================================================================
-- ThomasCRM  —  Database Schema
-- Engine: MySQL 5.7+ / MariaDB 10.3+
--
-- Import:  mysql -u root -p thomas_crm < schema.sql
--   OR paste this file into phpMyAdmin > SQL tab.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS thomas_crm
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE thomas_crm;

-- ── contacts ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contacts (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name       VARCHAR(150)    NOT NULL,
    email      VARCHAR(255)    DEFAULT NULL,
    phone      VARCHAR(50)     DEFAULT NULL,
    company    VARCHAR(150)    DEFAULT NULL,
    notes      TEXT            DEFAULT NULL,
    address1   VARCHAR(255)    DEFAULT NULL,
    address2   VARCHAR(255)    DEFAULT NULL,
    city       VARCHAR(100)    DEFAULT NULL,
    state      VARCHAR(100)    DEFAULT NULL,
    zip        VARCHAR(20)     DEFAULT NULL,
    country    VARCHAR(100)    DEFAULT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_contacts_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── projects ──────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS projects (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(200)    NOT NULL,
    description TEXT            DEFAULT NULL,
    status      ENUM('active','on_hold','completed','cancelled')
                                NOT NULL DEFAULT 'active',
    start_date  DATE            DEFAULT NULL,
    end_date    DATE            DEFAULT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── tasks ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tasks (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    title       VARCHAR(255)    NOT NULL,
    description TEXT            DEFAULT NULL,
    due_date    DATE            DEFAULT NULL,
    status      ENUM('pending','in_progress','done')
                                NOT NULL DEFAULT 'pending',
    priority    ENUM('low','medium','high')
                                NOT NULL DEFAULT 'medium',
    contact_id  INT UNSIGNED    DEFAULT NULL,
    project_id  INT UNSIGNED    DEFAULT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_tasks_contact FOREIGN KEY (contact_id)
        REFERENCES contacts (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_tasks_project FOREIGN KEY (project_id)
        REFERENCES projects (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── seed data (optional — remove if you prefer a clean start) ─────────────────
INSERT INTO contacts (name, email, phone, company, notes) VALUES
    ('Alice Wanjiku',  'alice@example.com',  '+254 700 111 222', 'Savannah Tech',    'Met at Nairobi Tech Week 2025'),
    ('Brian Otieno',   'brian@example.com',  '+254 711 333 444', 'LakeView Farms',   'Referred by Alice'),
    ('Carol Njeri',    'carol@example.com',  '+254 722 555 666', 'Nairobi Supplies', NULL);

INSERT INTO projects (name, description, status, start_date, end_date) VALUES
    ('Website Redesign',   'Full redesign of the company website', 'active',    '2026-01-15', '2026-04-30'),
    ('CRM Integration',    'Integrate CRM with accounting system',  'on_hold',  '2026-03-01', '2026-06-30'),
    ('Mobile App Phase 1', 'React Native client-facing app',        'active',   '2026-02-01', '2026-07-31');

INSERT INTO tasks (title, description, due_date, status, priority, contact_id, project_id) VALUES
    ('Send proposal',       'Draft and send project proposal',    '2026-07-05', 'pending',     'high',   1, 1),
    ('Design wireframes',   'Low-fi wireframes for home & about', '2026-07-10', 'in_progress', 'medium', NULL, 1),
    ('DB schema review',    'Review schema with DBA',             '2026-07-02', 'done',        'high',   NULL, 2),
    ('Collect requirements','Initial call with stakeholders',      '2026-07-15', 'pending',     'medium', 2, 3);
