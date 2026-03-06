<?php
session_start();
require_once '../phpscripts/config.php';
require_once 'includes/header.php'; // navbar + sidebar

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch active promo
$stmt = $conn->prepare("SELECT * FROM whatsapp_promos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$promo = $result->fetch_assoc();
$stmt->close();
?>

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="font-weight-bolder mb-0">WhatsApp Promotion</h3>
            <p class="text-muted small mb-0">Earn by sharing our promos on your status</p>
        </div>
        <div class="text-end">
            <span class="badge bg-gradient-success">Earn per view/status</span>
        </div>
    </div>

    <?php if ($promo): ?>
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-header bg-gradient-dark p-4">
                        <h5 class="text-white mb-0 font-weight-bold"><?= htmlspecialchars($promo['title']) ?></h5>
                        <p class="text-white opacity-8 text-sm mb-0">Follow the steps below to participate</p>
                    </div>
                    <div class="card-body p-4 text-center">
                        <p class="text-start mb-3">1. Download this image and post it on your WhatsApp Status for 24 hours.</p>
                        <div class="mb-4">
                            <img src="<?= $promo['image_path'] ?>" alt="Promo Image" class="img-fluid border-radius-lg shadow-sm" style="max-height: 400px; border: 1px solid #eee;">
                        </div>
                        <a href="../<?= $promo['image_path'] ?>" download class="btn btn-dark btn-custom w-100">
                            <span class="material-symbols-rounded align-middle me-1">download</span> DOWNLOAD PROMO IMAGE
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-header p-4 pb-0">
                        <h5 class="font-weight-bold mb-1">2. Submit Proof</h5>
                        <p class="text-muted text-sm">Upload a screenshot of your status after 24 hours</p>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="../phpscripts/submit_whatsapp_proof.php" enctype="multipart/form-data" id="proofForm">
                            <input type="hidden" name="promo_id" value="<?= $promo['id'] ?>">
                            <div class="mb-4">
                                <label for="screenshot" class="form-label font-weight-bold">Status Screenshot</label>
                                <div class="p-3 border-2 border-dashed border-radius-lg text-center bg-gray-100" id="dropZone">
                                    <span class="material-symbols-rounded text-secondary display-4 d-block mb-2">add_a_photo</span>
                                    <input type="file" name="screenshot" id="screenshot" class="form-control" accept="image/*" required style="display: none;">
                                    <button type="button" class="btn btn-sm btn-outline-dark mb-2" onclick="document.getElementById('screenshot').click()">Choose File</button>
                                    <p class="text-xs text-muted mb-0" id="fileNameDisplay">No file chosen (Max 2MB)</p>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-custom w-100">
                                <span class="material-symbols-rounded align-middle me-1">upload_file</span> SUBMIT FOR REVIEW
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card feature-card p-5 text-center">
            <div class="mb-3 text-muted">
                <span class="material-symbols-rounded display-4">campaign</span>
            </div>
            <h4 class="font-weight-bold">No Active Promotions</h4>
            <p class="text-muted">No active WhatsApp promotions at the moment. Please check back later.</p>
        </div>
    <?php endif; ?>

    <!-- User Submissions -->
    <?php
    $submissions = $conn->prepare("SELECT * FROM whatsapp_submissions WHERE user_id = ? ORDER BY created_at DESC");
    $submissions->bind_param("i", $user_id);
    $submissions->execute();
    $subResults = $submissions->get_result();
    ?>

    <?php if ($subResults->num_rows > 0): ?>
        <div class="card feature-card mt-4">
            <div class="card-header p-4 pb-0">
                <h5 class="font-weight-bold mb-0">Your Submission History</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Promo ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date Submitted</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Status</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $subResults->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">#<?= htmlspecialchars($row['promo_id']) ?></p>
                                    </td>
                                    <td>
                                        <p class="text-sm mb-0"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <span class="badge badge-sm bg-gradient-warning">Pending</span>
                                        <?php elseif ($row['status'] == 'approved'): ?>
                                            <span class="badge badge-sm bg-gradient-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge badge-sm bg-gradient-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-end">
                                        <a href="../<?= $row['screenshot_path'] ?>" target="_blank" class="btn btn-link text-dark text-gradient p-0 mb-0">
                                            <span class="material-symbols-rounded text-sm">open_in_new</span> View Proof
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    alertify.set('notifier','position', 'top-right');
    
    <?php if (isset($_SESSION['whatsapp_success'])): ?>
    alertify.success("<?= addslashes($_SESSION['whatsapp_success']) ?>");
    <?php unset($_SESSION['whatsapp_success']); endif; ?>

    <?php if (isset($_SESSION['whatsapp_error'])): ?>
    alertify.error("<?= addslashes($_SESSION['whatsapp_error']) ?>");
    <?php unset($_SESSION['whatsapp_error']); endif; ?>

    document.getElementById('screenshot')?.addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : "No file chosen";
        document.getElementById('fileNameDisplay').innerText = fileName;
        document.getElementById('dropZone').classList.add('bg-light');
    });

    document.getElementById('proofForm')?.addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';
    });
</script>

<?php require_once 'includes/footer.php'; ?>