<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'helapesa';

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!function_exists('getSiteSetting')) {
    function getSiteSetting($conn, $key) {
        // Check if table exists first prevents crash
        $check = $conn->query("SHOW TABLES LIKE 'site_settings'");
        if($check->num_rows == 0) return '';
        
        $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_assoc()) {
            return $row['setting_value'];
        }
        return '';
    }
}