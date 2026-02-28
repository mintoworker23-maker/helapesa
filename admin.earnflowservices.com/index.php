<?php
session_start();

// Fetch error then unset
$errorMsg = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Earnflow Admin Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

  <!-- Styles -->
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #f4f6f8;
    }

    .container {
      width: 100%;
      max-width: 400px;
      padding: 20px;
      background: #fff;
      box-shadow: 0 4px 16px rgba(0,0,0,0.1);
      border-radius: 12px;
    }

    .form-box h2 {
      text-align: center;
      margin-bottom: 24px;
      color: #333;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      transition: 0.2s;
      font-size: 15px;
    }

    input:focus {
      border-color: #00bcd4;
      outline: none;
      box-shadow: 0 0 0 2px rgba(0,188,212,0.2);
    }

    button[type="submit"] {
      width: 100%;
      padding: 12px;
      background: #00bcd4;
      border: none;
      color: white;
      font-weight: 600;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.2s ease;
    }

    button:hover {
      background: #0097a7;
    }

    .error-message {
      color: red;
      font-size: 14px;
      margin-bottom: 16px;
      text-align: center;
    }

    @media (max-width: 480px) {
      .container {
        padding: 15px;
        margin: 0 10px;
      }
    }
  </style>
</head>

<body>

<div class="container">
  <div class="form-box" id="login-form">
    <form action="config/loginconfig.php" method="post">
      <h2>Admin Login</h2>
      <?= showError($errorMsg) ?>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" name="Login">Login</button>
    </form>
  </div>
</div>

</body>
</html>
