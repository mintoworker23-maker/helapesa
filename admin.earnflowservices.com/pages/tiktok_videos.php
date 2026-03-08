<?php
session_start();
require_once '../config/config.php';

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM reward_tiktok_videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_msg = "Video deleted successfully!";
    } else {
        $error_msg = "Failed to delete video.";
    }
}

require_once '../includes/header.php'; // Includes sidebar + navbar

// Fetch videos
$stmt = $conn->prepare("SELECT * FROM reward_tiktok_videos ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
.video-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.video-card {
  border: 1px solid #ddd;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  background: #fff;
  padding: 15px;
  position: relative;
}

.delete-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
}

.add-video-card {
  border: 2px dashed #aaa;
  text-align: center;
  font-size: 30px;
  color: #aaa;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 150px;
  border-radius: 12px;
  transition: background 0.2s ease;
  text-decoration: none;
}

.add-video-card:hover {
  background: #f9f9f9;
  color: #555;
}
</style>

<div class="container py-4">
  <h4 class="mb-3">Manage TikTok Reward Videos</h4>

  <div class="video-grid">
    <!-- ➕ Add New -->
    <a href="add_tiktok_video.php" class="add-video-card">
      <div>+</div>
    </a>

    <!-- 🧱 Existing videos -->
    <?php while ($video = $result->fetch_assoc()): ?>
      <div class="video-card">
        <form method="POST" class="delete-btn" onsubmit="return confirm('Are you sure you want to delete this video?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $video['id'] ?>">
          <button type="submit" class="btn btn-link text-danger p-0"><i class="material-symbols-rounded">delete</i></button>
        </form>
        <div class="ratio ratio-16x9 mb-2">
           <blockquote class="tiktok-embed" cite="<?= htmlspecialchars($video['tiktok_url']) ?>" data-video-id="<?= preg_replace('/^.*\/video\/(\d+).*$/', '$1', $video['tiktok_url']) ?>" style="max-width: 605px;min-width: 325px;" > <section> </section> </blockquote> <script async src="https://www.tiktok.com/embed.js"></script>
        </div>
        <h6 class="mt-2"><?= htmlspecialchars($video['title']) ?></h6>
        <small class="text-muted d-block text-truncate"><?= htmlspecialchars($video['tiktok_url']) ?></small>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php if (isset($success_msg)): ?>
<script>
  alertify.set('notifier','position', 'top-right');
  alertify.success("<?= $success_msg ?>");
</script>
<?php endif; ?>

<?php if (isset($error_msg)): ?>
<script>
  alertify.set('notifier','position', 'top-right');
  alertify.error("<?= $error_msg ?>");
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
