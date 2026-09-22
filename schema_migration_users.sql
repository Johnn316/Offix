-- =============================================================================
-- ThomasCRM — Users Migration
-- Run against an existing thomas_crm database.
-- Default admin login: admin@thomascrm.com / admin1234
-- =============================================================================

USE thomas_crm;

CREATE TABLE IF NOT EXISTS users (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name         VARCHAR(150)  NOT NULL,
    email        VARCHAR(255)  NOT NULL UNIQUE,
    password     VARCHAR(255)  NOT NULL          COMMENT 'bcrypt hash',
    role         ENUM('admin','manager','user')  NOT NULL DEFAULT 'user',
    language     VARCHAR(10)   NOT NULL DEFAULT 'en-US'
                     COMMENT 'BCP 47 tag: en-US, de-CH',
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    avatar       VARCHAR(255)  DEFAULT NULL      COMMENT 'filename in uploads/avatars/',
    last_login   TIMESTAMP     NULL DEFAULT NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_users_email (email),
    INDEX idx_users_role  (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin (password: admin1234)
INSERT IGNORE INTO users (name, email, password, role, language) VALUES
    ('Admin', 'admin@thomascrm.com',
     '$2y$10$O3pXwtlbGdsYUzWg3gFwTuuzbqxOimfVdd0CiKt9na9IH1iHU5WBq',
     'admin', 'en-US');
