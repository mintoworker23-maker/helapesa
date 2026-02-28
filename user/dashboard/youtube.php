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

<!-- AlertifyJS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<div class="container py-4">
    <h3 class="mb-3">Watch and Earn</h3>
    <p>Earn <strong>Ksh 100</strong> for watching each assigned video fully. New videos may be added daily.</p>

    <?php if ($video): ?>
        <div class="card p-3">
            <h5><?= htmlspecialchars($video['title']) ?></h5>
            <div class="ratio ratio-16x9 mb-3">
                <div id="player"></div>
            </div>
            <form method="POST" action="../phpscripts/submit_video_reward.php" id="watchForm">
                <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                <button type="submit" class="btn btn-success btn-primary btn-dark" id="manualSubmit" disabled>
                    I have watched the video
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You’ve watched all available videos for today. Please check back later.
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
        player = new YT.Player('player', {
            height: '360',
            width: '640',
            videoId: '<?= htmlspecialchars($video['youtube_id']) ?>',
            events: {
                'onStateChange': onPlayerStateChange
            }
        });
    }

    function onPlayerStateChange(event) {
        if (event.data === YT.PlayerState.PLAYING) {
            timer = setInterval(() => watchTime++, 1000);
        } else {
            clearInterval(timer);
        }

        if (event.data === YT.PlayerState.ENDED) {
            if (watchTime >= player.getDuration() - 2) {
                alertify.success("Video completed! Submitting reward...");
                document.getElementById('manualSubmit').disabled = false;
                setTimeout(() => document.getElementById('watchForm').submit(), 1000);
            } else {
                alertify.error("You must watch the full video to earn rewards.");
            }
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>