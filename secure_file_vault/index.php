<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "secure_vault";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Register User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if user already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $register_error = "Username already exists!";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $encryption_key = bin2hex(random_bytes(16));

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, encryption_key) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $encryption_key);
        if ($stmt->execute()) {
            $register_success = "User registered successfully! Please log in.";
        } else {
            $register_error = "Failed to register user.";
        }
        $stmt->close();
    }
    $checkStmt->close();
}

// Login User
include "db.php";  // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password_hash);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            $_SESSION["username"] = $username;
            $_SESSION["user_id"] = $id;
            
            //  NO OUTPUT BEFORE THIS LINE 
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Invalid password";
        }
    } else {
        echo "User not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure File Vault - Login/Register</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background-color: #f4f4f4; }
        .container { width: 300px; margin: 50px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        input { width: 100%; padding: 8px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { width: 100%; padding: 8px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if(isset($register_success)) echo "<p class='success'>$register_success</p>"; ?>
        <?php if(isset($register_error)) echo "<p class='error'>$register_error</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Enter username" required>
            <input type="password" name="password" placeholder="Enter password" required>
            <button type="submit" name="register">Register</button>
        </form>
    </div>

    <div class="container">
        <h2>Login</h2>
        <?php if(isset($login_error)) echo "<p class='error'>$login_error</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Enter username" required>
            <input type="password" name="password" placeholder="Enter password" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
