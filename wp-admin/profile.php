<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Redirect to edit user page for the current user
header("Location: user-new.php?id=" . $_SESSION['user_id']);
exit();
?>
