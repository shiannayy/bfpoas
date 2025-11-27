<?php
session_start();

// Clear all session data
unset($_SESSION);
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to home (public landing page)
header("Location: ../public/index.php?logout=success");
exit;
