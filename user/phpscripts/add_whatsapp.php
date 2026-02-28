<?php
session_start();
require_once '../config/config.php';

$error = '';
$baseURL = 'https://earnflowservices.com/admin.earnflowservices.com'; // Add protocol

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $imagePath = '';

    // Handle image upload
    if (!empty($_FILES['promo_image']['name'])) {
        $targetDir = "uploads/whatsapp/";
        $fullDirPath = "../" . $targetDir;

        if (!is_dir($fullDirPath)) {
            mkdir($fullDirPath, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES['promo_image']['name']);
        $targetFile = $targetDir . $filename;
        $serverFilePath = $fullDirPath . $filename;

        if (move_uploaded_file($_FILES['promo_image']['tmp_name'], $serverFilePath)) {
            $imagePath = $baseURL . '/' . $targetFile; // ✅ Save full URL
        } else {
            $error = "Failed to upload image.";
        }
    }

    // Save to DB
    if ($title && $imagePath) {
        $stmt = $conn->prepare("INSERT INTO whatsapp_promos (title, image_path, reward_amount, created_at) VALUES (?, ?, '150', NOW())");
        $stmt->bind_param("ss", $title, $imagePath);

        if ($stmt->execute()) {
            header("Location: socialmediaads.php");
            exit;
        } else {
            $error = "Failed to save promotion.";
        }
    } else if (!$error) {
        $error = "Title and image are required.";
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="container py-4">
    <h4 class="mb-3">Add WhatsApp Promotion</h4>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Promotion Title</label>
            <input type="text" name="title" id="title" class="form-control border border-1" required>
        </div>

        <div class="mb-3">
            <label for="promo_image" class="form-label">Promo Image</label>
            <input type="file" name="promo_image" id="promo_image" class="form-control border border-1" accept="image/*" required>
        </div>

        <button type="submit" class="btn btn-primary btn-dark">Save Promotion</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>