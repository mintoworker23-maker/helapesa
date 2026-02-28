<?php
session_start();
require_once '../config/config.php';
require_once '../includes/header.php';

// Fetch promos
$promoQuery = $conn->query("SELECT * FROM whatsapp_promos ORDER BY created_at DESC");
?>

<style>
.promo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
}

.promo-card {
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 16px;
  background-color: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.add-card {
  border: 2px dashed #aaa;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 150px;
  border-radius: 12px;
  font-size: 30px;
  color: #aaa;
  cursor: pointer;
  transition: background 0.2s ease;
}

.add-card:hover {
  background: #f9f9f9;
  color: #555;
}
</style>

<div class="container py-4">
  <h4 class="mb-3">WhatsApp Promotions</h4>
  <div class="promo-grid">
    <a href="add_whatsapp.php" class="add-card">+</a>

    <?php while ($promo = $promoQuery->fetch_assoc()): ?>
      <div class="promo-card">
        <h6><?= htmlspecialchars($promo['title']) ?></h6>
        <img src="<?= htmlspecialchars($promo['image_path']) ?>" alt="Promo" class="img-fluid my-2 rounded border">
        <p><small>Created: <?= date('d M Y', strtotime($promo['created_at'])) ?></small></p>
        <a href="reviewads.php?promo_id=<?= $promo['id'] ?>" class="btn btn-sm btn-dark mt-2 w-100">Review Submissions</a>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
