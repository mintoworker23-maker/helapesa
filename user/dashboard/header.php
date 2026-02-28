<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../phpscripts/config.php';

// Redirect unauthorized users
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Optional: Fetch current user if needed
$user = getCurrentUser($conn, $_SESSION['user_id']);
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
<script src="../assets/js/material-dashboard.min.js"></script>

<!-- Add this to the head section -->
<style>
/* Improved sidebar animation */
#sidenav-main {
  background-color: #ffffff !important; /* white for light mode */
}

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
.logo-light {
  display: none !important;
}
.logo-dark {
  display: inline !important;
}

body[data-theme='dark'] .logo-light {
  display: inline !important;
}
body[data-theme='dark'] .logo-dark {
  display: none !important;
}
/* Light mode */
#sidenav-main .nav-link.active {
  background-image: linear-gradient(195deg, #42424a, #191919);
  color: white !important;
}

/* Dark mode */
body[data-theme='dark'] #sidenav-main .nav-link.active {
  background-image: linear-gradient(195deg, #323a54, #1a2035);
  color: white !important;
}
#sidenav-main .nav-link {
  color: #6c757d;
}
#sidenav-main .nav-link:hover {
  color: #000;
  background-color: #f5f5f5;
}

/* Dark mode override */
body[data-theme='dark'] #sidenav-main .nav-link {
  color: #ddd;
}
body[data-theme='dark'] #sidenav-main .nav-link:hover {
  color: #fff;
  background-color: #2c2c3c;
}
/* Default button */
.btn.bg-gradient-dark {
  background-image: linear-gradient(195deg, #42424a, #191919);
  color: #fff;
}

/* In dark mode */
body[data-theme='dark'] .btn.bg-gradient-dark {
  background-image: linear-gradient(195deg, #323a54, #1a2035);
  color: #fff;
}

body[data-theme='dark'] .btn.btn-outline-dark {
  border-color: #aaa;
  color: #ddd;
}
.footer {
  z-index: 1;
  position: relative;
}
body[data-theme='dark'] #sidenav-main {
  background-color: #1a2035 !important; /* dark sidebar */
}
/* Light mode (already default) */
body {
  background-color: #ffffff;
  color: #212529;
}

/* Dark mode body + all page text */
body[data-theme='dark'] {
  background-color: #1a2035 !important;
  color: #f1f1f1 !important;
}
.navbar, header.navbar-main {
  background-color: #ffffff;
  color: #212529;
}

body[data-theme='dark'] .navbar,
body[data-theme='dark'] header.navbar-main {
  background-color: #1a2035 !important;
  color: #f1f1f1 !important;
  border-bottom: 1px solid #333;
}
.footer {
  background-color: #ffffff;
  color: #212529;
}

body[data-theme='dark'] .footer {
  background-color: #1a2035 !important;
  color: #f1f1f1 !important;
  border-top: 1px solid #333;
}
body[data-theme='dark'] .card,
body[data-theme='dark'] .main-content,
body[data-theme='dark'] .container {
  background-color: #1e1e2f !important;
  color: #f1f1f1 !important;
}
body[data-theme='dark'] .bg-gray-100 {
  background-color: #121212 !important;
}

body[data-theme='dark'] .text-muted {
  color: #aaa !important;
}

</style>

</head>

<body class="g-sidenav-show bg-gray-100" data-theme="light">

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="main-content position-relative border-radius-lg" style="margin-left: 250px;">