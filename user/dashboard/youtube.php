<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php'; // Includes sidebar + navbar

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch the next video the user hasn't watched today
$video = null;
// Check if user has already watched a video today
$stmt = $conn->prepare("SELECT video_id FROM video_views WHERE user_id = ? AND DATE(viewed_at) = CURDATE() LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($video_id_today);
if ($stmt->fetch()) {
    // User already watched one video today, don’t show another
    $video = null;
} else {
    // Fetch a video they haven’t watched at all
    $stmt->close();
    $stmt = $conn->prepare("
        SELECT * FROM reward_videos 
        WHERE id NOT IN (
            SELECT video_id FROM video_views WHERE user_id = ?
        )
        ORDER BY id ASC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $video = $result->fetch_assoc();
}
$stmt->close();
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="font-weight-bolder mb-0">Watch and Earn</h3>
            <p class="text-muted small mb-0">Watch videos to earn rewards</p>
        </div>
        <div class="text-end">
            <span class="badge bg-gradient-success">Ksh 100 per video</span>
        </div>
    </div>

    <?php if ($video): ?>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card feature-card overflow-hidden">
                    <div class="card-header bg-gradient-dark p-4">
                        <h5 class="text-white mb-0 font-weight-bold"><?= htmlspecialchars($video['title']) ?></h5>
                        <p class="text-white opacity-8 text-sm mb-0">Watch the full video to receive your reward</p>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9">
                            <div id="player"></div>
                        </div>
                    </div>
                    
                    <div class="card-footer p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <span class="material-symbols-rounded text-info me-2">timer</span>
                                <span class="text-sm font-weight-bold" id="timerDisplay">Watch Time: 0s</span>
                            </div>
                            <div class="text-end">
                                <span class="text-xs text-muted">Reward: <strong class="text-dark">Ksh 100.00</strong></span>
                            </div>
                        </div>
                        
                        <form method="POST" action="../phpscripts/submit_video_reward.php" id="watchForm">
                            <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                            <button type="submit" class="btn btn-dark btn-custom w-100" id="manualSubmit" disabled>
                                <span class="material-symbols-rounded align-middle me-1">check_circle</span> 
                                I HAVE WATCHED THE VIDEO
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card feature-card p-5 text-center">
            <div class="mb-3 text-muted">
                <span class="material-symbols-rounded display-4">video_library</span>
            </div>
            <h4 class="font-weight-bold">All Caught Up!</h4>
            <p class="text-muted">You’ve watched all available videos for today. Please check back later for new content.</p>
            <div class="mt-2">
                <a href="index.php" class="btn btn-outline-dark btn-custom">Back to Dashboard</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://www.youtube.com/iframe_api"></script>
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

    let player;
    let watchTime = 0;
    let timer;

    function onYouTubeIframeAPIReady() {
        <?php if ($video): ?>
        player = new YT.Player('player', {
            height: '360',
            width: '640',
            videoId: '<?= htmlspecialchars($video['youtube_id']) ?>',
            events: {
                'onStateChange': onPlayerStateChange
            }
        });
        <?php endif; ?>
    }

    function onPlayerStateChange(event) {
        if (event.data === YT.PlayerState.PLAYING) {
            timer = setInterval(() => {
                watchTime++;
                document.getElementById('timerDisplay').innerText = `Watch Time: ${watchTime}s`;
            }, 1000);
        } else {
            clearInterval(timer);
        }

        if (event.data === YT.PlayerState.ENDED) {
            if (watchTime >= player.getDuration() - 5) {
                alertify.success("🎉 Video completed! You can now claim your reward.");
                document.getElementById('manualSubmit').disabled = false;
                document.getElementById('manualSubmit').classList.remove('btn-dark');
                document.getElementById('manualSubmit').classList.add('btn-success');
                // Automatically submit after 2 seconds
                setTimeout(() => document.getElementById('watchForm').submit(), 2000);
            } else {
                alertify.error("⚠️ You must watch the full video to earn rewards.");
            }
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>