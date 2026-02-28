<?php
// config.php

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'helapesa';

// Base URL configuration
$base_url = 'http://localhost/helapesa/user'; // Local URL since we are in XAMPP
// Note: If deploying to live, revert to https://earnflowservices.com/earnflow

// Create database connection
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get site settings
if (!function_exists('getSiteSetting')) {
    function getSiteSetting($conn, $key) {
        $stmt = $conn->prepare("SHOW TABLES LIKE 'site_settings'");
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows == 0) return ''; // Table not found
        $stmt->close();
        
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

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Package definitions
$packages = [
    'basic' => ['price' => 1300, 'commissions' => [1 => 600]],
    'silver' => ['price' => 2000, 'commissions' => [1 => 500, 2 => 200]],
    'gold' => ['price' => 3000, 'commissions' => [1 => 1200, 2 => 500]],
    'premium' => ['price' => 4000, 'commissions' => [1 => 1300, 2 => 700]],
];

// Consolidated getCurrentUser function
function getCurrentUser($conn, $userId = null) {
    if ($userId === null) {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        $userId = $_SESSION['user_id'];
    }
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get dashboard data
function getDashboardData($conn, $user_id) {
    $data = [
        'current_balance' => 0.00,
        'total_earned' => 0.00,
        'total_withdrawn' => 0.00,
        'referral_count' => 0,
        'referral_bonus' => 0.00,
        'earning_trend' => [],
        'referral_trend' => [],
        'leaderboard' => []
    ];

    if (!$user_id) return $data;

    try {
        // Current balance
        $stmt = $conn->prepare("SELECT commission AS current_balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows) {
            $row = $result->fetch_assoc();
            $data['current_balance'] = (float)$row['current_balance'];
        }

        // Total earned
        $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE user_id = ? AND type = 'earn'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $data['total_earned'] = (float)($row['total'] ?? 0);
        }

        // Total withdrawn
        $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE user_id = ? AND type = 'withdraw'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $data['total_withdrawn'] = (float)($row['total'] ?? 0);
        }

        // Referral count
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM referals WHERE referrer_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $data['referral_count'] = (int)($row['count'] ?? 0);
        }

        // Referral Bonus Sum
        $stmt = $conn->prepare("SELECT SUM(bonus_amount) AS total FROM referals WHERE referrer_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $data['referral_bonus'] = (float)($row['total'] ?? 0);
        }

        // Earning trend (Last 7 days)
        $stmt = $conn->prepare("
            SELECT DATE(created_at) AS date, SUM(amount) AS total 
            FROM transactions  
            WHERE user_id = ? 
            GROUP BY DATE(created_at) 
            ORDER BY DATE(created_at) DESC 
            LIMIT 7
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data['earning_trend'][] = [
                'date' => $row['date'],
                'total' => (float)$row['total']
            ];
        }

        // Referral trend (Last 7 days)
        $stmt = $conn->prepare("
            SELECT DATE(created_at) AS date, COUNT(*) AS count 
            FROM referals 
            WHERE referrer_id = ? 
            GROUP BY DATE(created_at) 
            ORDER BY DATE(created_at) DESC 
            LIMIT 7
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data['referral_trend'][] = [
                'date' => $row['date'],
                'count' => (int)$row['count']
            ];
        }

        // Leaderboard
        $stmt = $conn->prepare("
            SELECT u.username, SUM(t.amount) AS total 
            FROM transactions t
            JOIN users u ON t.user_id = u.id 
            WHERE t.type = 'earn'
            GROUP BY t.user_id 
            ORDER BY total DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data['leaderboard'][] = [
                'username' => $row['username'],
                'total' => (float)$row['total']
            ];
        }
    } catch (Exception $e) {
        error_log("Dashboard data error: " . $e->getMessage());
    }

    return $data;
}

// Get referral data
function getReferralData($conn, $user_id) {
    global $base_url;
    
    $data = [
        'referral_link' => '',
        'account_balance' => 0.00,
        'referred_users' => []
    ];

    if (!$user_id) return $data;

    try {
        // Get username
        $user = getCurrentUser($conn, $user_id);
        $username = $user['username'] ?? '';
        $data['referral_link'] = $base_url . '/register.php?ref=' . urlencode($username);

        // Account balance
        $stmt = $conn->prepare("SELECT commission FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows) {
            $row = $result->fetch_assoc();
            $data['account_balance'] = (float)($row['commission'] ?? 0.00);
        }

        // Referred users
        $stmt = $conn->prepare("
            SELECT u.username, u.email, u.phone, u.created_on, r.bonus_amount 
            FROM referals r
            JOIN users u ON r.referred_id = u.id
            WHERE r.referrer_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data['referred_users'][] = $row;
        }
    } catch (Exception $e) {
        error_log("Referral data error: " . $e->getMessage());
    }

    return $data;
}

// Helper function for credential checks
function isDuplicateCredential($conn, $field, $value, $userId) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE $field = ? AND id != ?");
    $stmt->bind_param("si", $value, $userId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}
?>