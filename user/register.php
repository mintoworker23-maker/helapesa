<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$referrer_username = $_GET['ref'] ?? '';
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? '',
    'logout' => $_SESSION['logout_message'] ?? '',
];

function showError($error) {
    return !empty($error) ? "<div class='alert alert-danger' role='alert'>$error</div>" : '';
}

function isactive($formname, $activeform) {
    return $formname === $activeform ? 'active' : '';
} 
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <title>Earnflow | Register</title>
  <!-- Fonts and icons -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- CSS Files -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/css/intlTelInput.min.css">
  <link id="pagestyle" href="assets/css/material-dashboard.css" rel="stylesheet" />
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
</head>

<body class="">
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
      <div class="col-12">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg blur blur-rounded top-0 z-index-3 shadow position-absolute my-3 py-2 start-0 end-0 mx-4">
          <div class="container-fluid pe-0">
            <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 " href="../index.html">
              <img src="assets/images/faviconlight.png">
            </a>
            <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon mt-2">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
              </span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navigation">
               <li class="nav-item d-flex align-items-center">
                <a class="btn btn-round btn-sm mb-0 btn-outline-primary me-2" href="login.php">Login</a>
              </li>
            </div>
          </div>
        </nav>
        <!-- End Navbar -->
         <div style="height: 80px;"></div>
      </div>
    </div>
  </div>
  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-0 text-center justify-content-center flex-column">
              <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center" style="background-image: url('assets/images/business-img-3-min.jpg'); background-size: cover;">
              </div>
            </div>
            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5 col-md-6 ">
              <div class="card card-plain">
                <div class="card-header">
                  <h4 class="font-weight-bolder">Register</h4>
                  <p class="mb-0">Create new account</p>
                </div>
                <div class="card-body">
                  <form action="phpscripts/registerconfig.php" method="POST" role="form">
                    <?php
                    if (isset($_SESSION['register_error'])) {
                        echo "<div class='alert alert-danger fade-message' style='color: red; background: #ffe0e0; padding: 10px; margin-bottom: 10px; border-radius: 5px;'>"
                            . $_SESSION['register_error'] . "</div>";
                        unset($_SESSION['register_error']);
                    }

                    if (isset($_SESSION['register_success'])) {
                        echo "<div class='alert alert-success fade-message' style='color: green; background: #e0ffe0; padding: 10px; margin-bottom: 10px; border-radius: 5px;'>"
                            . $_SESSION['register_success'] . "</div>";
                        unset($_SESSION['register_success']);
                    }
                    ?>
                    <div class="input-group input-group-outline mb-3">
                      <input type="text" placeholder="Username" name="username" class="form-control" required>
                    </div>
                    <div class="w-full max-w-md mx-auto input-group input-group-outline mb-3">
                      <label class="form-label">Phone number</label>
                       <input
                        id="phone"
                        type="tel"
                        class="border py-2 rounded w-full"
                        name="phone"
                        required
                        />
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <input type="email" placeholder="Email" name="email" class="form-control" required>
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <input type="password" placeholder="Password" name="password" class="form-control" required>
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <input type="password" placeholder="Confirm Password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <select
                        id="country"
                        name="country"
                        class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                      >
                        <option value="">Select Country</option>
                        <option value="KE" selected>Kenya</option>
                        <option value="UG">Uganda</option>
                        <option value="TZ">Tanzania</option>
                        <option value="NG">Nigeria</option>
                        <option value="ZA">South Africa</option>
                      </select>
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <input id="referral_code" type="text" placeholder="Referral Code (Optional)" name="referral_code" class="form-control" value="<?= htmlspecialchars($referrer_username) ?>">
                    </div>
                    <div class="form-check form-check-info text-start ps-0">
                      <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked required>
                      <label class="form-check-label" for="flexCheckDefault">
                        I agree the <a href="terms.php" class="text-dark font-weight-bolder">Terms and Conditions</a>
                      </label>
                    </div>
                    <div class="text-center">
                      <button type="submit" name="register" class="btn btn-lg bg-gradient-dark btn-lg w-100 mt-4 mb-0">Sign Up</button>
                    </div>
                  </form>
                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <p class="mb-2 text-sm mx-auto">
                    Already have an account?
                    <a href="login.php" class="text-primary text-gradient font-weight-bold">Sign in</a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <!-- Core JS Files -->
  <script src="../assets/js/core/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/js/intlTelInput.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = { damping: '0.5' }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script>
  setTimeout(() => {
    document.querySelectorAll('.fade-message').forEach(msg => {
      msg.style.transition = "opacity 0.5s ease";
      msg.style.opacity = '0';
      setTimeout(() => msg.style.display = 'none', 500);
    });
  }, 5000);
  </script>
  <script>
document.addEventListener('DOMContentLoaded', function () {
  const phoneInput = document.querySelector("#phone");
  const emailInput = document.querySelector('input[name="email"]');
  const passwordInput = document.querySelector('input[name="password"]');
  const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');

  // Initialize intl-tel-input
  const iti = window.intlTelInput(phoneInput, {
    initialCountry: "ke",
    separateDialCode: true,
    preferredCountries: ["ke", "ug", "tz", "ng", "za"],
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/js/utils.js",
  });

  // Prevent non-numeric input
  phoneInput.addEventListener("keypress", function (e) {
    const charCode = e.charCode;
    if (charCode < 48 || charCode > 57) {
      e.preventDefault();
    }
  });

  phoneInput.addEventListener("input", function () {
    this.value = this.value.replace(/[^\d]/g, '');
  });

  // Validate phone as user types
  function validatePhoneField() {
    const nationalNumber = iti.getNumber(intlTelInputUtils.numberFormat.NATIONAL).replace(/\D/g, '');
    if (!/^(7|1)\d{8}$/.test(nationalNumber)) {
      phoneInput.classList.remove("border-green-500");
      phoneInput.classList.add("border-red-500");
    } else {
      phoneInput.classList.remove("border-red-500");
      phoneInput.classList.add("border-green-500");
    }
  }
  phoneInput.addEventListener("blur", validatePhoneField);
  phoneInput.addEventListener("input", validatePhoneField);

  // Form submit validations
  document.querySelector('form').addEventListener('submit', function (e) {
    const phoneNumber = iti.getNumber(intlTelInputUtils.numberFormat.NATIONAL).replace(/\D/g, '');
    if (!/^0?(7|1)\d{8}$/.test(phoneNumber)) {
      alert("Validating phone number: " + phoneNumber);
      alert("Phone number must start with 7 or 1 and have exactly 9 digits (after country code).");
      e.preventDefault();
      return;
    }

    const emailDomain = emailInput.value.split('@')[1]?.toLowerCase();
    const allowedDomains = [
      'gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'icloud.com',
      'protonmail.com', 'aol.com', 'zoho.com', 'yandex.com', 'mail.com'
    ];
    if (!allowedDomains.includes(emailDomain)) {
      alert("Please use a popular email provider like Gmail, Yahoo, Outlook, etc.");
      e.preventDefault();
      return;
    }

    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

    if (!passwordPattern.test(password)) {
      alert("Password must have at least 8 characters, including uppercase, lowercase, a symbol, and a number.");
      e.preventDefault();
      return;
    }

    if (password !== confirmPassword) {
      alert("Passwords do not match.");
      e.preventDefault();
      return;
    }
  });
});
</script>


</body>
</html>