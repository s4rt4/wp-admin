-- ============================================================
-- Tier 4 Migrations
-- Run once against the `wp-admin` database.
-- All statements use IF NOT EXISTS / IF EXISTS so they are
-- safe to re-run.
-- ============================================================

-- ──────────────────────────────────────────
-- 1. Media Folder Organizer
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `media_folders` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(255) NOT NULL,
  `parent_id`  INT NULL DEFAULT NULL,
  `created_by` INT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Maps a filesystem-relative file path to a virtual folder.
-- file_path = relative path from wp-admin/media/, e.g. "2026/02/img.jpg"
CREATE TABLE IF NOT EXISTS `media_assignments` (
  `file_path`   VARCHAR(500) NOT NULL,
  `folder_id`   INT NOT NULL,
  `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`file_path`),
  KEY `idx_folder` (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ──────────────────────────────────────────
-- 2. Multi-language / i18n
-- ──────────────────────────────────────────
ALTER TABLE `posts`
  ADD COLUMN IF NOT EXISTS `lang`           VARCHAR(10)  NOT NULL DEFAULT 'id'   AFTER `status`,
  ADD COLUMN IF NOT EXISTS `translation_of` INT          NULL DEFAULT NULL        AFTER `lang`;

ALTER TABLE `pages`
  ADD COLUMN IF NOT EXISTS `lang`           VARCHAR(10)  NOT NULL DEFAULT 'id'   AFTER `status`,
  ADD COLUMN IF NOT EXISTS `translation_of` INT          NULL DEFAULT NULL        AFTER `lang`;

-- ──────────────────────────────────────────
-- 3. Automation / Workflows
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `automations` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `name`           VARCHAR(255) NOT NULL,
  `trigger_event`  VARCHAR(64)  NOT NULL,
  `trigger_config` JSON,
  `conditions`     JSON,
  `actions`        JSON         NOT NULL,
  `active`         TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `automation_logs` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `automation_id`  INT          NOT NULL,
  `trigger_data`   JSON,
  `result`         VARCHAR(32)  NOT NULL DEFAULT 'ok',
  `message`        TEXT,
  `ran_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_auto`   (`automation_id`),
  KEY `idx_ran`    (`ran_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
