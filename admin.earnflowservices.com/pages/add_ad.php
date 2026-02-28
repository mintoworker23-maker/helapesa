<?php
session_start();
require_once '../config/config.php';
include '../includes/header.php';

// Optional: Only allow admins
/*
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}
*/
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_url = trim($_POST['target_url']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/ads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = uniqid('ad_') . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_path = $upload_dir . $file_name;
        $relative_path = 'uploads/ads/' . $file_name;

        if (move_uploaded_file($file_tmp, $file_path)) {
            $stmt = $conn->prepare("INSERT INTO like_ads (image_url, target_url, is_active) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $relative_path, $target_url, $is_active);
            $stmt->execute();
            $stmt->close();
            $message = "Ad uploaded successfully.";
        } else {
            $error = "Image upload failed.";
        }
    } else {
        $error = "No image selected or upload error.";
    }
}
?>

<div class="container py-4">
    <h3 class="mb-4">Upload New Ad</h3>


    <form action="" method="POST" enctype="multipart/form-data" class="card p-4">
        <div class="form-group mb-3">
            <label for="image">Ad Image</label>
            <input type="file" name="image" id="image" class="form-control border border-1 px-1" required accept="image/*">
        </div>

        <div class="form-group mb-3">
            <label for="target_url">Target URL</label>
            <input type="url" name="target_url" id="target_url" class="form-control border border-1 px-1" required placeholder="https://example.com">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
            <label for="is_active" class="form-check-label">Active</label>
        </div>

        <button type="submit" class="btn btn-primary btn-dark">Upload Ad</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>