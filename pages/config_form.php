<?php
include '../includes/_init.php'; // this loads your db + helper functions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_key = $_POST['api_key'];

    // Use a strong random IV
    $iv = openssl_random_pseudo_bytes(16);

    // Encryption key (store securely, not in DB)
    $secret = "my_super_secret_encryption_key";

    // Encrypt API key
    $encrypted_key = openssl_encrypt($api_key, 'AES-128-CTR', $secret, 0, $iv);

    // Convert IV to base64 for storage
    $iv_base64 = base64_encode($iv);

    // Insert or update record using your insert_data() function
    $data = [
        'config_key' => 'API_KEY',
        'config_value' => $encrypted_key,
        'iv_value' => $iv_base64
    ];

    // insert_data(table, data, duplicate_update=true)
    insert_data('system_config', $data, true);

    echo "<p style='color:green;'>API Key saved successfully!</p>";
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-4 offset-4 py-5">
                <form method="POST">
                    <label class="form-label">Enter API Key:</label><br>
                    <input class="form-control" type="password" name="api_key" required style="width:300px;">
                    <button class="btn btn-primary mt-2" type="submit">Save</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
