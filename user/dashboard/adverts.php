<?php
require_once '../phpscripts/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
include('includes/header.php');
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="font-weight-bolder mb-0">Like & Earn</h3>
            <p class="text-muted small mb-0">View ads and like them to earn rewards</p>
        </div>
        <div class="text-end">
            <span class="badge bg-gradient-success">Ksh 50 per like</span>
        </div>
    </div>

    <div class="card feature-card mb-4 bg-gradient-dark">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h5 class="text-white mb-1">Instructions</h5>
                    <p class="text-white opacity-8 text-sm mb-0">
                        1. Click "View Ad" to open the banner.<br>
                        2. Stay on the ad page for at least 3 seconds.<br>
                        3. Come back and click the "Like" button to earn <strong>Ksh 50</strong>.
                    </p>
                </div>
                <div class="col-lg-4 text-end d-none d-lg-block">
                    <span class="material-symbols-rounded text-white opacity-5" style="font-size: 80px;">ads_click</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="adsContainer">
        <?php
        $stmt = $conn->prepare("SELECT la.id, la.image_url, la.target_url FROM like_ads la WHERE la.is_active = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $ads = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Check today's like count
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM ad_likes WHERE user_id = ? AND DATE(liked_at) = CURDATE()");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $likes_today_row = $result->fetch_assoc();
        $likes_today = $likes_today_row['count'] ?? 0;
        $stmt->close();

        if ($likes_today >= 5) {
            echo '<div class="col-12"><div class="alert alert-info border-0 text-white bg-gradient-info">🚀 You\'ve reached your daily like limit (5 ads). Check back tomorrow!</div></div>';
        } elseif (empty($ads)) {
            echo '<div class="col-12"><div class="card feature-card p-5 text-center"><p class="text-muted mb-0">No ads available at the moment. Please check back later.</p></div></div>';
        } else {
            foreach ($ads as $ad) {
                $adId = (int)$ad['id'];
                ?>
                <div class="col-12 col-sm-6 col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="card-img-top" alt="Ad Banner" style="height: 180px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-white text-dark shadow-sm">Ad #<?= $adId ?></span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" class="btn btn-outline-dark btn-custom w-100 mb-2" onclick="markVisited(<?= $adId ?>)">
                                <span class="material-symbols-rounded align-middle text-sm me-1">visibility</span> View Ad
                            </a>
                            <button class="btn btn-success btn-custom w-100" onclick="likeAd(<?= $adId ?>)" id="likeBtn<?= $adId ?>" disabled>
                                <span class="material-symbols-rounded align-middle text-sm me-1">thumb_up</span> Like Ad
                            </button>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<script>
    const visitedAds = new Set();

    function markVisited(adId) {
        visitedAds.add(adId);
        const likeBtn = document.getElementById('likeBtn' + adId);
        if (likeBtn) {
            likeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Waiting...';
            setTimeout(() => {
                likeBtn.disabled = false;
                likeBtn.innerHTML = '<span class="material-symbols-rounded align-middle text-sm me-1">thumb_up</span> Like Ad';
            }, 3000);
        }
    }

    function likeAd(adId) {
        if (!visitedAds.has(adId)) {
            alertify.error("Please view the ad before liking.");
            return;
        }

        const btn = document.getElementById('likeBtn' + adId);
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

        fetch('../phpscripts/submit_like.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `ad_id=${adId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alertify.success(data.message);
                btn.innerHTML = '<span class="material-symbols-rounded align-middle text-sm me-1">check_circle</span> Liked';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-secondary');
            } else {
                alertify.error(data.message);
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-rounded align-middle text-sm me-1">thumb_up</span> Like Ad';
            }
        })
        .catch(() => {
            alertify.error("Something went wrong.");
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-rounded align-middle text-sm me-1">thumb_up</span> Like Ad';
        });
    }
</script>

<?php include('includes/footer.php'); ?>