<?php
session_start();
require_once '../config/config.php';
require_once '../includes/header.php';

// Fetch all ads
$stmt = $conn->prepare("SELECT * FROM like_ads ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
.ads-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.ads-card {
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 16px;
  background-color: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.ads-card img {
  max-width: 100%;
  border-radius: 8px;
  margin-bottom: 10px;
}

.ads-status {
  font-weight: bold;
  color: green;
}

.ads-status.inactive {
  color: red;
}

.add-ads-card {
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

.add-ads-card:hover {
  background: #f9f9f9;
  color: #555;
}
</style>

<div class="container py-4">
  <h4 class="mb-3">Manage Like & Earn Ads</h4>

  <div class="ads-grid">
    <!-- Add New Ad Block -->
    <a href="add_ad.php" class="add-ads-card">
      <div>+</div>
    </a>

    <!-- Ads Cards -->
    <?php while ($ad = $result->fetch_assoc()): ?>
      <div class="ads-card">
        <img src="<?= htmlspecialchars($ad['image_url']) ?>" alt="Ad Image" />
        <p><strong>Target:</strong> <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank"><?= htmlspecialchars($ad['target_url']) ?></a></p>
        <p class="ads-status <?= $ad['is_active'] ? '' : 'inactive' ?>">
          <?= $ad['is_active'] ? 'Active' : 'Inactive' ?>
        </p>
        <small class="text-muted">Created: <?= date('d M Y', strtotime($ad['created_at'])) ?></small>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
