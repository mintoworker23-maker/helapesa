<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'includes/header.php';
include_once '../phpscripts/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$user = getCurrentUser($conn, $userId);
?>

<div class="container row mt-5 justify-content-center mx-auto">
    <div class="col-lg-6">
        <div class="card text-center p-4">
            <div class="card-header pb-0">
                <h4 class="text-center m-0">Settings</h4>
            </div>
            <form method="POST" id="settingsForm">
                <div class="form-group text-start">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control border border-2 border-dark px-2"
                           id="email" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group text-start mt-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control border border-2 border-dark px-2"
                           id="password" name="password"
                           placeholder="Leave blank to keep current password">
                </div>

                <div class="form-group text-start mt-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control border border-2 border-dark px-2"
                           id="confirm_password" name="confirm_password"
                           placeholder="Leave blank to keep current password">
                </div>

                <button type="submit" class="btn btn-lg btn-primary mt-4 w-100 bg-dark">
                    Update Settings
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Include Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Alertify CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css" />

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<script>
$(document).ready(function() {
    $('#settingsForm').submit(function(e) {
        e.preventDefault();

        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password !== confirmPassword) {
            alertify.error('Passwords do not match!');
            return;
        }

        const formData = new FormData(this);

        alertify.message('Updating...');
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');

        $.ajax({
            type: 'POST',
            url: '../phpscripts/update_settings.php',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html('Update Settings');

                if (response.success) {
    alertify.success(response.message);
    $('#password, #confirm_password').val('');
} else {
    alertify.error(response.message);
}
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html('Update Settings');
                console.error("AJAX Error:", error);
                alertify.error('An error occurred. Please try again.');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>