<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db_connect.php';

$userId = require_auth();

$message = '';
$error   = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $country_id = !empty($_POST['country_id']) ? intval($_POST['country_id']) : null;
    $city_id    = !empty($_POST['city_id']) ? intval($_POST['city_id']) : null;
    $extra_info = trim($_POST['extra_info'] ?? '');
    $photo_url  = trim($_POST['photo_url'] ?? '');

    if (!empty($first_name) && !empty($last_name)) {
        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, country_id=?, city_id=?, extra_info=?, photo_url=? WHERE id=?");
        $stmt->bind_param("sssiissi", $first_name, $last_name, $phone, $country_id, $city_id, $extra_info, $photo_url, $userId);
        if ($stmt->execute()) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['photo_url']  = $photo_url;
            $message = "Profile updated successfully! ✨";
        } else {
            $error = "Update failed: " . $stmt->error;
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass     = $_POST['new_password'] ?? '';

    $uStmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $uStmt->bind_param("i", $userId);
    $uStmt->execute();
    $currHash = $uStmt->get_result()->fetch_assoc()['password_hash'];

    if (password_verify($current_pass, $currHash)) {
        if (strlen($new_pass) >= 6) {
            $newHash = password_hash($new_pass, PASSWORD_DEFAULT);
            $updPass = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updPass->bind_param("si", $newHash, $userId);
            $updPass->execute();
            $message = "Password changed successfully! 🔒";
        } else {
            $error = "New password must be at least 6 characters.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT u.*, c.name AS city_name, co.name AS country_name 
    FROM users u 
    LEFT JOIN cities c ON u.city_id = c.id 
    LEFT JOIN countries co ON u.country_id = co.id 
    WHERE u.id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch countries & Indian cities for dropdowns
$countries = $conn->query("SELECT id, name FROM countries ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$cities    = $conn->query("SELECT id, name, state FROM cities ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "My Profile — " . htmlspecialchars($user['first_name']);
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container container-narrow">
    
    <!-- Top Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:2.5rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border);">
      <div style="display:flex; align-items:center; gap:1.25rem;">
        <img
          src="<?php echo !empty($user['photo_url']) ? htmlspecialchars($user['photo_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['first_name'] . ' ' . $user['last_name']) . '&background=6366f1&color=fff&bold=true'; ?>"
          alt="Avatar"
          style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); box-shadow:0 0 20px var(--primary-glow);"
        >
        <div>
          <div style="display:flex; align-items:center; gap:0.5rem;">
            <h1 style="font-size:1.85rem; font-weight:800;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
            <span class="badge badge-gold"><?php echo ucfirst($user['role']); ?></span>
          </div>
          <p class="text-muted" style="font-size:0.9rem;">
            📧 <?php echo htmlspecialchars($user['email']); ?> • Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
          </p>
        </div>
      </div>

      <a href="dashboard.php" class="btn btn-secondary btn-sm">← Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-success">
        <span>✨</span>
        <span><?php echo htmlspecialchars($message); ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error">
        <span>⚠️</span>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 2fr 1.2fr; gap:2rem; align-items:start;">
      
      <!-- Profile Information Form -->
      <div class="card" style="padding:2rem;">
        <span class="badge badge-primary" style="margin-bottom:0.5rem;">Account Details</span>
        <h3 style="font-size:1.4rem; margin-bottom:1.5rem;">Edit Profile Information</h3>

        <form method="POST" action="profile.php">
          <input type="hidden" name="action" value="update_profile">

          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label">First Name *</label>
              <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Last Name *</label>
              <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+91-9876543210">
          </div>

          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label">Country</label>
              <select name="country_id">
                <?php foreach ($countries as $c): ?>
                  <option value="<?php echo $c['id']; ?>" <?php echo ($user['country_id'] == $c['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Home City</label>
              <select name="city_id">
                <option value="">-- None --</option>
                <?php foreach ($cities as $ct): ?>
                  <option value="<?php echo $ct['id']; ?>" <?php echo ($user['city_id'] == $ct['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($ct['name']) . ($ct['state'] ? ' (' . htmlspecialchars($ct['state']) . ')' : ''); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Avatar Image URL</label>
            <input type="text" name="photo_url" value="<?php echo htmlspecialchars($user['photo_url'] ?? ''); ?>" placeholder="https://images.unsplash.com/...">
          </div>

          <div class="form-group">
            <label class="form-label">Travel Bio / Favorite Circuits</label>
            <textarea name="extra_info" rows="3" placeholder="Tell other travelers about your travel passions..."><?php echo htmlspecialchars($user['extra_info'] ?? ''); ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <span>Save Profile Changes ✨</span>
          </button>
        </form>
      </div>

      <!-- Security / Change Password Box -->
      <div class="card" style="padding:2rem;">
        <span class="badge badge-teal" style="margin-bottom:0.5rem;">Security</span>
        <h3 style="font-size:1.4rem; margin-bottom:1.5rem;">Change Password</h3>

        <form method="POST" action="profile.php">
          <input type="hidden" name="action" value="change_password">

          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" required>
          </div>

          <div class="form-group">
            <label class="form-label">New Password (min 6 chars)</label>
            <input type="password" name="new_password" required>
          </div>

          <button type="submit" class="btn btn-secondary btn-block">
            <span>Update Password 🔒</span>
          </button>
        </form>
      </div>

    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
