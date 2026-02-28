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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<div class="container px-3 px-md-4 py-4">
    <h3 class="mb-4">Like & Earn</h3>
    <p>Click on a banner to view it, then come back and like it to earn <strong>Ksh 50</strong>. You can like up to <strong>5 ads per day</strong>.</p>

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
            echo '<div class="alert alert-info"> You\'ve reached your daily like limit. Check back tomorrow!</div>';
        } elseif (empty($ads)) {
            echo '<div class="alert alert-warning">No ads available at the moment. Please check back later.</div>';
        } else {
            foreach ($ads as $ad) {
                $adId = (int)$ad['id'];
                echo '<div class="col-12 col-sm-6 col-md-4 mb-4">';
                echo '<div class="card">';
                echo '<img src="' . htmlspecialchars($ad['image_url']) . '" class="card-img-top" alt="Ad Banner">';
                echo '<div class="card-body text-center">';
                echo '<a href="' . htmlspecialchars($ad['target_url']) . '" target="_blank" class="btn btn-outline-primary w-100 mb-2" onclick="markVisited(' . $adId . ')">View Ad</a>';
                echo '<button class="btn btn-success w-100" onclick="likeAd(' . $adId . ')" id="likeBtn' . $adId . '" disabled> Like</button>';
                echo '</div></div></div>';
            }
        }
        ?>
    </div>
</div>

<script>
    const visitedAds = new Set();

    function markVisited(adId) {
        visitedAds.add(adId);
        setTimeout(() => {
            const likeBtn = document.getElementById('likeBtn' + adId);
            if (likeBtn) likeBtn.disabled = false;
        }, 3000);
    }

    function likeAd(adId) {
        if (!visitedAds.has(adId)) {
            alertify.error("Please view the ad before liking.");
            return;
        }

        fetch('../phpscripts/submit_like.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `ad_id=${adId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alertify.success(data.message);
                const likeBtn = document.getElementById('likeBtn' + adId);
                if (likeBtn) likeBtn.disabled = true;
            } else {
                alertify.error(data.message);
            }
        })
        .catch(() => alertify.error("Something went wrong."));
    }
</script>

<?php include('includes/footer.php'); ?>