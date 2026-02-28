<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Optional: Create a fresh session just to carry a message
session_start();
$_SESSION['logout_message'] = "You have been logged out successfully.";

// Redirect to login page
header("Location: login.php");
exit();
?>
