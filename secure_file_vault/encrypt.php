<?php
function encryptFile($source, $dest, $key) {
    $iv = openssl_random_pseudo_bytes(16); // Generate IV
    $data = file_get_contents($source);
    $encrypted = openssl_encrypt($data, "aes-256-cbc", $key, 0, $iv);

    file_put_contents($dest, $iv . $encrypted); // Store IV with encrypted data
}

// Example usage
if (isset($_FILES['file'])) {
    require 'db.php';
    session_start();
    $user_id = $_SESSION['user_id'];

    // Fetch user encryption key
    $stmt = $conn->prepare("SELECT encryption_key FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($encryption_key);
    $stmt->fetch();
    $stmt->close();

    // Encrypt file
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $encrypted_path = "C:xampp\htdocs\secure_file_vault\upload.php" . bin2hex(random_bytes(10)) . ".enc";

    encryptFile($file_tmp, $encrypted_path, $encryption_key);

    // Store metadata
    $stmt = $conn->prepare("INSERT INTO files (user_id, file_name, file_path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $file_name, $encrypted_path);
    $stmt->execute();

    echo "File encrypted and uploaded successfully!";
}
?>
