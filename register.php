<?php
include 'includes/db_connect.php';
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone_code   = trim($_POST['phone_code'] ?? '91');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $country_id   = !empty($_POST['country_id']) ? intval($_POST['country_id']) : 1; // Default to India
    $city_id      = !empty($_POST['city_id']) ? intval($_POST['city_id']) : null;
    $extra_info   = trim($_POST['extra_info'] ?? '');
    $password     = $_POST['password'] ?? '';

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "Please fill in all mandatory fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Construct standard international phone format
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone_number);
        $phone = '+' . $phone_code . '-' . $cleanPhone;

        // Check if email already registered
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "This email address is already registered. Please sign in.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $default_photo = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200';

            $insertSql = "INSERT INTO users 
                (first_name, last_name, email, phone, country_id, city_id, extra_info, password_hash, role, photo_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'user', ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ssssiisss", $first_name, $last_name, $email, $phone, $country_id, $city_id, $extra_info, $password_hash, $default_photo);

            if ($insertStmt->execute()) {
                $newUserId = $insertStmt->insert_id;
                // Automatically establish session
                $_SESSION['user_id']    = $newUserId;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['role']       = 'user';
                $_SESSION['photo_url']  = $default_photo;

                // Log audit action
                $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, 'REGISTER', 'users', ?)");
                if ($logStmt) {
                    $logStmt->bind_param("ii", $newUserId, $newUserId);
                    $logStmt->execute();
                }

                header("Location: dashboard.php?registered=1");
                exit();
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}

// Fetch countries for dropdown
$countries = $conn->query("SELECT id, name, phone_code FROM countries ORDER BY (id = 1) DESC, name ASC");

// Fetch Indian cities by default for quick selection
$defaultCities = $conn->query("SELECT id, name, state FROM cities WHERE country_id = 1 ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Join GlobeTrotter India — Create Your Free Account</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><text y='26' font-size='28'>🇮🇳</text></svg>">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-wrapper">
  <!-- Left Side: Inspiring Indian Travel Visuals -->
  <div class="auth-sidebar">
    <img
      src="https://images.unsplash.com/photo-1599661046289-e31897846e41?w=1200&auto=format&fit=crop&q=80"
      alt="Incredible India Palaces & Forts"
      class="auth-bg-img"
    >
    <div class="auth-sidebar-overlay">
      <div>
        <a href="index.php" class="navbar-brand" style="font-size: 1.6rem;">
          <div class="brand-icon">🇮🇳</div>
          Globe<span>Trotter</span>
        </a>
        <span class="badge badge-gold" style="margin-top: 1rem;">🪷 Discover Incredible India</span>
      </div>

      <div>
        <h2 style="font-size: 2.4rem; font-weight: 800; line-height: 1.2; margin-bottom: 1rem; color: #fff;">
          Your Gateway to <br><span class="text-gradient">36+ Curated Indian Adventures</span>
        </h2>
        <p style="color: rgba(255, 255, 255, 0.85); font-size: 1.05rem; margin-bottom: 2rem; max-width: 440px;">
          Plan multi-city royal circuits, track expenses in Indian Rupees (₹), and share breathtaking travel stories.
        </p>

        <div class="auth-feature-pill">
          <span>🏰</span>
          <span><strong>Royal Heritage:</strong> Forts of Rajasthan &amp; Ghats of Varanasi</span>
        </div>
        <div class="auth-feature-pill">
          <span>🏔️</span>
          <span><strong>Himalayan Expeditions:</strong> Ladakh, Manali &amp; Rishikesh</span>
        </div>
        <div class="auth-feature-pill">
          <span>🌴</span>
          <span><strong>Tropical Escapes:</strong> Kerala Houseboats &amp; Goa Beaches</span>
        </div>
      </div>

      <div style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.6);">
        🌟 Join over 50,000+ passionate explorers planning trips across India
      </div>
    </div>
  </div>

  <!-- Right Side: Registration Form -->
  <div class="auth-content">
    <div class="auth-box">
      <div style="margin-bottom: 2rem;">
        <span class="badge badge-teal" style="margin-bottom: 0.75rem;">Create Account</span>
        <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.4rem;">Start Your Journey</h1>
        <p class="text-muted" style="font-size: 0.95rem;">Join GlobeTrotter today to plan your dream trips.</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error">
          <span>⚠️</span>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form id="registerForm" method="POST" action="register.php">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="input-float">
            <input type="text" name="first_name" id="first_name" placeholder=" " value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
            <label for="first_name">First Name *</label>
          </div>
          <div class="input-float">
            <input type="text" name="last_name" id="last_name" placeholder=" " value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
            <label for="last_name">Last Name *</label>
          </div>
        </div>

        <div class="input-float">
          <input type="email" name="email" id="email" placeholder=" " value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autocomplete="email">
          <label for="email">Email Address *</label>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label" for="countrySelect">Country</label>
            <select name="country_id" id="countrySelect" required>
              <?php while ($c = $countries->fetch_assoc()): ?>
                <option value="<?php echo $c['id']; ?>" data-code="<?php echo $c['phone_code']; ?>" <?php echo ($c['id'] == 1) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($c['name']); ?> (+<?php echo htmlspecialchars($c['phone_code']); ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="citySelect">Home City</label>
            <select name="city_id" id="citySelect">
              <option value="" disabled selected>-- Select City --</option>
              <?php while ($ct = $defaultCities->fetch_assoc()): ?>
                <option value="<?php echo $ct['id']; ?>">
                  <?php echo htmlspecialchars($ct['name']) . ($ct['state'] ? ' (' . htmlspecialchars($ct['state']) . ')' : ''); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>

        <input type="hidden" name="phone_code" id="phoneCode" value="91">

        <div class="input-float">
          <input type="text" name="phone_number" id="phoneNumber" placeholder=" " value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>" placeholder="9876543210">
          <label for="phoneNumber">Phone Number (10 digits)</label>
        </div>

        <div class="input-float">
          <input type="password" name="password" id="password" placeholder=" " required autocomplete="new-password">
          <label for="password">Choose Password (min 6 characters) *</label>
        </div>

        <div class="input-float">
          <textarea name="extra_info" id="extra_info" placeholder=" " rows="2"><?php echo htmlspecialchars($_POST['extra_info'] ?? ''); ?></textarea>
          <label for="extra_info">Travel Bio / Favorite Destinations</label>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 0.5rem;">
          <span>Register &amp; Start Exploring 🚀</span>
        </button>
      </form>

      <div style="text-align: center; margin-top: 1.75rem; font-size: 0.92rem; color: var(--text-muted);">
        Already have an account? <a href="login.php" style="font-weight: 700; color: var(--primary);">Sign In Here →</a>
      </div>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="assets/js/api.js"></script>
</body>
</html>
