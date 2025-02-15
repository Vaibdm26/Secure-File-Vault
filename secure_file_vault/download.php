<?php
function decryptFile($source, $dest, $key) {
    $data = file_get_contents($source);
    $iv = substr($data, 0, 16);
    $data = substr($data, 16);
    $decrypted = openssl_decrypt($data, "aes-256-cbc", $key, 0, $iv);
    file_put_contents($dest, $decrypted);
}

$file_id = $_GET['file_id'];
$stmt = $conn->prepare("SELECT file_path, file_name FROM files WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$stmt->bind_result($file_path, $file_name);
$stmt->fetch();

$temp_path = "temp/" . $file_name;
decryptFile($file_path, $temp_path, $encryption_key);

header("Content-Disposition: attachment; filename=$file_name");
readfile($temp_path);
unlink($temp_path);

?>