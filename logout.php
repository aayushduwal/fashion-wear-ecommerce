<?php
session_start();

// Store the user type before destroying the session
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Destroy the session to log the user out
session_unset();
session_destroy();

// Redirect based on stored user type
if ($is_admin) {
    header("Location: /fashionwear/dashboard/login.php"); // Admin login page
} else {
    header("Location: login.php"); // User login page
}
exit();
?>