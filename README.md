# Secure-File-Vault
A Secure File Vault is a web application that allows users to upload, store, and download files securely using end-to-end encryption (E2EE).

Instructions to run thise Project:
To implement thise project, you have to install Xampp Local Server.
After installing and setup, paste this folder inside Xampp such that [C:\xampp\htdocs\secure_file_vault].
Start Apache and MySQL from Xampp control Panel.
In your browser opean "http://localhost/phpmyadmin/".
To create batabase copy this query and paste it in phpmyadmin->sql: 
"CREATE DATABASE secure_vault;"

USE secure_vault;

"CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    encryption_key VARCHAR(512) NOT NULL
);
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);"
