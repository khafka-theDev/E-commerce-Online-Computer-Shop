<?php
include("include/connect.php");
session_start();

// Check if the session is active
if (session_status() === PHP_SESSION_ACTIVE) {
    // Clear all session variables
    $_SESSION = array();

    // Expire the session cookie if applicable
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/'); // Expire the cookie
    }

    // Destroy the session
    session_destroy();

    // Check if user was logged in as admin
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        // Redirect to admin login page
        header("Location: admin.php?message=logout_successful");
    } else {
        // Redirect to user login page
        header("Location: login.php?message=logout_successful");
    }
    exit();
} else {
    // If no session is active, redirect with a session expired message
    header("Location: login.php?message=session_expired");
    exit();
}
?>
