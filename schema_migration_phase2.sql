-- =============================================================================
-- ThomasCRM  —  Phase 2 Migration
-- Run this against an existing thomas_crm database.
-- =============================================================================

USE thomas_crm;

-- ── Recurring task fields ──────────────────────────────────────────────────────
ALTER TABLE tasks
    ADD COLUMN IF NOT EXISTS is_recurring        TINYINT(1)   NOT NULL DEFAULT 0   AFTER project_id,
    ADD COLUMN IF NOT EXISTS recurrence_type     ENUM('daily','weekly','monthly','yearly') DEFAULT NULL AFTER is_recurring,
    ADD COLUMN IF NOT EXISTS recurrence_interval INT UNSIGNED NOT NULL DEFAULT 1    AFTER recurrence_type,
    ADD COLUMN IF NOT EXISTS recurrence_end_date DATE         DEFAULT NULL          AFTER recurrence_interval,
    ADD COLUMN IF NOT EXISTS parent_task_id      INT UNSIGNED DEFAULT NULL          AFTER recurrence_end_date;

-- ── Module registry ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS modules (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name       VARCHAR(50)   NOT NULL UNIQUE,
    label      VARCHAR(100)  NOT NULL,
    icon       VARCHAR(80)   NOT NULL DEFAULT 'fa-puzzle-piece',
    enabled    TINYINT(1)    NOT NULL DEFAULT 0,
    sort_order INT           NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modules (name, label, icon, enabled, sort_order) VALUES
    ('invoicing',   'Invoicing',   'fa-file-invoice-dollar', 0, 1),
    ('it_planning', 'IT Planning', 'fa-server',              0, 2);

-- ── Invoices ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS invoices (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    number       VARCHAR(30)   NOT NULL UNIQUE,
    contact_id   INT UNSIGNED  DEFAULT NULL,
    project_id   INT UNSIGNED  DEFAULT NULL,
    issue_date   DATE          NOT NULL,
    due_date     DATE          DEFAULT NULL,
    status       ENUM('draft','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
    notes        TEXT          DEFAULT NULL,
    tax_rate     DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_invoices_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoices_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoice_items (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    invoice_id  INT UNSIGNED   NOT NULL,
    description VARCHAR(255)   NOT NULL,
    quantity    DECIMAL(10,2)  NOT NULL DEFAULT 1.00,
    unit_price  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    amount      DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    sort_order  INT            NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    CONSTRAINT fk_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── IT Assets ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS it_assets (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name             VARCHAR(200)  NOT NULL,
    asset_type       ENUM('laptop','desktop','server','monitor','phone','tablet','printer','network','other') NOT NULL DEFAULT 'other',
    serial_number    VARCHAR(100)  DEFAULT NULL,
    status           ENUM('active','inactive','maintenance','retired') NOT NULL DEFAULT 'active',
    purchase_date    DATE          DEFAULT NULL,
    warranty_expiry  DATE          DEFAULT NULL,
    assigned_to      INT UNSIGNED  DEFAULT NULL,
    location         VARCHAR(200)  DEFAULT NULL,
    notes            TEXT          DEFAULT NULL,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_it_assets_contact FOREIGN KEY (assigned_to) REFERENCES contacts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
