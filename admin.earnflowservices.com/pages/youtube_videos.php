<?php
session_start();
require_once '../config/config.php';
require_once '../includes/header.php'; // Includes sidebar + navbar

// Fetch videos
$stmt = $conn->prepare("SELECT * FROM reward_videos ORDER BY id DESC");
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
}

.add-video-card:hover {
  background: #f9f9f9;
  color: #555;
}
</style>

<div class="container py-4">
  <h4 class="mb-3">Manage Reward Videos</h4>

  <div class="video-grid">
    <!-- ➕ Add New -->
    <a href="add_video.php" class="add-video-card">
      <div>+</div>
    </a>

    <!-- 🧱 Existing videos -->
    <?php while ($video = $result->fetch_assoc()): ?>
      <div class="video-card">
        <div class="ratio ratio-16x9 mb-2">
          <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($video['youtube_id']) ?>" frameborder="0" allowfullscreen></iframe>
        </div>
        <h6><?= htmlspecialchars($video['title']) ?></h6>
        <small>Video ID: <?= htmlspecialchars($video['youtube_id']) ?></small>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
