<?php
// Database/db_conn.php

$host = 'localhost'; // or your server IP
$dbname = 'pocketSLC';
$db_username = 'root';
$db_password = '';

try {
    // Data Source Name (DSN) string
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    // Create the PDO connection instance
    $conn = new PDO($dsn, $db_username, $db_password);
    
    // Set PDO attributes for better error handling and security
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Throws exceptions on errors
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Disables emulation for better security

} catch (PDOException $e) {
    // Log the error securely and display a generic error message
    error_log("Database connection error: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}
?>