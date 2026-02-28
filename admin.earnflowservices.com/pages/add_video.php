<?php
session_start();
require_once '../config/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['youtube_url']);

    // ✅ Clean YouTube ID extraction
    preg_match('/(?:v=|\/)([0-9A-Za-z_-]{11})/', $url, $matches);
    $youtube_id = $matches[1] ?? '';

    if ($youtube_id) {
        // ✅ Call YouTube oEmbed API
        $oembed_url = "https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=$youtube_id&format=json";
        $response = @file_get_contents($oembed_url);

        if ($response) {
            $data = json_decode($response, true);
            $title = $data['title'];

            $stmt = $conn->prepare("INSERT INTO reward_videos (youtube_id, title, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $youtube_id, $title);
            if ($stmt->execute()) {
              header("Location: youtube_videos.php");
              exit;
          } else {
              $error = "Failed to save video.";
          }
        } else {
            $error = "Could not fetch video title. Please ensure the YouTube link is valid.";
        }
    } else {
        $error = "Invalid YouTube link format.";
    }
}
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<div class="container py-4">
  <h4 class="mb-3">Add New Reward Video</h4>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">YouTube Video URL</label>
      <input type="url" name="youtube_url" class="form-control border border-1" placeholder="https://www.youtube.com/watch?v=abc123xyz78" required>
    </div>
    <button type="submit" class="btn btn-dark">Save Video</button>
    <a href="admin_videos.php" class="btn btn-outline-secondary ms-2">Back</a>
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
