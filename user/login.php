<?php
session_start();
require_once 'phpscripts/config.php';

$theme_color = getSiteSetting($conn, 'theme_color');
$site_logo = getSiteSetting($conn, 'site_logo');
$site_name = getSiteSetting($conn, 'site_name') ?: 'Helapesa';

// Theme color mapping
$colors = [
    'black' => ['primary' => '#141727', 'gradient' => 'linear-gradient(310deg, #3A416F, #141727)', 'light' => '#f8f9fa'],
    'darkblue' => ['primary' => '#2196F3', 'gradient' => 'linear-gradient(310deg, #2196F3, #047edf)', 'light' => '#f0f7ff'],
    'green' => ['primary' => '#4CAF50', 'gradient' => 'linear-gradient(310deg, #66BB6A, #43A047)', 'light' => '#f6fbf5'],
    'purple' => ['primary' => '#cb0c9f', 'gradient' => 'linear-gradient(310deg, #7928CA, #FF0080)', 'light' => '#fdfaff'],
    'red' => ['primary' => '#f44336', 'gradient' => 'linear-gradient(310deg, #f53939, #fb6340)', 'light' => '#fffafa'],
];

$selected_color = $colors[$theme_color] ?? $colors['black'];

$referrer_username = $_GET['ref'] ?? '';
$messages = [
    'login_error' => $_SESSION['login_error'] ?? '',
    'register_error' => $_SESSION['register_error'] ?? '',
    'register_success' => $_SESSION['register_success'] ?? '',
    'logout_message' => $_SESSION['logout_message'] ?? '',
    'activation_success' => $_SESSION['activation_success'] ?? '',
];

// Determine which side to show based on errors
$initial_state = (!empty($messages['register_error']) || !empty($messages['register_success']) || !empty($referrer_username)) ? 'right-panel-active' : '';

unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['register_success'], $_SESSION['logout_message'], $_SESSION['activation_success']);

