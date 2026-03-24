<?php
/**
 * CMS Installer — runs once, creates all tables, writes wp-config.php
 * Supports: Bahasa Indonesia (id) and English (en)
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// Already installed → go to login
if (file_exists(__DIR__ . '/wp-config.php')) {
    header('Location: login.php'); exit;
}

// ── Translations ──────────────────────────────────────────────────────────────
$LANG = [
    'id' => [
        'welcome'        => 'Selamat Datang',
        'welcome_sub'    => 'Sebelum memulai, kami memerlukan beberapa informasi database dan situs.',
        'choose_lang'    => 'Pilih Bahasa',
        'step_db'        => 'Konfigurasi Database',
        'step_site'      => 'Informasi Situs',
        'step_done'      => 'Selesai',
        'db_host'        => 'Host Database',
        'db_name'        => 'Nama Database',
        'db_user'        => 'Username Database',
        'db_pass'        => 'Password Database',
        'db_host_ph'     => 'localhost',
        'db_test'        => 'Test Koneksi',
        'db_ok'          => 'Koneksi berhasil!',
        'db_fail'        => 'Koneksi gagal',
        'db_note'        => 'Jika tidak tahu, hubungi penyedia hosting Anda.',
        'next'           => 'Lanjut →',
        'back'           => '← Kembali',
        'site_name'      => 'Nama Situs',
        'site_url'       => 'URL Situs',
        'admin_user'     => 'Username Admin',
        'admin_email'    => 'Email Admin',
        'admin_pass'     => 'Password Admin',
        'admin_pass2'    => 'Konfirmasi Password',
        'install_btn'    => 'Install Sekarang',
        'installing'     => 'Menginstall…',
        'pass_mismatch'  => 'Password tidak cocok.',
        'pass_short'     => 'Password minimal 6 karakter.',
        'fill_all'       => 'Harap isi semua kolom yang wajib diisi.',
        'done_title'     => 'Instalasi Berhasil!',
        'done_sub'       => 'CMS Anda sudah siap digunakan.',
        'done_login'     => 'Pergi ke Halaman Login',
        'done_user'      => 'Username',
        'done_pass'      => 'Password yang Anda buat',
        'existing_db'    => 'Database sudah ada — tabel yang ada tidak diubah.',
        'new_db'         => 'Database baru dibuat.',
        'tables_created' => 'Semua tabel berhasil dibuat.',
        'defaults_ins'   => 'Data default berhasil dimasukkan.',
        'config_saved'   => 'Konfigurasi disimpan.',
        'step1_of_2'     => 'Langkah 1 dari 2',
        'step2_of_2'     => 'Langkah 2 dari 2',
        'db_name_note'   => 'Database akan dibuat jika belum ada.',
        'req_title'      => 'Cek Persyaratan Sistem',
        'demo_label'     => 'Install konten demo',
        'demo_desc'      => 'Tambahkan contoh post, halaman, dan kategori agar CMS tidak kosong.',
        'demo_ins'       => 'Konten demo berhasil dimasukkan.',
    ],
    'en' => [
        'welcome'        => 'Welcome',
        'welcome_sub'    => 'Before we get started, we need some information about the database and your site.',
        'choose_lang'    => 'Choose Language',
        'step_db'        => 'Database Configuration',
        'step_site'      => 'Site Information',
        'step_done'      => 'Complete',
        'db_host'        => 'Database Host',
        'db_name'        => 'Database Name',
        'db_user'        => 'Database Username',
        'db_pass'        => 'Database Password',
        'db_host_ph'     => 'localhost',
        'db_test'        => 'Test Connection',
        'db_ok'          => 'Connection successful!',
        'db_fail'        => 'Connection failed',
        'db_note'        => 'If you don\'t know this, contact your hosting provider.',
        'next'           => 'Next →',
        'back'           => '← Back',
        'site_name'      => 'Site Name',
        'site_url'       => 'Site URL',
        'admin_user'     => 'Admin Username',
        'admin_email'    => 'Admin Email',
        'admin_pass'     => 'Admin Password',
        'admin_pass2'    => 'Confirm Password',
        'install_btn'    => 'Install Now',
        'installing'     => 'Installing…',
        'pass_mismatch'  => 'Passwords do not match.',
        'pass_short'     => 'Password must be at least 6 characters.',
        'fill_all'       => 'Please fill in all required fields.',
        'done_title'     => 'Installation Complete!',
        'done_sub'       => 'Your CMS is ready to use.',
        'done_login'     => 'Go to Login',
        'done_user'      => 'Username',
        'done_pass'      => 'The password you set',
        'existing_db'    => 'Database already exists — existing tables were not modified.',
        'new_db'         => 'New database created.',
        'tables_created' => 'All tables created successfully.',
        'defaults_ins'   => 'Default data inserted.',
        'config_saved'   => 'Configuration saved.',
        'step1_of_2'     => 'Step 1 of 2',
        'step2_of_2'     => 'Step 2 of 2',
        'db_name_note'   => 'Database will be created if it doesn\'t exist.',
        'demo_label'     => 'Install demo content',
        'demo_desc'      => 'Add sample posts, pages, and categories so the CMS isn\'t empty.',
        'demo_ins'       => 'Demo content installed successfully.',
    ],
];

$lang = (isset($_SESSION['install_lang']) && isset($LANG[$_SESSION['install_lang']]))
    ? $_SESSION['install_lang'] : 'id';
$t = $LANG[$lang];

// ── AJAX: Test DB connection ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'test_db') {
    header('Content-Type: application/json');
    $host = trim($_POST['host'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        // Try to create DB if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '', $name) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── POST: Language ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'lang') {
    $l = $_POST['lang'] ?? 'id';
    $_SESSION['install_lang'] = in_array($l, ['id', 'en']) ? $l : 'id';
    header('Location: install.php?step=req'); exit;
}

// ── System Requirements Check ─────────────────────────────────────────────────
function check_requirements(): array {
    $checks = [];

    // PHP Version
    $php_ver = PHP_VERSION;
    $checks['php'] = ['label_id' => 'PHP ≥ 7.4', 'label_en' => 'PHP ≥ 7.4', 'value' => $php_ver, 'pass' => version_compare($php_ver, '7.4.0', '>='), 'required' => true];

    // Required extensions
    $exts = ['pdo' => 'PDO', 'pdo_mysql' => 'PDO MySQL', 'mbstring' => 'Mbstring', 'json' => 'JSON', 'curl' => 'cURL', 'openssl' => 'OpenSSL'];
    foreach ($exts as $ext => $name) {
        $checks['ext_' . $ext] = ['label_id' => "Ekstensi $name", 'label_en' => "$name Extension", 'value' => extension_loaded($ext) ? 'OK' : 'Missing', 'pass' => extension_loaded($ext), 'required' => true];
    }

    // GD or Imagick (one of them)
    $has_gd = extension_loaded('gd');
    $has_imagick = extension_loaded('imagick');
    $checks['ext_image'] = ['label_id' => 'GD atau Imagick', 'label_en' => 'GD or Imagick', 'value' => $has_gd ? 'GD' : ($has_imagick ? 'Imagick' : 'Missing'), 'pass' => $has_gd || $has_imagick, 'required' => false];

    // Writable directory
    $writable = is_writable(__DIR__);
    $checks['writable'] = ['label_id' => 'Direktori wp-admin writable', 'label_en' => 'wp-admin directory writable', 'value' => $writable ? 'Yes' : 'No', 'pass' => $writable, 'required' => true];

    // Media directory
    $media_dir = __DIR__ . '/media';
    if (!is_dir($media_dir)) @mkdir($media_dir, 0755, true);
    $media_writable = is_dir($media_dir) && is_writable($media_dir);
    $checks['media_writable'] = ['label_id' => 'Direktori media writable', 'label_en' => 'Media directory writable', 'value' => $media_writable ? 'Yes' : 'No', 'pass' => $media_writable, 'required' => false];

    // Upload max size
    $upload_max = ini_get('upload_max_filesize');
    $checks['upload'] = ['label_id' => 'Upload max filesize', 'label_en' => 'Upload max filesize', 'value' => $upload_max, 'pass' => true, 'required' => false];

    // Memory limit
    $mem = ini_get('memory_limit');
    $mem_bytes = (int)$mem * (stripos($mem, 'G') !== false ? 1073741824 : (stripos($mem, 'M') !== false ? 1048576 : 1));
    $checks['memory'] = ['label_id' => 'Memory limit ≥ 64M', 'label_en' => 'Memory limit ≥ 64M', 'value' => $mem, 'pass' => $mem_bytes >= 67108864 || $mem == '-1', 'required' => false];

    return $checks;
}

// ── POST: DB Config → Step 2 ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === '1') {
    $_SESSION['install_db'] = [
        'host' => trim($_POST['db_host'] ?? 'localhost'),
        'name' => trim($_POST['db_name'] ?? ''),
        'user' => trim($_POST['db_user'] ?? ''),
        'pass' => $_POST['db_pass'] ?? '',
    ];
    header('Location: install.php?step=2'); exit;
}

// ── POST: Site Info → Run Installation ───────────────────────────────────────
$install_result = null;
$install_error  = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === '2') {
    $site_name   = trim($_POST['site_name']   ?? '');
    $site_url    = rtrim(trim($_POST['site_url'] ?? ''), '/');
    $admin_user  = trim($_POST['admin_user']  ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_pass  = $_POST['admin_pass']  ?? '';
    $admin_pass2 = $_POST['admin_pass2'] ?? '';

    if (!$site_name || !$site_url || !$admin_user || !$admin_email || !$admin_pass) {
        $install_error = $t['fill_all'];
    } elseif ($admin_pass !== $admin_pass2) {
        $install_error = $t['pass_mismatch'];
    } elseif (strlen($admin_pass) < 6) {
        $install_error = $t['pass_short'];
    } else {
        $install_demo = isset($_POST['install_demo']) && $_POST['install_demo'] === '1';
        $install_result = run_installation(
            $_SESSION['install_db'] ?? [],
            $site_name, $site_url, $admin_user, $admin_email, $admin_pass, $lang, $install_demo
        );
    }
}

// ── Current step ─────────────────────────────────────────────────────────────
$step_raw = $_GET['step'] ?? '0';
$step = ($step_raw === 'req') ? 'req' : intval($step_raw);
if ($install_result !== null && !isset($install_result['error'])) $step = 3;

// ── Installation runner ───────────────────────────────────────────────────────
function run_installation(array $db, string $site_name, string $site_url,
    string $admin_user, string $admin_email, string $admin_pass, string $lang, bool $demo = false): array
{
    $log = [];
    try {
        // 1. Connect (DB was already tested, but retry here)
        $pdo = new PDO(
            "mysql:host={$db['host']};charset=utf8mb4",
            $db['user'], $db['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $dbname = str_replace('`', '', $db['name']);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");

        // Check if DB had existing tables
        $existing = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $log[] = count($existing) ? 'existing_db' : 'new_db';

        // 2. Create all tables
        create_all_tables($pdo);
        $log[] = 'tables_created';

        // 3. Insert default data
        insert_defaults($pdo, $site_name, $site_url, $admin_email, $lang, $site_name);
        insert_admin_user($pdo, $admin_user, $admin_email, $admin_pass);
        $log[] = 'defaults_ins';

        // 3b. Demo content
        if ($demo) {
            insert_demo_content($pdo, $lang);
            $log[] = 'demo_ins';
        }

        // 4. Write wp-config.php
        write_wp_config($db['host'], $dbname, $db['user'], $db['pass']);
        if (!file_exists(__DIR__ . '/wp-config.php')) {
            throw new Exception('Cannot write wp-config.php — directory is not writable by the web server. Run: sudo chown www-data:www-data ' . __DIR__);
        }
        $log[] = 'config_saved';

        return ['ok' => true, 'log' => $log, 'admin_user' => $admin_user];

    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// ── Create all 32 tables ─────────────────────────────────────────────────────
function create_all_tables(PDO $pdo): void
{
    $tables = [
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `username` varchar(50) NOT NULL,
            `email` varchar(255) DEFAULT NULL,
            `role` enum('admin','editor','author','contributor','subscriber') DEFAULT 'subscriber',
            `password` varchar(255) NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `profile_picture` varchar(255) DEFAULT NULL,
            `two_fa_enabled` tinyint(1) NOT NULL DEFAULT 0,
            `two_fa_otp` varchar(6) DEFAULT NULL,
            `two_fa_otp_expires` datetime DEFAULT NULL,
            `two_fa_backup_codes` text DEFAULT NULL,
            `last_login` datetime DEFAULT NULL,
            `last_active` datetime DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `user_roles_caps` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `role_name` varchar(100) NOT NULL,
            `capabilities` text NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `options` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `option_name` varchar(191) NOT NULL,
            `option_value` longtext DEFAULT NULL,
            `autoload` varchar(20) DEFAULT 'yes',
            UNIQUE KEY `uq_name` (`option_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `posts` (
            `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` text NOT NULL,
            `slug` varchar(200) NOT NULL DEFAULT '',
            `content` longtext NOT NULL,
            `featured_image` varchar(255) DEFAULT NULL,
            `excerpt` text DEFAULT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'draft',
            `visibility` varchar(20) NOT NULL DEFAULT 'public',
            `author_id` bigint UNSIGNED NOT NULL DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `views` int DEFAULT 0,
            `meta_title` varchar(255) DEFAULT NULL,
            `meta_desc` text DEFAULT NULL,
            `focus_keyword` varchar(255) DEFAULT NULL,
            `is_featured` tinyint(1) DEFAULT 0,
            `locked_by` int DEFAULT NULL,
            `locked_at` datetime DEFAULT NULL,
            `scheduled_at` datetime DEFAULT NULL,
            `lang` varchar(10) NOT NULL DEFAULT 'id',
            `translation_of` int DEFAULT NULL,
            INDEX `idx_slug`   (`slug`),
            INDEX `idx_status` (`status`),
            INDEX `idx_author` (`author_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `pages` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `content` longtext DEFAULT NULL,
            `builder_type` varchar(50) NOT NULL DEFAULT 'grapesjs',
            `status` varchar(20) NOT NULL DEFAULT 'draft',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `views` int DEFAULT 0,
            `locked_by` int DEFAULT NULL,
            `locked_at` datetime DEFAULT NULL,
            `lang` varchar(10) NOT NULL DEFAULT 'id',
            `translation_of` int DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `categories` (
            `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(200) NOT NULL,
            `slug` varchar(200) NOT NULL,
            `description` longtext DEFAULT NULL,
            `parent` bigint UNSIGNED NOT NULL DEFAULT 0,
            `count` bigint NOT NULL DEFAULT 0,
            INDEX `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `tags` (
            `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(200) NOT NULL,
            `slug` varchar(200) NOT NULL,
            `count` bigint NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `post_categories` (
            `post_id` int NOT NULL,
            `category_id` int NOT NULL,
            PRIMARY KEY (`post_id`, `category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `post_tags` (
            `post_id` int NOT NULL,
            `tag_id` int NOT NULL,
            PRIMARY KEY (`post_id`, `tag_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `post_relationships` (
            `object_id` bigint UNSIGNED NOT NULL,
            `term_taxonomy_id` bigint UNSIGNED NOT NULL,
            PRIMARY KEY (`object_id`, `term_taxonomy_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `post_revisions` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `post_id` int NOT NULL,
            `content` longtext NOT NULL,
            `title` varchar(255) DEFAULT NULL,
            `revised_by` int NOT NULL,
            `revised_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `note` varchar(255) DEFAULT NULL,
            INDEX `idx_post` (`post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `post_meta` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `post_id` int NOT NULL,
            `meta_key` varchar(255) NOT NULL,
            `meta_value` longtext DEFAULT NULL,
            INDEX `idx_post` (`post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `comments` (
            `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `post_id` bigint UNSIGNED NOT NULL,
            `author_name` varchar(100) NOT NULL,
            `author_email` varchar(100) NOT NULL,
            `content` text NOT NULL,
            `status` enum('approved','pending','spam','trash') DEFAULT 'pending',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `parent_id` int DEFAULT NULL,
            INDEX `post_id` (`post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `media_folders` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) NOT NULL,
            `parent_id` int DEFAULT NULL,
            `created_by` int NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `media_assignments` (
            `file_path` varchar(500) NOT NULL PRIMARY KEY,
            `folder_id` int NOT NULL,
            `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `kanban_boards` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `created_by` int NOT NULL DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `position` int DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `kanban_columns` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `board_id` int NOT NULL,
            `name` varchar(100) NOT NULL,
            `position` int DEFAULT 0,
            `color` varchar(20) DEFAULT '#e2e8f0',
            INDEX `idx_board` (`board_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `kanban_cards` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `column_id` int NOT NULL,
            `title` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `priority` enum('low','medium','high') DEFAULT 'medium',
            `due_date` date DEFAULT NULL,
            `assigned_to` int DEFAULT NULL,
            `position` int DEFAULT 0,
            `post_id` int DEFAULT NULL,
            `form_submission_id` int DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_column` (`column_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `kanban_history` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `board_id` int NOT NULL,
            `card_id` int NOT NULL,
            `card_title` varchar(255) DEFAULT NULL,
            `from_column_name` varchar(100) DEFAULT NULL,
            `to_column_name` varchar(100) DEFAULT NULL,
            `moved_by` int NOT NULL,
            `moved_at` timestamp DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `form_builder` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) NOT NULL,
            `fields_json` text NOT NULL,
            `shortcode` varchar(100) NOT NULL,
            `kanban_board_id` int DEFAULT NULL,
            `kanban_column_id` int DEFAULT NULL,
            `notification_email` varchar(255) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_shortcode` (`shortcode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `form_submissions` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `form_id` int NOT NULL,
            `data_json` text NOT NULL,
            `submitted_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `ip_address` varchar(45) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `menus` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `menu_items` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `menu_id` int NOT NULL,
            `title` varchar(255) DEFAULT NULL,
            `navigation_label` varchar(255) DEFAULT NULL,
            `type` enum('page','post','custom') NOT NULL,
            `object_id` int DEFAULT 0,
            `url` varchar(255) DEFAULT NULL,
            `custom_url` varchar(500) DEFAULT NULL,
            `target` varchar(20) DEFAULT '',
            `position` int DEFAULT 0,
            `parent_id` int DEFAULT 0,
            INDEX `idx_menu` (`menu_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `snippets` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `type` enum('php','html','css','js','universal','post_inline') DEFAULT 'html',
            `content` text NOT NULL,
            `description` text DEFAULT NULL,
            `shortcode` varchar(100) NOT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `site_tags` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) NOT NULL,
            `type` enum('analytics','ads','pixel','custom','verification') NOT NULL DEFAULT 'custom',
            `placement` enum('head','body_open','body_close') NOT NULL DEFAULT 'head',
            `content` text DEFAULT NULL,
            `config` json DEFAULT NULL,
            `status` enum('active','inactive') NOT NULL DEFAULT 'active',
            `priority` int NOT NULL DEFAULT 10,
            `load_condition` enum('all','include','exclude') NOT NULL DEFAULT 'all',
            `condition_type` enum('page','post','category') DEFAULT NULL,
            `condition_ids` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `notifications` (
            `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` int NOT NULL,
            `type` varchar(80) NOT NULL DEFAULT '',
            `message` varchar(500) NOT NULL DEFAULT '',
            `link` varchar(300) NOT NULL DEFAULT '',
            `is_read` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_user_read` (`user_id`, `is_read`),
            INDEX `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `audit_log` (
            `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` int DEFAULT NULL,
            `username` varchar(255) DEFAULT NULL,
            `action` varchar(100) NOT NULL,
            `object_type` varchar(50) NOT NULL DEFAULT '',
            `object_id` int NOT NULL DEFAULT 0,
            `object_title` varchar(500) NOT NULL DEFAULT '',
            `old_value` text DEFAULT NULL,
            `new_value` text DEFAULT NULL,
            `ip` varchar(45) NOT NULL DEFAULT '',
            `user_agent` varchar(500) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_action`  (`action`),
            INDEX `idx_user`    (`user_id`),
            INDEX `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `automations` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) NOT NULL,
            `trigger_event` varchar(64) NOT NULL,
            `trigger_config` json DEFAULT NULL,
            `conditions` json DEFAULT NULL,
            `actions` json NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `automation_logs` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `automation_id` int NOT NULL,
            `trigger_data` json DEFAULT NULL,
            `result` varchar(32) NOT NULL DEFAULT 'ok',
            `message` text DEFAULT NULL,
            `ran_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_auto` (`automation_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `post_relations` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `post_id` int NOT NULL,
            `related_post_id` int NOT NULL,
            `sort_order` int DEFAULT 0,
            INDEX `idx_post` (`post_id`),
            UNIQUE KEY `uq_rel` (`post_id`, `related_post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `sites` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) NOT NULL,
            `slug` varchar(100) NOT NULL UNIQUE,
            `domain` varchar(255) DEFAULT NULL,
            `description` text DEFAULT NULL,
            `status` enum('active','inactive','archived') DEFAULT 'active',
            `admin_user_id` int NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `api_tokens` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `token` varchar(64) NOT NULL UNIQUE,
            `name` varchar(100) NOT NULL,
            `user_id` int NOT NULL,
            `permissions` varchar(255) DEFAULT 'read',
            `last_used_at` datetime DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `redirects` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `source_url` varchar(500) NOT NULL,
            `target_url` varchar(500) NOT NULL,
            `type` enum('301','302') DEFAULT '301',
            `hits` int DEFAULT 0,
            `active` tinyint(1) DEFAULT 1,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_source` (`source_url`(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `daily_visitors` (
            `visit_date` date NOT NULL PRIMARY KEY,
            `visitor_count` int DEFAULT 0,
            `page_views` int DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `page_analytics` (
            `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `page_type` varchar(20) NOT NULL DEFAULT 'post',
            `object_id` int NOT NULL DEFAULT 0,
            `referrer_type` varchar(20) NOT NULL DEFAULT 'direct',
            `device_type` varchar(20) NOT NULL DEFAULT 'desktop',
            `visit_date` date NOT NULL,
            INDEX `idx_date` (`visit_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` int NOT NULL,
            `token` varchar(64) NOT NULL,
            `expires_at` datetime NOT NULL,
            `used` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
}

// ── Insert default data ───────────────────────────────────────────────────────
function insert_defaults(PDO $pdo, string $site_name, string $site_url,
    string $admin_email, string $lang, string $from_name): void
{
    // Roles (only if table is empty)
    $count = $pdo->query("SELECT COUNT(*) FROM user_roles_caps")->fetchColumn();
    if (!$count) {
        $pdo->exec("INSERT INTO user_roles_caps (role_name, capabilities) VALUES
            ('admin',       '{\"manage_options\":1,\"edit_users\":1,\"delete_users\":1,\"promote_users\":1,\"publish_posts\":1,\"edit_posts\":1,\"edit_others_posts\":1,\"read\":1,\"upload_files\":1}'),
            ('editor',      '{\"publish_posts\":1,\"edit_posts\":1,\"edit_others_posts\":1,\"read\":1,\"upload_files\":1}'),
            ('author',      '{\"publish_posts\":1,\"edit_posts\":1,\"read\":1,\"upload_files\":1}'),
            ('subscriber',  '{\"read\":1}')");
    }

    // Default category (only if table is empty)
    $count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if (!$count) {
        $cat_name = $lang === 'id' ? 'Tanpa Kategori' : 'Uncategorized';
        $pdo->prepare("INSERT INTO categories (name, slug, description, parent, count) VALUES (?, 'uncategorized', '', 0, 0)")
            ->execute([$cat_name]);
    }

    // Essential options (use INSERT IGNORE to preserve existing values)
    $opts = [
        ['siteurl',            $site_url],
        ['blogname',           $site_name],
        ['blogdescription',    ''],
        ['admin_email',        $admin_email],
        ['timezone_string',    $lang === 'id' ? 'Asia/Jakarta' : 'UTC'],
        ['date_format',        'F j, Y'],
        ['time_format',        'g:i a'],
        ['upload_path',        'wp-admin/media'],
        ['admin_color_scheme', 'fresh'],
        ['site_favicon',       ''],
        ['site_logo',          ''],
        ['dark_mode_default',  '0'],
        ['posts_per_page',     '10'],
        ['comment_status',     'open'],
        ['smtp_host',          ''],
        ['smtp_port',          '587'],
        ['smtp_user',          ''],
        ['smtp_pass',          ''],
        ['smtp_from',          $admin_email],
        ['smtp_from_name',     $site_name],
        ['smtp_auth',          '1'],
        ['smtp_encryption',    'tls'],
        ['install_lang',       $lang],
        ['cms_version',        '1.0.0'],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO options (option_name, option_value) VALUES (?, ?)");
    foreach ($opts as [$k, $v]) { $stmt->execute([$k, $v]); }
}

function insert_admin_user(PDO $pdo, string $username, string $email, string $password): void
{
    // Only create admin if no users exist yet
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if (!$count) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, email, role, password) VALUES (?, ?, 'admin', ?)")
            ->execute([$username, $email, $hash]);
    }
}

// ── Write wp-config.php ───────────────────────────────────────────────────────
function insert_demo_content(PDO $pdo, string $lang): void
{
    $now = date('Y-m-d H:i:s');

    // Demo categories
    $pdo->exec("INSERT IGNORE INTO categories (name, slug, description) VALUES
        ('Technology', 'technology', 'Posts about technology and software'),
        ('Lifestyle', 'lifestyle', 'Posts about lifestyle and daily life'),
        ('Tutorial', 'tutorial', 'How-to guides and tutorials')");

    // Demo tags
    $pdo->exec("INSERT IGNORE INTO tags (name, slug) VALUES
        ('getting-started', 'getting-started'),
        ('tips', 'tips'),
        ('news', 'news')");

    // Demo posts
    $titles = $lang === 'id' ? [
        ['Selamat Datang di CMS Baru Anda', 'selamat-datang', '<p>Ini adalah post pertama Anda. Edit atau hapus, lalu mulai menulis!</p><p>CMS ini dilengkapi dengan editor Markdown, kalender konten, SEO analysis, dan banyak fitur lainnya.</p>'],
        ['Panduan Memulai', 'panduan-memulai', '<p>Berikut beberapa langkah untuk memulai:</p><ol><li>Buat post atau halaman baru</li><li>Atur menu navigasi di Appearance → Menus</li><li>Konfigurasi pengaturan di Settings</li><li>Undang pengguna lain jika diperlukan</li></ol>'],
        ['Tips Menulis Konten yang Baik', 'tips-menulis-konten', '<p>Konten yang baik adalah konten yang bermanfaat bagi pembaca. Berikut tips singkat:</p><ul><li>Gunakan judul yang menarik</li><li>Tulis paragraf pendek</li><li>Sertakan gambar pendukung</li><li>Optimalkan SEO</li></ul>'],
    ] : [
        ['Welcome to Your New CMS', 'welcome-to-your-new-cms', '<p>This is your first post. Edit or delete it, then start writing!</p><p>This CMS comes with a Markdown editor, content calendar, SEO analysis, and many more features.</p>'],
        ['Getting Started Guide', 'getting-started-guide', '<p>Here are some steps to get started:</p><ol><li>Create a new post or page</li><li>Set up navigation menus in Appearance → Menus</li><li>Configure settings in Settings</li><li>Invite other users if needed</li></ol>'],
        ['Tips for Writing Great Content', 'tips-for-writing-great-content', '<p>Great content is content that provides value to readers. Here are some quick tips:</p><ul><li>Use compelling headlines</li><li>Write short paragraphs</li><li>Include supporting images</li><li>Optimize for SEO</li></ul>'],
    ];

    $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, status, author_id, lang, created_at, updated_at) VALUES (?, ?, ?, 'publish', 1, ?, ?, ?)");
    foreach ($titles as $i => $t) {
        $date = date('Y-m-d H:i:s', strtotime("-{$i} days"));
        $stmt->execute([$t[0], $t[1], $t[2], $lang, $date, $date]);
    }

    // Demo page
    $page_title = $lang === 'id' ? 'Beranda' : 'Home';
    $page_content = $lang === 'id'
        ? '<h1>Selamat Datang</h1><p>Ini adalah halaman beranda Anda. Edit di Page Builder.</p>'
        : '<h1>Welcome</h1><p>This is your home page. Edit it in the Page Builder.</p>';
    $pdo->exec("INSERT IGNORE INTO pages (title, slug, content, builder_type, status) VALUES ('$page_title', 'home', '$page_content', 'grapesjs', 'publish')");
}

function write_wp_config(string $host, string $name, string $user, string $pass): void
{
    $h = addslashes($host);
    $n = addslashes($name);
    $u = addslashes($user);
    $p = addslashes($pass);
    $secret = bin2hex(random_bytes(32));
    $now = date('Y-m-d H:i:s');
    $content = <<<PHP
<?php
/**
 * CMS Configuration
 * Generated by installer on {$now}
 */

