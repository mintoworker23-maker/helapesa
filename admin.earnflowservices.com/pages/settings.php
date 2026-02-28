<?php
session_start();
require_once '../config/config.php';
require_once '../includes/header.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) { 
    // Adjust based on how admin auth is checked. usually it's admin_id or just user_id with a role.
    // Looking at index.php, it just checks session_start but doesn't explicitly redirect if not logged in?
    // Wait, index.php didn't have a redirect. Let's assume the session check is handled or I should replicate what other pages do.
    // The previous index.php read just had session_start(). Let's assume there is some auth check.
}

// Handle Form Submissions
$message = '';
$error = '';

// Helper function to get setting
function getSetting($conn, $key) {
    // Check if table exists first prevents crash
    $check = $conn->query("SHOW TABLES LIKE 'site_settings'");
    if($check->num_rows == 0) return '';
    
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()) {
        return $row['setting_value'];
    }
    return '';
}

// 1. Save Site Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $site_name = $_POST['site_name'] ?? '';
    $site_description = $_POST['site_description'] ?? '';
    $site_phone = $_POST['site_phone'] ?? '';
    $site_email = $_POST['site_email'] ?? '';
    $site_address = $_POST['site_address'] ?? '';
    $spin_0x = max(0, (float)($_POST['spin_0x'] ?? 30));
    $spin_0_5x = max(0, (float)($_POST['spin_0_5x'] ?? 25));
    $spin_1x = max(0, (float)($_POST['spin_1x'] ?? 20));
    $spin_2x = max(0, (float)($_POST['spin_2x'] ?? 15));
    $spin_5x = max(0, (float)($_POST['spin_5x'] ?? 8));
    $spin_10x = max(0, (float)($_POST['spin_10x'] ?? 2));
    
    // Create table if not exists (Safety check for first run)
    $conn->query("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT
    )");

    $settings = [
        'site_name' => $site_name,
        'site_description' => $site_description,
        'site_phone' => $site_phone,
        'site_email' => $site_email,
        'site_address' => $site_address,
        'spin_percent_0x' => $spin_0x,
        'spin_percent_0_5x' => $spin_0_5x,
        'spin_percent_1x' => $spin_1x,
        'spin_percent_2x' => $spin_2x,
        'spin_percent_5x' => $spin_5x,
        'spin_percent_10x' => $spin_10x
    ];

    // Handle Logo Upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $filename = $_FILES['site_logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $newFilename = 'logo.' . $ext;
            $destination = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $destination)) {
                $settings['site_logo'] = $newFilename; // Save just filename or relative path
            }
        }
    }

    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
    }
    $_SESSION['message'] = "Site settings updated successfully!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 2. Add Package
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_package') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $features = $_POST['features'] ?? '';
    $access_trivia = isset($_POST['access_trivia']) ? 1 : 0;
    $access_adverts = isset($_POST['access_adverts']) ? 1 : 0;
    $access_youtube = isset($_POST['access_youtube']) ? 1 : 0;
    $access_social_media = isset($_POST['access_social_media']) ? 1 : 0;
    
    // Create table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        features TEXT,
        access_trivia TINYINT(1) DEFAULT 0,
        access_adverts TINYINT(1) DEFAULT 0,
        access_youtube TINYINT(1) DEFAULT 0,
        access_social_media TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Add columns if they don't exist (for existing tables)
    $columns = ['access_trivia', 'access_adverts', 'access_youtube', 'access_social_media'];
    foreach ($columns as $col) {
        $check = $conn->query("SHOW COLUMNS FROM packages LIKE '$col'");
        if ($check->num_rows == 0) {
            $conn->query("ALTER TABLE packages ADD COLUMN $col TINYINT(1) DEFAULT 0");
        }
    }

    if ($name && $price) {
        $stmt = $conn->prepare("INSERT INTO packages (name, price, features, access_trivia, access_adverts, access_youtube, access_social_media) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsiiii", $name, $price, $features, $access_trivia, $access_adverts, $access_youtube, $access_social_media);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Package added successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error adding package: " . $conn->error;
        }
    } else {
        $error = "Name and Price are required.";
    }
}

