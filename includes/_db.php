<?php
// includes/_db.php

define('Config::DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fsed');

// Create connection
define("CONN", mysqli_connect(Config::DB_HOST, DB_USER, DB_PASS, DB_NAME));

// Check connection
if (!CONN) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
