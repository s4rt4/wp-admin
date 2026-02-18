<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/db_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' &lsaquo; ' . htmlspecialchars(get_option('site_title', get_option('blogname', 'Admin'))) : 'Admin Dashboard'; ?></title>
<?php
$_fav = get_option('site_favicon', '');
if ($_fav): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($_fav); ?>">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($_fav); ?>">
<?php endif; ?>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="colors.css">
</head>
<?php
// Get User Color Scheme, default to 'fresh'
$admin_color = get_option('admin_color_scheme', 'fresh');
?>
<body class="wp-admin admin-color-<?php echo htmlspecialchars($admin_color); ?>">
    <!-- Top Admin Bar (Simplified) -->
    <?php require_once 'topbar.php'; ?>

    <div class="content-wrapper">
