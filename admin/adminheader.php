<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to set active class for the current page
function setActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'class="active"' : '';
}

// Inactivity timeout (in seconds)
define('INACTIVITY_TIMEOUT', 300); // 5 minutes

// Check if the admin is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login with an error message
    header("Location: ../admin.php?error=unauthorized_access");
    exit();
}

// Check last activity time for session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > INACTIVITY_TIMEOUT) {
    // Log out the admin if inactivity timeout is reached
    session_unset();
    session_destroy();
    header("Location: ../admin.php?timeout=1");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>

<section id="header">
    <link rel="stylesheet" href="css/adminstyle.css" />
    <a href="adminpanel.php"><img src="../img/logoshop1.png" class="logo" alt="Logo" /></a>
    <div>
        <ul id="navbar">
            <li><a href="adminpanel.php" <?php echo setActive('adminpanel.php'); ?>>Dashboard</a></li>
            <li><a href="inventory.php" <?php echo setActive('inventory.php'); ?>>Manage Products</a></li>
            <li><a href="manageorder.php" <?php echo setActive('manageorder.php'); ?>>Manage Orders</a></li>
            <li><a href="managereward.php" <?php echo setActive('managereward.php'); ?>>Manage Rewards</a></li>
            <li><a href="manageuser.php" <?php echo setActive('manageuser.php'); ?>>Manage Users</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
</section>

<script>
// Reset session inactivity timer on user activity
let timeoutDuration = <?php echo INACTIVITY_TIMEOUT * 1000; ?>; // Convert seconds to milliseconds
let inactivityTimer;

function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(function () {
        // Redirect to logout.php if inactive
        window.location.href = "../logout.php?inactive=1";
    }, timeoutDuration);
}

// Reset timer on user interaction
window.onload = resetInactivityTimer;
document.onmousemove = resetInactivityTimer;
document.onkeypress = resetInactivityTimer;
</script>
