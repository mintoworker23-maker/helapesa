<?php
session_start();
require_once '../config/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $video_url = trim($_POST['video_url']);
    $reward = trim($_POST['reward']);
    
    if ($title) {
        $stmt = $conn->prepare("INSERT INTO forex_lessons (title, content, video_url, reward_amount, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssd", $title, $content, $video_url, $reward);
        
        if ($stmt->execute()) {
            $success = "Forex lesson added successfully!";
        } else {
            $error = "Failed to save to database.";
        }
    } else {
        $error = "Title is required.";
    }
}
require_once '../includes/header.php';
?>

<div class="container py-4">
  <h4 class="mb-3">Add New Forex Lesson</h4>

  <div class="card border-0 shadow-sm p-4">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Lesson Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Content (Text/HTML)</label>
        <textarea name="content" class="form-control" rows="5"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Video URL (Optional)</label>
        <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/...">
      </div>
      <div class="mb-3">
        <label class="form-label">Reward Amount (Ksh)</label>
        <input type="number" step="0.01" name="reward" class="form-control" value="50.00" required>
      </div>
      <button type="submit" class="btn btn-dark">Save Lesson</button>
      <a href="forex_lessons.php" class="btn btn-outline-secondary ms-2">Back</a>
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
