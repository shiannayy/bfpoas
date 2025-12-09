<?php
include '../includes/_init.php'; // this loads your db + helper functions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
            $key = $_POST['key'];
            $configKey = $_POST['configKey'];

    // Use a strong random IV
    $iv = openssl_random_pseudo_bytes(16);

    // Encryption key (store securely, not in DB)
    $secret = config::APP_SECRET;

    // Encrypt API key
    $encrypted_key = openssl_encrypt($key, 'AES-128-CTR', $secret, 0, $iv);

    // Convert IV to base64 for storage
    $iv_base64 = base64_encode($iv);

    // Insert or update record using your insert_data() function
    $data = [
        'config_key' => $configKey,
        'config_value' => $encrypted_key,
        'iv_value' => $iv_base64
    ];
    $exists = select('system_config', ['config_key' => $configKey]);
    if ($exists) {
        // If record exists, update it
        $data['created_at'] = date('Y-m-d H:i:s');
        update_data('system_config', $data, ['config_key' => $configKey]);
    } else {
        // If record does not exist, set created_at
        $data['created_at'] = date('Y-m-d H:i:s');
        insert_data('system_config', $data);
    }
    

    echo "<p style='color:green;'>Secret saved successfully!</p>";
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
                <h3>Setup API Key</h3>
                <form method="POST">
                    <select name="configKey" id="" class="form-select mb-2" required>
                        <option value="API_KEY">for API_KEY</option>
                        <option value="EMAIL_PASS">for EMAIL_PASS</option>
                    </select>
                    <br>
                    <label class="form-label">Enter Secret Code to Configure:</label><br>
                    <input class="form-control" type="password" name="key" required style="width:300px;">
                    <button class="btn btn-primary mt-2" type="submit">Save</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
