<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch forex lessons and check if already rewarded
$stmt = $conn->prepare("
    SELECT l.*, (SELECT id FROM forex_lesson_rewards WHERE user_id = ? AND lesson_id = l.id) as is_rewarded
    FROM forex_lessons l
    ORDER BY l.created_at ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="font-weight-bolder mb-0">Forex Lessons</h3>
            <p class="text-muted small mb-0">Learn Forex trading and earn rewards</p>
        </div>
    </div>

    <div class="row">
        <?php while ($lesson = $result->fetch_assoc()): ?>
            <div class="col-md-6 mb-4">
                <div class="card feature-card">
                    <div class="card-body">
                        <h5 class="font-weight-bold mb-3"><?= htmlspecialchars($lesson['title']) ?></h5>
                        <div class="mb-3">
                            <p class="text-muted"><?= nl2br(htmlspecialchars($lesson['content'])) ?></p>
                        </div>
                        <?php if ($lesson['video_url']): ?>
                            <div class="ratio ratio-16x9 mb-3">
                                <iframe src="<?= htmlspecialchars(str_replace('watch?v=', 'embed/', $lesson['video_url'])) ?>" frameborder="0" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success font-weight-bold">Reward: Ksh <?= number_format($lesson['reward_amount'], 2) ?></span>
                            <?php if ($lesson['is_rewarded']): ?>
                                <span class="badge bg-light text-dark">Completed</span>
                            <?php else: ?>
                                <form method="POST" action="../phpscripts/claim_forex_reward.php">
                                    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                    <button type="submit" class="btn btn-dark">Mark as Completed & Earn</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
