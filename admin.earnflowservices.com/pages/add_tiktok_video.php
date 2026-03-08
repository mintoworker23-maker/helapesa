<?php
session_start();
require_once '../config/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['tiktok_url']);
    $title = trim($_POST['title']);

    if ($url && $title) {
        $stmt = $conn->prepare("INSERT INTO reward_tiktok_videos (tiktok_url, title, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $url, $title);
        if ($stmt->execute()) {
          header("Location: tiktok_videos.php");
          exit;
      } else {
          $error = "Failed to save video.";
      }
    } else {
        $error = "Please fill in all fields.";
    }
}
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<div class="container py-4">
  <h4 class="mb-3">Add New TikTok Reward Video</h4>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">TikTok Video URL</label>
      <input type="url" name="tiktok_url" class="form-control border border-1" placeholder="https://www.tiktok.com/@user/video/123456789" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Video Title</label>
      <input type="text" name="title" class="form-control border border-1" placeholder="Enter video title" required>
    </div>
    <button type="submit" class="btn btn-dark">Save Video</button>
    <a href="tiktok_videos.php" class="btn btn-outline-secondary ms-2">Back</a>
  </form>
</div>

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
