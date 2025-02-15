<?php
function encryptFile($source, $dest, $key) {
    $iv = openssl_random_pseudo_bytes(16);
    $data = file_get_contents($source);
    $encrypted = openssl_encrypt($data, "aes-256-cbc", $key, 0, $iv);
    file_put_contents($dest, $iv . $encrypted);
}

$file_name = $_FILES['file']['name'];
$file_tmp = $_FILES['file']['tmp_name'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT encryption_key FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($encryption_key);
$stmt->fetch();

$encrypted_path = "uploads/" . bin2hex(random_bytes(10)) . ".enc";
encryptFile($file_tmp, $encrypted_path, $encryption_key);

$stmt = $conn->prepare("INSERT INTO files (user_id, file_name, file_path) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $file_name, $encrypted_path);
$stmt->execute();

?>