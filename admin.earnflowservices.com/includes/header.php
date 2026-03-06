<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  $_SESSION['login_error'] = 'Please login to access the dashboard';
    // Not logged in — redirect to login page
    header("Location: ../index.php");
    exit();
}

// Fetch Theme Color from Settings
$themeColor = getSiteSetting($conn, 'theme_color');
if (!$themeColor) $themeColor = 'black'; // Default

// Define theme-specific styles
$bgColor = '#f8f9fa';
$cardBg = '#ffffff';
$textColor = '#212529';

// Keep every theme on the same darker-bottom-left to lighter-top-right diagonal.
switch ($themeColor) {
    case 'darkblue':
        $primaryGradient = 'linear-gradient(45deg, #1a2035 0%, #323a54 100%)';
        break;
    case 'green':
        $primaryGradient = 'linear-gradient(45deg, #43A047 0%, #66BB6A 100%)';
        break;
    case 'purple':
        $primaryGradient = 'linear-gradient(45deg, #D81B60 0%, #EC407A 100%)';
        break;
    case 'red':
        $primaryGradient = 'linear-gradient(45deg, #E53935 0%, #EF5350 100%)';
        break;
    case 'black':
    default:
        $primaryGradient = 'linear-gradient(45deg, #191919 0%, #42424a 100%)';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= getSiteSetting($conn, 'site_name') ? htmlspecialchars(getSiteSetting($conn, 'site_name')) : 'Helapesa Dashboard' ?></title>
  <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/png">

  <!-- Google Fonts and Material Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <!-- Custom Dashboard Styles -->
  <link rel="stylesheet" href="../assets/css/material-dashboard.css" />

  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

<!-- AlertifyJS CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

<!-- AlertifyJS JS -->
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<!-- Toastify CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<!-- Toastify JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<!-- Add this to the head section -->
<style>
:root {
    --primary-gradient: <?= $primaryGradient ?>;
    --theme-bg: <?= $bgColor ?>;
    --theme-card-bg: <?= $cardBg ?>;
    --theme-text: <?= $textColor ?>;
}

/* Improved sidebar animation */
#sidenav-main {
  background-color: #ffffff !important;
  transition: transform 0.3s ease-out;
}

@media (max-width: 991.98px) {
  #sidenav-main {
    transform: translateX(-100%);
    z-index: 9999;
  }
  
  #sidenav-main.sidebar-open {
    transform: translateX(0);
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
  }
  
  .overflow-hidden {
    overflow: hidden;
  }
}

/* Sidebar Active Link */
#sidenav-main .nav-link.active {
  background-image: var(--primary-gradient);
  color: #fff !important;
}

#sidenav-main .nav-link.bg-gradient-dark,
#sidenav-main .nav-link.bg-gradient-dark .nav-link-text,
#sidenav-main .nav-link.bg-gradient-dark .material-symbols-rounded {
  color: #fff !important;
}

#sidenav-main .nav-link {
  color: var(--theme-text) !important;
}
#sidenav-main .nav-link:hover {
  color: #000;
  background-color: #f5f5f5;
}

/* Button & Icon Styling */
.btn.bg-gradient-dark, .bg-dark, .btn-dark, .icon, .bg-gradient-dark {
  background-image: var(--primary-gradient) !important;
  color: #fff !important;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
  border: none !important;
}

/* Global Body Styling */
body {
  background-color: var(--theme-bg) !important;
  color: var(--theme-text) !important;
}

.navbar, header.navbar-main {
  background-color: #f5f5f5 !important;
  color: var(--theme-text) !important;
}

.card, .card-header {
  background-color: var(--theme-card-bg) !important;
  color: var(--theme-text) !important;
}

.card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05) !important;
  border: 1px solid rgba(0,0,0,0.05) !important;
  border-radius: 12px !important;
}

.bg-gray-100 {
  background-color: #f8f9fa !important;
}

.text-primary {
  color: <?= ($themeColor === 'black') ? '#000' : 'var(--theme-text)' ?> !important;
  text-decoration: none;
}
.text-primary:hover {
  text-decoration: underline;
}

h1, h2, h3, h4, h5, h6, label, p, ::placeholder, .font-weight-bold {
  color: var(--theme-text) !important;
}

td, th {
  color: #212529 !important;
}

input.form-control {
    background-color: #ffffff !important;
    color: var(--theme-text) !important;
    border-color: #d2d6da !important;
}
</style>

</head>

<body class="g-sidenav-show bg-gray-100">

<?php include 'navbar.php'; 
include 'sidebar.php'
?>


<main class="main-content position-relative border-radius-lg">
