-- Settings table (key-value pairs)
CREATE TABLE IF NOT EXISTS `settings` (
    `key`        VARCHAR(100) NOT NULL,
    `value`      TEXT         DEFAULT NULL,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default: USD and CHF enabled
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
    ('currencies',        'USD,CHF'),
    ('default_currency',  'USD');