function showMessage($message, $type = 'danger') {
    return !empty($message) ? "<div class='alert alert-{$type} fade-message' role='alert' style='font-size: 0.8rem; padding: 0.6rem 1rem; border-radius: 10px; border: none;'>{$message}</div>" : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <title><?= htmlspecialchars($site_name) ?> | Welcome</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link id="pagestyle" href="assets/css/soft-ui-dashboard.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/css/intlTelInput.min.css">
    <style>
        :root {
            --primary-color: <?= $selected_color['primary'] ?>;
            --gradient-color: <?= $selected_color['gradient'] ?>;
            --light-bg: <?= $selected_color['light'] ?>;
        }

        body {
            background-color: var(--light-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .auth-container {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            width: 900px;
            max-width: 95%;
            min-height: 600px;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .auth-container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .auth-container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        @keyframes show {
            0%, 49.99% { opacity: 0; z-index: 1; }
            50%, 100% { opacity: 1; z-index: 5; }
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .auth-container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .overlay {
            background: var(--gradient-color);
            color: #FFFFFF;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .auth-container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transition: transform 0.6s ease-in-out;
        }

        .overlay-left { transform: translateX(-20%); }
        .auth-container.right-panel-active .overlay-left { transform: translateX(0); }
        .overlay-right { right: 0; transform: translateX(0); }
        .auth-container.right-panel-active .overlay-right { transform: translateX(20%); }

        .form-content {
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
            overflow-y: auto;
        }

        .form-content::-webkit-scrollbar { width: 4px; }
        .form-content::-webkit-scrollbar-thumb { background: #f1f1f1; border-radius: 10px; }

        h1 { font-weight: 700; margin: 0; font-size: 2rem; }
        h2 { font-weight: 700; margin-bottom: 10px; }
        p { font-size: 0.9rem; line-height: 1.6; margin: 20px 0 30px; }

        input, select {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        input:focus, select:focus {
            background-color: #fff;
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
        }

        button {
            border-radius: 12px;
            border: 1px solid var(--primary-color);
            background-color: var(--primary-color);
            color: #FFFFFF;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in, background-color 0.3s;
            cursor: pointer;
            margin-top: 10px;
        }
        button:active { transform: scale(0.95); }
        button:focus { outline: none; }
        button.ghost {
            background-color: transparent;
            border-color: #FFFFFF;
            margin-top: 0;
        }

        .btn-submit {
            background: var(--gradient-color);
            border: none;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .mobile-toggle { display: none; }

        @media (max-width: 768px) {
            body { height: auto; min-height: 100vh; overflow-y: auto; align-items: flex-start; padding: 20px 0; }
            .auth-container { min-height: 100vh; width: 100%; border-radius: 0; box-shadow: none; max-width: 100%; }
            .overlay-container { display: none; }
            .form-container { width: 100%; position: relative; height: auto; }
            .auth-container.right-panel-active .sign-in-container { display: none; }
            .auth-container:not(.right-panel-active) .sign-up-container { display: none; }
            .sign-up-container { opacity: 1; z-index: 5; transform: none !important; }
            .sign-in-container { transform: none !important; }
            .mobile-toggle {
                display: block !important;
                margin: 25px 0;
                color: var(--primary-color);
                cursor: pointer;
                font-size: 0.9rem;
                font-weight: 600;
            }
            .form-content { padding: 40px 30px; height: auto; min-height: 100vh; }
        }

        .iti { width: 100%; margin: 8px 0; }
    </style>
</head>
<body class="<?= $initial_state ?>">

<div class="auth-container <?= $initial_state ?>" id="container">
    <!-- Sign Up Form -->
    <div class="form-container sign-up-container">
        <form class="form-content" action="phpscripts/registerconfig.php" method="POST" id="registerForm">
            <h2 style="color: var(--primary-color)">Create Account</h2>
            <p class="text-muted">Start your journey with <?= htmlspecialchars($site_name) ?></p>
            
            <?= showMessage($messages['register_error'], 'danger') ?>
            <?= showMessage($messages['register_success'], 'success') ?>

            <input type="text" placeholder="Username" name="username" required />
            <input id="phone" type="tel" name="phone" required />
            <input type="email" placeholder="Email Address" name="email" required />
            <input type="password" placeholder="Create Password" name="password" required />
            <input type="password" placeholder="Confirm Password" name="confirm_password" required />
            <select id="country" name="country" required>
                <option value="">Select Country</option>
                <option value="KE" selected>Kenya</option>
                <option value="UG">Uganda</option>
                <option value="TZ">Tanzania</option>
                <option value="NG">Nigeria</option>
                <option value="ZA">South Africa</option>
            </select>
            <input id="referral_code" type="text" placeholder="Referral Code (Optional)" name="referral_code" value="<?= htmlspecialchars($referrer_username) ?>">
            
            <div class="text-start w-100 mt-2" style="font-size: 0.85rem;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" id="terms" style="width: auto; margin: 0;" checked required>
                    <span>I agree to the <a href="terms.php" class="text-dark font-weight-bold">Terms</a></span>
                </label>
            </div>

            <button type="submit" name="register" class="btn-submit">Sign Up</button>
            <span class="mobile-toggle" id="mobileSignIn">Already have an account? <b>Sign In</b></span>
        </form>
    </div>

    <!-- Sign In Form -->
    <div class="form-container sign-in-container">
        <form class="form-content" action="phpscripts/loginconfig.php" method="post">
            <div class="mb-4">
                <?php if ($site_logo): ?>
                    <img src="../uploads/<?= htmlspecialchars($site_logo) ?>" style="max-height: 70px; width: auto;">
                <?php else: ?>
                    <h1 style="color: var(--primary-color)"><?= htmlspecialchars($site_name) ?></h1>
                <?php endif; ?>
            </div>
            <h2>Welcome Back</h2>
            <p class="text-muted">Log in to manage your account</p>

            <?= showMessage($messages['activation_success'], 'success') ?>
            <?= showMessage($messages['logout_message'], 'success') ?>
            <?= showMessage($messages['login_error'], 'danger') ?>

            <input type="text" name="identifier" placeholder="Username, Phone, or Email" required />
            <input type="password" name="password" placeholder="Your Password" required />
            
            <div class="text-start w-100 mt-2" style="font-size: 0.85rem;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" id="rememberMe" style="width: auto; margin: 0;" checked>
                    <span>Keep me logged in</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>
            <span class="mobile-toggle" id="mobileSignUp">New here? <b>Create an Account</b></span>
        </form>
    </div>

    <!-- Overlay -->
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Welcome Back!</h1>
                <p>Stay connected with us by logging into your account</p>
                <button class="ghost" id="signIn">Sign In</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>Hello, Friend!</h1>
                <p>Register your details and start earning with us today</p>
                <button class="ghost" id="signUp">Sign Up</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/core/popper.min.js"></script>
<script src="assets/js/core/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/js/intlTelInput.min.js"></script>
<script>
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const mobileSignUp = document.getElementById('mobileSignUp');
    const mobileSignIn = document.getElementById('mobileSignIn');
    const container = document.getElementById('container');

    const activateSignUp = () => container.classList.add("right-panel-active");
    const activateSignIn = () => container.classList.remove("right-panel-active");

    signUpButton.addEventListener('click', activateSignUp);
    signInButton.addEventListener('click', activateSignIn);
    mobileSignUp.addEventListener('click', activateSignUp);
    mobileSignIn.addEventListener('click', activateSignIn);

    // Auto-fade messages
    setTimeout(() => {
        document.querySelectorAll('.fade-message').forEach(msg => {
            msg.style.transition = "opacity 0.5s ease, transform 0.5s ease";
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-10px)';
            setTimeout(() => msg.style.display = 'none', 500);
        });
    }, 5000);

    // Phone Input
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.querySelector("#phone");
        const iti = window.intlTelInput(phoneInput, {
            initialCountry: "ke",
            separateDialCode: true,
            preferredCountries: ["ke", "ug", "tz", "ng", "za"],
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/js/utils.js",
        });

        phoneInput.addEventListener("keypress", function (e) {
            if (e.charCode < 48 || e.charCode > 57) e.preventDefault();
        });

        const registerForm = document.getElementById('registerForm');
        registerForm.addEventListener('submit', function (e) {
            const phoneNumber = iti.getNumber(intlTelInputUtils.numberFormat.NATIONAL).replace(/\D/g, '');
            if (!/^0?(7|1)\d{8}$/.test(phoneNumber)) {
                alert("Phone number must start with 7 or 1 and have exactly 9 digits.");
                e.preventDefault();
                return;
            }

            const password = registerForm.querySelector('input[name="password"]').value;
            const confirmPassword = registerForm.querySelector('input[name="confirm_password"]').value;
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
