<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch ebooks and check if already rewarded
$stmt = $conn->prepare("
    SELECT e.*, (SELECT id FROM ebook_rewards WHERE user_id = ? AND ebook_id = e.id) as is_rewarded
    FROM business_ebooks e
    ORDER BY e.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="font-weight-bolder mb-0">Business Ebooks</h3>
            <p class="text-muted small mb-0">Download ebooks to gain knowledge and earn rewards</p>
        </div>
    </div>

    <div class="row">
        <?php while ($ebook = $result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 feature-card">
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="material-symbols-rounded text-primary display-6">book</span>
                        </div>
                        <h5 class="font-weight-bold"><?= htmlspecialchars($ebook['title']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($ebook['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-success font-weight-bold">Ksh <?= number_format($ebook['reward_amount'], 2) ?></span>
                            <form method="POST" action="../phpscripts/claim_ebook_reward.php">
                                <input type="hidden" name="ebook_id" value="<?= $ebook['id'] ?>">
                                <?php if ($ebook['is_rewarded']): ?>
                                    <button type="submit" class="btn btn-sm btn-outline-dark">Download Again</button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-dark">Download & Earn</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