// Database
define('DB_HOST', '$h');
define('DB_NAME', '$n');
define('DB_USER', '$u');
define('DB_PASS', '$p');

// Environment: 'production', 'staging', or 'development'
define('WP_ENV', 'production');

// Debug mode (set to true in development for detailed errors)
define('WP_DEBUG', false);

// Auth secret key (used for token signing)
define('AUTH_SECRET_KEY', '$secret');

// Installed flag
define('WP_INSTALLED', true);

PHP;
    file_put_contents(__DIR__ . '/wp-config.php', $content);
}

// ── Auto-detect site URL ──────────────────────────────────────────────────────
$detected_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

// ── Saved DB values (for repopulating form) ───────────────────────────────────
$db = $_SESSION['install_db'] ?? ['host' => 'localhost', 'name' => '', 'user' => 'root', 'pass' => ''];

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($t['welcome']); ?> — CMS Installer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{background:#f0f0f1;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;font-size:14px;color:#1d2327;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:30px 16px}
        .installer-wrap{width:100%;max-width:480px}
        .installer-logo{text-align:center;margin-bottom:24px}
        .installer-logo h1{font-size:26px;font-weight:800;color:#1d2327;letter-spacing:-0.5px}
        .installer-logo p{font-size:13px;color:#646970;margin-top:6px}
        .card{background:#fff;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.1);overflow:hidden}
        .card-header{background:#0073aa;color:#fff;padding:16px 24px;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px}
        .card-header .step-badge{background:rgba(255,255,255,.25);border-radius:20px;padding:2px 10px;font-size:11px;font-weight:600;margin-left:auto}
        .card-body{padding:28px 24px}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-size:13px;font-weight:600;color:#1d2327;margin-bottom:6px}
        .form-group label span{font-size:11px;color:#646970;font-weight:normal;margin-left:4px}
        .form-group input{width:100%;padding:9px 12px;border:1px solid #8c8f94;border-radius:4px;font-size:14px;color:#1d2327;transition:border-color .15s,box-shadow .15s;outline:none}
        .form-group input:focus{border-color:#0073aa;box-shadow:0 0 0 2px rgba(0,115,170,.2)}
        .form-note{font-size:11px;color:#646970;margin-top:4px}
        .btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:4px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;border:none;transition:background .15s}
        .btn-primary{background:#0073aa;color:#fff}
        .btn-primary:hover{background:#005f8a}
        .btn-primary:disabled{background:#8c8f94;cursor:not-allowed}
        .btn-secondary{background:#f0f0f1;color:#1d2327;border:1px solid #c3c4c7}
        .btn-secondary:hover{background:#e0e0e0}
        .btn-block{width:100%;justify-content:center;padding:12px}
        .form-actions{display:flex;justify-content:space-between;align-items:center;margin-top:24px;gap:10px}
        .form-actions-center{justify-content:center}
        .notice{padding:10px 14px;border-radius:4px;font-size:13px;margin-bottom:18px;display:flex;align-items:flex-start;gap:8px}
        .notice-error{background:#fce8e8;color:#c0392b;border:1px solid #f5c6cb}
        .notice-success{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
        .notice-info{background:#e8f0fe;color:#1e40af;border:1px solid #93c5fd}

        /* Language step */
        .lang-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:10px}
        .lang-btn{padding:20px;border:2px solid #e0e0e0;border-radius:8px;background:#fff;cursor:pointer;text-align:center;transition:border-color .15s,box-shadow .15s;display:flex;flex-direction:column;align-items:center;gap:8px}
        .lang-btn:hover{border-color:#0073aa;box-shadow:0 2px 8px rgba(0,115,170,.15)}
        .lang-btn .flag{font-size:36px;line-height:1}
        .lang-btn .lang-name{font-size:14px;font-weight:700;color:#1d2327}
        .lang-btn .lang-sub{font-size:11px;color:#646970}

        /* DB test button */
        .test-row{display:flex;gap:8px;align-items:flex-end}
        .test-row input{flex:1}
        #db-test-result{font-size:12px;margin-top:6px;display:none;padding:6px 10px;border-radius:4px}
        .test-ok{background:#d1fae5;color:#065f46}
        .test-fail{background:#fce8e8;color:#c0392b}

        /* Progress (step 3) */
        .install-log{list-style:none;padding:0;display:flex;flex-direction:column;gap:8px;margin-bottom:20px}
        .install-log li{display:flex;align-items:center;gap:10px;font-size:13px;padding:8px 12px;background:#f8f9fa;border-radius:4px;border:1px solid #e0e0e0}
        .install-log li .icon-ok{color:#00a32a;font-size:15px}
        .done-box{text-align:center;padding:10px 0 4px}
        .done-box h2{font-size:22px;font-weight:800;color:#00a32a;margin-bottom:6px}
        .done-box p{font-size:13px;color:#646970}
        .creds-box{background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;padding:14px 16px;margin:18px 0;font-size:13px}
        .creds-box table{width:100%;border-collapse:collapse}
        .creds-box td{padding:4px 0}
        .creds-box td:first-child{font-weight:600;color:#646970;width:120px}

        /* Steps indicator */
        .steps-bar{display:flex;gap:0;margin-bottom:24px;background:#fff;border-radius:6px;overflow:hidden;border:1px solid #e0e0e0}
        .steps-bar .s{flex:1;padding:9px 6px;text-align:center;font-size:11px;font-weight:600;color:#646970;border-right:1px solid #e0e0e0;position:relative}
        .steps-bar .s:last-child{border-right:none}
        .steps-bar .s.active{background:#0073aa;color:#fff}
        .steps-bar .s.done{background:#d1fae5;color:#065f46}
    </style>
</head>
<body>

<div class="installer-wrap">
    <!-- Logo -->
    <div class="installer-logo">
        <h1><i class="fa-solid fa-cube" style="color:#0073aa;margin-right:6px;"></i>CMS</h1>
        <p><?php echo htmlspecialchars($t['welcome_sub']); ?></p>
    </div>

    <?php if ($step === 0): ?>
    <!-- ── Step 0: Language ──────────────────────────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-globe"></i>
            <?php echo htmlspecialchars($t['choose_lang']); ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="step" value="lang">
                <div class="lang-grid">
                    <button type="submit" name="lang" value="id" class="lang-btn">
                        <span class="flag">🇮🇩</span>
                        <span class="lang-name">Bahasa Indonesia</span>
                        <span class="lang-sub">Indonesian</span>
                    </button>
                    <button type="submit" name="lang" value="en" class="lang-btn">
                        <span class="flag">🇬🇧</span>
                        <span class="lang-name">English</span>
                        <span class="lang-sub">English (US/UK)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php elseif ($step === 'req'): ?>
    <!-- ── Step: System Requirements ─────────────────────────────────────────── -->
    <?php $reqs = check_requirements(); $all_required_pass = true; foreach ($reqs as $r) { if ($r['required'] && !$r['pass']) $all_required_pass = false; } ?>
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-clipboard-check"></i>
            <?php echo $lang === 'id' ? 'Cek Persyaratan Sistem' : 'System Requirements Check'; ?>
        </div>
        <div class="card-body">
            <?php if (!$all_required_pass): ?>
            <div class="notice notice-error" style="margin-bottom:16px;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-top:1px;flex-shrink:0;"></i>
                <span><?php echo $lang === 'id' ? 'Beberapa persyaratan wajib belum terpenuhi. Perbaiki sebelum melanjutkan.' : 'Some required checks failed. Fix them before proceeding.'; ?></span>
            </div>
            <?php else: ?>
            <div class="notice notice-success" style="margin-bottom:16px;">
                <i class="fa-solid fa-circle-check" style="margin-top:1px;flex-shrink:0;"></i>
                <span><?php echo $lang === 'id' ? 'Semua persyaratan terpenuhi!' : 'All requirements passed!'; ?></span>
            </div>
            <?php endif; ?>

            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:2px solid #e0e0e0;">
                        <th style="text-align:left;padding:8px 10px;font-weight:600;"><?php echo $lang === 'id' ? 'Persyaratan' : 'Requirement'; ?></th>
                        <th style="text-align:left;padding:8px 10px;font-weight:600;"><?php echo $lang === 'id' ? 'Nilai' : 'Value'; ?></th>
                        <th style="text-align:center;padding:8px 10px;font-weight:600;">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reqs as $r): ?>
                    <tr style="border-bottom:1px solid #f0f0f1;">
                        <td style="padding:8px 10px;">
                            <?php echo htmlspecialchars($lang === 'id' ? $r['label_id'] : $r['label_en']); ?>
                            <?php if (!$r['required']): ?><small style="color:#646970;margin-left:4px;">(<?php echo $lang === 'id' ? 'opsional' : 'optional'; ?>)</small><?php endif; ?>
                        </td>
                        <td style="padding:8px 10px;font-family:monospace;font-size:12px;"><?php echo htmlspecialchars($r['value']); ?></td>
                        <td style="padding:8px 10px;text-align:center;">
                            <?php if ($r['pass']): ?>
                                <i class="fa-solid fa-circle-check" style="color:#00a32a;font-size:16px;"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-circle-xmark" style="color:<?php echo $r['required'] ? '#d63638' : '#dba617'; ?>;font-size:16px;"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="form-actions" style="margin-top:20px;">
                <a href="install.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> <?php echo $t['back']; ?></a>
                <?php if ($all_required_pass): ?>
                <a href="install.php?step=1" class="btn btn-primary"><?php echo $t['next']; ?> <i class="fa-solid fa-arrow-right"></i></a>
                <?php else: ?>
                <button class="btn btn-primary" disabled style="opacity:.5;cursor:not-allowed;"><?php echo $t['next']; ?> <i class="fa-solid fa-arrow-right"></i></button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php elseif ($step === 1): ?>
    <!-- ── Step 1: Database ─────────────────────────────────────────────────── -->
    <div class="steps-bar">
        <div class="s active"><i class="fa-solid fa-database" style="margin-right:4px;"></i><?php echo $t['step_db']; ?></div>
        <div class="s"><i class="fa-solid fa-gear" style="margin-right:4px;"></i><?php echo $t['step_site']; ?></div>
    </div>
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-database"></i>
            <?php echo htmlspecialchars($t['step_db']); ?>
            <span class="step-badge"><?php echo $t['step1_of_2']; ?></span>
        </div>
        <div class="card-body">
            <div class="notice notice-info">
                <i class="fa-solid fa-circle-info" style="margin-top:1px;flex-shrink:0;"></i>
                <span><?php echo htmlspecialchars($t['db_note']); ?></span>
            </div>

            <form method="POST" id="form-db">
                <input type="hidden" name="step" value="1">

                <div class="form-group">
                    <label><?php echo $t['db_host']; ?></label>
                    <input type="text" name="db_host" id="db_host" value="<?php echo htmlspecialchars($db['host']); ?>" placeholder="<?php echo $t['db_host_ph']; ?>" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['db_name']; ?> <span>— <?php echo $t['db_name_note']; ?></span></label>
                    <input type="text" name="db_name" id="db_name" value="<?php echo htmlspecialchars($db['name']); ?>" placeholder="cms_db" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['db_user']; ?></label>
                    <input type="text" name="db_user" id="db_user" value="<?php echo htmlspecialchars($db['user']); ?>" placeholder="root" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['db_pass']; ?></label>
                    <input type="password" name="db_pass" id="db_pass" value="" autocomplete="off">
                </div>

                <div id="db-test-result"></div>

                <div class="form-actions">
                    <button type="button" id="btn-test" class="btn btn-secondary" onclick="testDB()">
                        <i class="fa-solid fa-plug"></i>
                        <?php echo $t['db_test']; ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $t['next']; ?>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php elseif ($step === 2): ?>
    <!-- ── Step 2: Site info ────────────────────────────────────────────────── -->
    <div class="steps-bar">
        <div class="s done"><i class="fa-solid fa-check" style="margin-right:4px;"></i><?php echo $t['step_db']; ?></div>
        <div class="s active"><i class="fa-solid fa-gear" style="margin-right:4px;"></i><?php echo $t['step_site']; ?></div>
    </div>
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-gear"></i>
            <?php echo htmlspecialchars($t['step_site']); ?>
            <span class="step-badge"><?php echo $t['step2_of_2']; ?></span>
        </div>
        <div class="card-body">
            <?php if ($install_error): ?>
            <div class="notice notice-error">
                <i class="fa-solid fa-triangle-exclamation" style="flex-shrink:0;margin-top:1px;"></i>
                <span><?php echo htmlspecialchars($install_error); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="step" value="2">

                <div class="form-group">
                    <label><?php echo $t['site_name']; ?></label>
                    <input type="text" name="site_name" value="<?php echo htmlspecialchars($_POST['site_name'] ?? ''); ?>" placeholder="My CMS" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['site_url']; ?></label>
                    <input type="url" name="site_url" value="<?php echo htmlspecialchars($_POST['site_url'] ?? $detected_url); ?>" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['admin_user']; ?></label>
                    <input type="text" name="admin_user" value="<?php echo htmlspecialchars($_POST['admin_user'] ?? 'admin'); ?>" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['admin_email']; ?></label>
                    <input type="email" name="admin_email" value="<?php echo htmlspecialchars($_POST['admin_email'] ?? ''); ?>" autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['admin_pass']; ?></label>
                    <input type="password" name="admin_pass" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['admin_pass2']; ?></label>
                    <input type="password" name="admin_pass2" autocomplete="new-password" required>
                </div>

                <div style="margin:18px 0 6px;padding:14px 16px;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600;color:#1d2327;">
                        <input type="checkbox" name="install_demo" value="1" checked style="width:16px;height:16px;">
                        <i class="fa-solid fa-box-open" style="color:#0073aa;"></i>
                        <?php echo $t['demo_label']; ?>
                    </label>
                    <p style="margin:6px 0 0 24px;font-size:12px;color:#646970;"><?php echo $t['demo_desc']; ?></p>
                </div>

                <div class="form-actions">
                    <a href="install.php?step=1" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i>
                        <?php echo $t['back']; ?>
                    </a>
                    <button type="submit" class="btn btn-primary" id="btn-install">
                        <i class="fa-solid fa-rocket"></i>
                        <?php echo $t['install_btn']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php elseif ($step === 3 && $install_result): ?>
    <!-- ── Step 3: Done ─────────────────────────────────────────────────────── -->
    <?php if (isset($install_result['error'])): ?>
    <div class="card">
        <div class="card-header" style="background:#d63638;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?php echo $lang === 'id' ? 'Instalasi Gagal' : 'Installation Failed'; ?>
        </div>
        <div class="card-body">
            <div class="notice notice-error">
                <i class="fa-solid fa-triangle-exclamation" style="flex-shrink:0;"></i>
                <span><?php echo htmlspecialchars($install_result['error']); ?></span>
            </div>
            <a href="install.php?step=1" class="btn btn-secondary"><?php echo $t['back']; ?></a>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-header" style="background:#00a32a;">
            <i class="fa-solid fa-circle-check"></i>
            <?php echo htmlspecialchars($t['step_done']); ?>
        </div>
        <div class="card-body">
            <div class="done-box">
                <h2><i class="fa-solid fa-circle-check" style="margin-right:6px;"></i><?php echo $t['done_title']; ?></h2>
                <p><?php echo $t['done_sub']; ?></p>
            </div>

            <ul class="install-log">
                <?php foreach ($install_result['log'] as $key): ?>
                <li>
                    <i class="fa-solid fa-check icon-ok"></i>
                    <?php echo htmlspecialchars($t[$key] ?? $key); ?>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="creds-box">
                <table>
                    <tr>
                        <td><?php echo $t['done_user']; ?></td>
                        <td><strong><?php echo htmlspecialchars($install_result['admin_user']); ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php echo $t['done_pass']; ?></td>
                        <td><strong><?php echo $lang === 'id' ? 'Password yang Anda buat' : 'The password you set'; ?></strong></td>
                    </tr>
                </table>
            </div>

            <a href="login.php" class="btn btn-primary btn-block">
                <i class="fa-solid fa-right-to-bracket"></i>
                <?php echo $t['done_login']; ?>
            </a>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

</div><!-- /installer-wrap -->

<script>
function testDB() {
    var host = document.getElementById('db_host').value;
    var name = document.getElementById('db_name').value;
    var user = document.getElementById('db_user').value;
    var pass = document.getElementById('db_pass').value;
    var btn  = document.getElementById('btn-test');
    var res  = document.getElementById('db-test-result');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <?php echo $t['installing']; ?>';
    res.style.display = 'none';
    fetch('install.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=test_db&host=' + encodeURIComponent(host)
            + '&name=' + encodeURIComponent(name)
            + '&user=' + encodeURIComponent(user)
            + '&pass=' + encodeURIComponent(pass)
    })
    .then(r => r.json())
    .then(d => {
        res.style.display = 'block';
        if (d.ok) {
            res.className = 'test-ok';
            res.innerHTML = '<i class="fa-solid fa-circle-check"></i> <?php echo $t['db_ok']; ?>';
        } else {
            res.className = 'test-fail';
            res.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> <?php echo $t['db_fail']; ?>: ' + (d.error || '');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-plug"></i> <?php echo $t['db_test']; ?>';
    })
    .catch(() => {
        res.style.display = 'block';
        res.className = 'test-fail';
        res.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> <?php echo $t['db_fail']; ?>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-plug"></i> <?php echo $t['db_test']; ?>';
    });
}
</script>
</body>
</html>
