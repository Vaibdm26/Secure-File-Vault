<?php
function decryptFile($source, $dest, $key) {
    $data = file_get_contents($source);
    $iv = substr($data, 0, 16); // Extract IV
    $data = substr($data, 16);
    $decrypted = openssl_decrypt($data, "aes-256-cbc", $key, 0, $iv);

    file_put_contents($dest, $decrypted);
}

// Example usage
if (isset($_GET['file_id'])) {
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

    // Fetch file path
    $file_id = $_GET['file_id'];
    $stmt = $conn->prepare("SELECT file_path, file_name FROM files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->bind_result($file_path, $file_name);
    $stmt->fetch();
    $stmt->close();

    // Decrypt the file
    $temp_path = "temp/" . $file_name;
    decryptFile($file_path, $temp_path, $encryption_key);

    // Serve file
    header("Content-Disposition: attachment; filename=$file_name");
    readfile($temp_path);
    unlink($temp_path);
}

if (isset($_GET["file"])) {
    $filePath = "uploads/" . $_GET["file"];
    $metaPath = $filePath . ".meta";

    if (file_exists($filePath) && file_exists($metaPath)) {
        $encryptionKey = "your-secret-key"; // Same key as in encrypt.php
        $encryptedData = file_get_contents($filePath);
        $originalFileName = file_get_contents($metaPath);

        // Decrypt file
        $decryptedData = openssl_decrypt($encryptedData, "AES-256-CBC", $encryptionKey, 0, str_repeat("0", 16));

        // Send file for download
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $originalFileName);
        echo $decryptedData;
        exit();
    } else {
        echo "File not found!";
    }
}

?>
