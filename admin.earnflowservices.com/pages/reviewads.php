<?php
session_start();
require_once '../config/config.php';
require_once '../includes/header.php';

$promo_id = $_GET['promo_id'] ?? 0;
$stmt = $conn->prepare("SELECT ws.*, u.username 
                        FROM whatsapp_submissions ws 
                        JOIN users u ON ws.user_id = u.id 
                        WHERE ws.promo_id = ? AND ws.status != 'approved' 
                        ORDER BY ws.created_at DESC");
$stmt->bind_param("i", $promo_id);
$stmt->execute();
$results = $stmt->get_result();
?>

<div class="container py-4">
  <h4 class="mb-3">Review Submissions</h4>
  <?php if ($results->num_rows > 0): ?>
    <div class="row g-4">
      <?php while ($row = $results->fetch_assoc()): ?>
        <div class="col-md-4">
          <div class="card border border-dark">
            <img src="<?= htmlspecialchars($row['screenshot_path']) ?>" class="card-img-top" style="max-height: 250px; object-fit: cover;">
            <div class="card-body">
              <h6 class="card-title">@<?= htmlspecialchars($row['username']) ?></h6>
              <p><small>Submitted on: <?= date('d M Y H:i', strtotime($row['created_at'])) ?></small></p>
              <div class="d-flex justify-content-between mt-3">
                <form method="POST" action="../config/rewarduser.php" class="me-2">
                  <input type="hidden" name="submission_id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="action" value="approve">
                  <input type="number" name="views" min="1" class="form-control mb-2" placeholder="Enter views" required>
                  <button type="submit" class="btn btn-sm btn-success btn-dark w-100">Approve</button>
                </form>
                <form method="POST" action="../config/rewarduser.php">
                  <input type="hidden" name="submission_id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="action" value="reject">
                  <button type="submit" class="btn btn-sm btn-danger w-100">Reject</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No submissions yet for this promo.</div>
  <?php endif; ?>
</div>
<!-- Modal for Fullscreen Image -->
<div id="imageModal" style="display: none; position: fixed; top: 0; left: 0; 
     width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); 
     justify-content: center; align-items: center; z-index: 9999;">
  <img id="modalImage" src="" style="max-width: 90%; max-height: 90%;">
</div>

<script>
  document.querySelectorAll('.card-img-top').forEach(img => {
    img.addEventListener('click', () => {
      const modal = document.getElementById('imageModal');
      const modalImg = document.getElementById('modalImage');
      modal.style.display = 'flex';
      modalImg.src = img.src;
    });
  });

  document.getElementById('imageModal').addEventListener('click', () => {
    document.getElementById('imageModal').style.display = 'none';
  });
</script>


<?php require_once '../includes/footer.php'; ?>
