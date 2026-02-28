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

  <!-- Font Awesome (if you used it) -->
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
/* Improved sidebar animation */
#sidenav-main {
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
.btn.bg-gradient-dark, .bg-dark, .btn-dark, .icon, .bg-gradient-dark, {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3)
}

.text-primary {
  color: #000; /* Bootstrap primary color */
  text-decoration: none;
}
.text-primary:hover {
  text-decoration: underline;
}
</style>

</head>

<body class="g-sidenav-show bg-gray-100">

<?php include 'navbar.php'; 
include 'sidebar.php'
?>


<main class="main-content position-relative border-radius-lg">