<?php
/**
 * Migration 002: Create migrations tracking table
 *
 * This is handled by the migrator itself, but we include it
 * here so the migration count is accurate.
 */
return [
    "CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `version` VARCHAR(100) NOT NULL,
        `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uq_version` (`version`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];
