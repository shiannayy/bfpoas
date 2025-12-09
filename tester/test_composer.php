<?php
echo "Testing Composer...<br>";

// Try to load autoloader
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
    echo "✓ vendor/autoload.php exists<br>";
    
    // Test if PHPMailer classes are available
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✓ PHPMailer is loaded via Composer<br>";
        echo "✓ Everything is working!";
    } else {
        echo "✗ PHPMailer class not found<br>";
    }
} else {
    echo "✗ vendor/autoload.php NOT found<br>";
    echo "Path checked: " . __DIR__ . '/vendor/autoload.php';
}
?>