<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php'; // Includes sidebar + navbar

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch the next tiktok video the user hasn't watched
$video = null;
$stmt = $conn->prepare("
    SELECT * FROM reward_tiktok_videos 
    WHERE id NOT IN (
        SELECT video_id FROM tiktok_video_views WHERE user_id = ?
    )
    ORDER BY id ASC 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();
$stmt->close();
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="font-weight-bolder mb-0">TikTok and Earn</h3>
            <p class="text-muted small mb-0">Watch TikTok videos to earn rewards</p>
        </div>
        <div class="text-end">
            <span class="badge bg-gradient-success">Ksh 100 per video</span>
        </div>
    </div>

    <?php if ($video): ?>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card feature-card overflow-hidden">
                    <div class="card-header bg-gradient-dark p-4">
                        <h5 class="text-white mb-0 font-weight-bold"><?= htmlspecialchars($video['title']) ?></h5>
                        <p class="text-white opacity-8 text-sm mb-0">Watch the video for at least 30 seconds to receive your reward</p>
                    </div>
                    
                    <div class="card-body p-0 text-center">
                         <blockquote class="tiktok-embed" cite="<?= htmlspecialchars($video['tiktok_url']) ?>" data-video-id="<?= preg_replace('/^.*\/video\/(\d+).*$/', '$1', $video['tiktok_url']) ?>" style="max-width: 605px;min-width: 325px;" > <section> </section> </blockquote> <script async src="https://www.tiktok.com/embed.js"></script>
                    </div>
                    
                    <div class="card-footer p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <span class="material-symbols-rounded text-info me-2">timer</span>
                                <span class="text-sm font-weight-bold" id="timerDisplay">Wait: 30s</span>
                            </div>
                        </div>
                        
                        <form method="POST" action="../phpscripts/submit_tiktok_reward.php" id="watchForm">
                            <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                            <button type="submit" class="btn btn-dark btn-custom w-100" id="manualSubmit" disabled>
                                <span class="material-symbols-rounded align-middle me-1">check_circle</span> 
                                CLAIM REWARD
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card feature-card p-5 text-center">
            <div class="mb-3 text-muted">
                <span class="material-symbols-rounded display-4">movie</span>
            </div>
            <h4 class="font-weight-bold">All Caught Up!</h4>
            <p class="text-muted">You’ve watched all available TikTok videos. Please check back later for new content.</p>
            <div class="mt-2">
                <a href="index.php" class="btn btn-outline-dark btn-custom">Back to Dashboard</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    alertify.set('notifier','position', 'top-right');

    <?php if (isset($_SESSION['video_success'])): ?>
        alertify.success("<?= addslashes($_SESSION['video_success']) ?>");
        <?php unset($_SESSION['video_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['video_error'])): ?>
        alertify.error("<?= addslashes($_SESSION['video_error']) ?>");
        <?php unset($_SESSION['video_error']); ?>
    <?php endif; ?>

    let timeLeft = 30;
    let timer;

    if (document.getElementById('timerDisplay')) {
        timer = setInterval(() => {
            timeLeft--;
            document.getElementById('timerDisplay').innerText = `Wait: ${timeLeft}s`;
            if (timeLeft <= 0) {
                clearInterval(timer);
                document.getElementById('timerDisplay').innerText = `Ready!`;
                document.getElementById('manualSubmit').disabled = false;
                document.getElementById('manualSubmit').classList.remove('btn-dark');
                document.getElementById('manualSubmit').classList.add('btn-success');
                alertify.success("🎉 You can now claim your reward!");
            }
        }, 1000);
    }
</script>

<?php require_once 'includes/footer.php'; ?>
