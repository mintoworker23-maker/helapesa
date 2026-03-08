<?php
session_start();
require_once '../config/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $reward = trim($_POST['reward']);
    
    if (isset($_FILES['ebook_file']) && $_FILES['ebook_file']['error'] === 0) {
        $allowed = ['pdf', 'epub', 'docx'];
        $filename = $_FILES['ebook_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = uniqid('ebook_', true) . '.' . $ext;
            $upload_dir = '../../uploads/ebooks/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $target = $upload_dir . $new_name;
            
            if (move_uploaded_file($_FILES['ebook_file']['tmp_name'], $target)) {
                $db_path = 'uploads/ebooks/' . $new_name;
                $stmt = $conn->prepare("INSERT INTO business_ebooks (title, description, file_path, reward_amount, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssd", $title, $description, $db_path, $reward);
                
                if ($stmt->execute()) {
                    $success = "Ebook added successfully!";
                } else {
                    $error = "Failed to save to database.";
                }
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "Invalid file type. Only PDF, EPUB, and DOCX are allowed.";
        }
    } else {
        $error = "Please select a file to upload.";
    }
}
require_once '../includes/header.php';
?>

<div class="container py-4">
  <h4 class="mb-3">Add New Business Ebook</h4>

  <div class="card border-0 shadow-sm p-4">
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Ebook Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Reward Amount (Ksh)</label>
        <input type="number" step="0.01" name="reward" class="form-control" value="100.00" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Ebook File (PDF, EPUB, DOCX)</label>
        <input type="file" name="ebook_file" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-dark">Upload Ebook</button>
      <a href="ebooks.php" class="btn btn-outline-secondary ms-2">Back</a>
    </form>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script>
  alertify.set('notifier','position', 'top-right');
  <?php if ($success): ?>
    alertify.success("<?= addslashes($success) ?>");
  <?php endif; ?>
  <?php if ($error): ?>
    alertify.error("<?= addslashes($error) ?>");
  <?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