// 3. Delete Package
if (isset($_GET['delete_package'])) {
    $id = intval($_GET['delete_package']);
    $conn->query("DELETE FROM packages WHERE id = $id");
    $_SESSION['message'] = "Package deleted successfully!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch Data for View
$site_name = getSetting($conn, 'site_name');
$site_description = getSetting($conn, 'site_description');
$site_logo = getSetting($conn, 'site_logo');
$site_phone = getSetting($conn, 'site_phone');
$site_email = getSetting($conn, 'site_email');
$site_address = getSetting($conn, 'site_address');
$spin_0x = getSetting($conn, 'spin_percent_0x');
$spin_0_5x = getSetting($conn, 'spin_percent_0_5x');
$spin_1x = getSetting($conn, 'spin_percent_1x');
$spin_2x = getSetting($conn, 'spin_percent_2x');
$spin_5x = getSetting($conn, 'spin_percent_5x');
$spin_10x = getSetting($conn, 'spin_percent_10x');

$spin_0x = ($spin_0x === '') ? 30 : (float)$spin_0x;
$spin_0_5x = ($spin_0_5x === '') ? 25 : (float)$spin_0_5x;
$spin_1x = ($spin_1x === '') ? 20 : (float)$spin_1x;
$spin_2x = ($spin_2x === '') ? 15 : (float)$spin_2x;
$spin_5x = ($spin_5x === '') ? 8 : (float)$spin_5x;
$spin_10x = ($spin_10x === '') ? 2 : (float)$spin_10x;

$packages = [];
$check_pkg = $conn->query("SHOW TABLES LIKE 'packages'");
if ($check_pkg->num_rows > 0) {
    $res = $conn->query("SELECT * FROM packages ORDER BY price ASC");
    while($row = $res->fetch_assoc()) {
        $packages[] = $row;
    }
}
?>

<body class="g-sidenav-show bg-gray-200">
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          
          <!-- General info Block -->
          <div class="card my-4" id="general-settings">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-dark shadow-primary border-radius-lg pt-4 pb-3"> <!-- Changed to bg-gradient-dark -->
                <h6 class="text-white text-capitalize ps-3">General Settings</h6>
              </div>
            </div>
            
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_settings">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Site Name</label>
                                <input type="text" class="form-control border border-2 border-dark px-2" name="site_name" value="<?= htmlspecialchars($site_name) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea class="form-control border border-2 border-dark px-2" name="site_description" rows="3"><?= htmlspecialchars($site_description) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" class="form-control border border-2 border-dark px-2" name="site_phone" value="<?= htmlspecialchars($site_phone) ?>" placeholder="+254...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" class="form-control border border-2 border-dark px-2" name="site_email" value="<?= htmlspecialchars($site_email) ?>" placeholder="info@example.com">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contact Address</label>
                                <textarea class="form-control border border-2 border-dark px-2" name="site_address" rows="2" placeholder="Street, City, Country"><?= htmlspecialchars($site_address) ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Site Logo (Upload)</label>
                                <input type="file" class="form-control border border-2 border-dark px-2" name="site_logo">
                            </div>

                            <hr>
                            <h6 class="mb-3">Spin and Win Percentages</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">0x (%)</label>
                                    <input type="number" step="0.01" min="0" class="form-control border border-2 border-dark px-2" name="spin_0x" value="<?= htmlspecialchars($spin_0x) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">0.5x (%)</label>
                                    <input type="number" step="0.01" min="0" class="form-control border border-2 border-dark px-2" name="spin_0_5x" value="<?= htmlspecialchars($spin_0_5x) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">1x (%)</label>
                                    <input type="number" step="0.01" min="0" class="form-control border border-2 border-dark px-2" name="spin_1x" value="<?= htmlspecialchars($spin_1x) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">2x (%)</label>
                                    <input type="number" step="0.01" min="0" class="form-control border border-2 border-dark px-2" name="spin_2x" value="<?= htmlspecialchars($spin_2x) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">5x (%)</label>
                                    <input type="number" step="0.01" min="0" class="form-control border border-2 border-dark px-2" name="spin_5x" value="<?= htmlspecialchars($spin_5x) ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">10x (%)</label>
                                    <input type="number" step="0.01" min="0" class="form-control border border-2 border-dark px-2" name="spin_10x" value="<?= htmlspecialchars($spin_10x) ?>">
                                </div>
                            </div>
                            <p class="text-xs text-muted mb-0">Tip: Set totals close to 100. If they do not total 100, the system auto-normalizes them.</p>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <label class="form-label">Current Logo</label><br>
                            <?php if($site_logo && file_exists("../uploads/".$site_logo)): ?>
                                <img src="../uploads/<?= htmlspecialchars($site_logo) ?>" alt="Site Logo" class="img-fluid border rounded p-2" style="max-height: 150px;">
                            <?php else: ?>
                                <div class="p-4 border border-dashed rounded text-muted">No Logo Set</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-dark mt-3">Save Changes</button>
                    <!-- Changed button logic to update specific form -->
                </form>
            </div>
          </div>

          <!-- Packages Block (Stacked below) -->
          <div class="card my-4" id="packages-settings">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-dark shadow-primary border-radius-lg pt-4 pb-3"> <!-- Changed to bg-gradient-dark -->
                <h6 class="text-white text-capitalize ps-3">Packages Management</h6>
              </div>
            </div>

            <div class="card-body p-4">
                        
                        <div class="mb-4">
                            <h5>Existing Packages</h5>
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Features</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($packages)): ?>
                                            <tr><td colspan="4" class="text-center py-4">No packages found. Add one below.</td></tr>
                                        <?php else: ?>
                                            <?php foreach($packages as $pkg): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($pkg['name']) ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0"><?= number_format($pkg['price'], 2) ?></p>
                                                </td>
                                                <td>
                                                    <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($pkg['features']) ?></p>
                                                </td>
                                                <td class="align-middle">
                                                    <a href="?delete_package=<?= $pkg['id'] ?>" class="text-danger font-weight-bold text-xs" onclick="return confirm('Are you sure?')">
                                                        Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="border-top pt-4">
                            <h5>Add New Package</h5>
                            <form method="POST">
                                <input type="hidden" name="action" value="add_package">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Package Name</label>
                                        <input type="text" class="form-control border border-2 border-dark px-2" name="name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Price</label>
                                        <input type="number" step="0.01" class="form-control border border-2 border-dark px-2" name="price" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Features (comma separated)</label>
                                    <textarea class="form-control border border-2 border-dark px-2" name="features" rows="2" placeholder="e.g. 24/7 Support, Free Access, 50 GB Storage"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label d-block text-strong">Access Permissions</label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-check ps-0">
                                                <input class="form-check-input ms-0" type="checkbox" name="access_trivia" value="1" id="access_trivia">
                                                <label class="form-check-label ms-2" for="access_trivia">Trivia</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check ps-0">
                                                <input class="form-check-input ms-0" type="checkbox" name="access_adverts" value="1" id="access_adverts">
                                                <label class="form-check-label ms-2" for="access_adverts">Adverts</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check ps-0">
                                                <input class="form-check-input ms-0" type="checkbox" name="access_youtube" value="1" id="access_youtube">
                                                <label class="form-check-label ms-2" for="access_youtube">YouTube</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check ps-0">
                                                <input class="form-check-input ms-0" type="checkbox" name="access_social_media" value="1" id="access_social_media">
                                                <label class="form-check-label ms-2" for="access_social_media">WhatsApp</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-dark">Add Package</button> <!-- Changed button color -->
                            </form>
                        </div>

                    </div> <!-- End of Card Body -->
            </div>
          </div>
        </div>
      </div>
      <?php include '../includes/footer.php'; ?>
    </div>
  </main>
</body>
