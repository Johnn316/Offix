CREATE TABLE IF NOT EXISTS `documents` (
    `id`         CHAR(32)     NOT NULL,
    `owner_id`   INT UNSIGNED NOT NULL,
    `title`      VARCHAR(255) NOT NULL DEFAULT 'Untitled Document',
    `content`    LONGTEXT     DEFAULT NULL COMMENT 'Quill delta JSON',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_doc_owner`   (`owner_id`),
    KEY `idx_doc_updated` (`updated_at`),
    CONSTRAINT `fk_doc_owner` FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_versions` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_id` CHAR(32)     NOT NULL,
    `content`     LONGTEXT     NOT NULL,
    `created_by`  INT UNSIGNED NOT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_dv_doc` (`document_id`, `created_at`),
    CONSTRAINT `fk_dv_doc`  FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_dv_user` FOREIGN KEY (`created_by`)  REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `signaling_messages` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id`     CHAR(32)     NOT NULL,
    `sender_id`   INT UNSIGNED NOT NULL,
    `receiver_id` INT UNSIGNED DEFAULT NULL,
    `type`        VARCHAR(20)  NOT NULL,
    `payload`     JSON         NOT NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sig_room` (`room_id`, `receiver_id`, `id`),
    KEY `idx_sig_clean` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
