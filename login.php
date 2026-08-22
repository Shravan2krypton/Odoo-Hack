<?php
include 'includes/db_connect.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$redirect = $_GET['redirect'] ?? 'dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'dashboard.php';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, password_hash, role, photo_url FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name']  = $user['last_name'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['photo_url']  = $user['photo_url'] ?? '';

            // Clean redirect URL
            $safeRedirect = urldecode($redirect);
            if (empty($safeRedirect) || strpos($safeRedirect, 'http') === 0 || strpos($safeRedirect, '//') === 0) {
                $safeRedirect = 'dashboard.php';
            }

            header("Location: {$safeRedirect}");
            exit();
        } else {
            $error = 'Invalid email or password. Please verify your credentials.';
        }
    }
}

$authRequired = isset($_GET['auth']);
$registered   = isset($_GET['registered']);
$loggedout    = isset($_GET['loggedout']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — GlobeTrotter India</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><text y='26' font-size='28'>🇮🇳</text></svg>">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .demo-pill {
      cursor: pointer;
      padding: 0.5rem 0.85rem;
      background: rgba(255, 255, 255, 0.05);
      border: 1px dashed var(--border-hover);
      border-radius: var(--radius-sm);
      font-size: 0.82rem;
      color: var(--text-muted);
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0.5rem;
    }
    .demo-pill:hover {
      background: rgba(99, 102, 241, 0.15);
      border-color: var(--primary);
      color: #fff;
    }
  </style>
</head>
<body>

<div class="auth-wrapper">
  <!-- Left Side: Visual Hero -->
  <div class="auth-sidebar">
    <img
      src="https://images.unsplash.com/photo-1564507592333-c60657eea523?w=1200&auto=format&fit=crop&q=80"
      alt="Taj Mahal Agra India"
      class="auth-bg-img"
    >
    <div class="auth-sidebar-overlay">
      <div>
        <a href="index.php" class="navbar-brand" style="font-size: 1.6rem;">
          <div class="brand-icon">🇮🇳</div>
          Globe<span>Trotter</span>
        </a>
        <span class="badge badge-gold" style="margin-top: 1rem;">👑 Welcome Back</span>
      </div>

      <div>
        <h2 style="font-size: 2.4rem; font-weight: 800; line-height: 1.2; margin-bottom: 1rem; color: #fff;">
          Resume Your Journey Through <br><span class="text-sunset">Incredible India</span>
        </h2>
        <p style="color: rgba(255, 255, 255, 0.85); font-size: 1.05rem; margin-bottom: 2rem; max-width: 440px;">
          Access your saved multi-city itineraries, budget breakdowns in INR, and interactive travel timeline.
        </p>

        <div class="auth-feature-pill">
          <span>📊</span>
          <span><strong>Smart Dashboard:</strong> Real-time travel metrics &amp; expense graphs</span>
        </div>
        <div class="auth-feature-pill">
          <span>📅</span>
          <span><strong>Timeline Calendar:</strong> Daily schedule &amp; stop reminders</span>
        </div>
        <div class="auth-feature-pill">
          <span>🌐</span>
          <span><strong>Community Sharing:</strong> Connect with fellow Indian explorers</span>
        </div>
      </div>

      <div style="font-size: 0.85rem; color: rgba(255, 255, 255, 0.6);">
        🌏 Explore Rajasthan, Ladakh, Kerala, Goa, Varanasi &amp; beyond.
      </div>
    </div>
  </div>

  <!-- Right Side: Sign In Form -->
  <div class="auth-content">
    <div class="auth-box">
      <div style="margin-bottom: 2rem;">
        <span class="badge badge-primary" style="margin-bottom: 0.75rem;">Welcome Back</span>
        <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.4rem;">Sign in to GlobeTrotter</h1>
        <p class="text-muted" style="font-size: 0.95rem;">Enter your credentials to continue planning.</p>
      </div>

      <?php if ($authRequired): ?>
        <div class="alert alert-warning">
          <span>🔒</span>
          <span>Authentication required. Please sign in to access that page.</span>
        </div>
      <?php endif; ?>

      <?php if ($registered): ?>
        <div class="alert alert-success">
          <span>🎉</span>
          <span>Account created successfully! Please sign in below.</span>
        </div>
      <?php endif; ?>

      <?php if ($loggedout): ?>
        <div class="alert alert-info">
          <span>👋</span>
          <span>You have been safely logged out. See you soon!</span>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error">
          <span>❌</span>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php" id="loginForm">
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

        <div class="input-float">
          <input type="email" name="email" id="email" placeholder=" " value="<?php echo htmlspecialchars($_POST['email'] ?? 'priya@example.com'); ?>" required autocomplete="email">
          <label for="email">Email Address</label>
        </div>

        <div class="input-float">
          <input type="password" name="password" id="password" placeholder=" " value="password" required autocomplete="current-password">
          <label for="password">Password</label>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 0.5rem;" id="loginSubmitBtn">
          <span>Sign In →</span>
        </button>
      </form>

      <!-- Quick Demo Credentials for Fast Testing -->
      <div style="margin-top: 2rem; padding: 1.25rem; background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius);">
        <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.5px;">
          ⚡ Quick Demo Logins (Click to autofill)
        </div>
        <div class="demo-pill" onclick="fillDemo('priya@example.com', 'password')">
          <span>👩 <strong>Priya Patel</strong> (Explorer — Rajasthan / Kerala)</span>
          <span class="badge badge-teal">Fill</span>
        </div>
        <div class="demo-pill" onclick="fillDemo('rohan@example.com', 'password')">
          <span>👨 <strong>Rohan Verma</strong> (Ladakh Biker)</span>
          <span class="badge badge-teal">Fill</span>
        </div>
        <div class="demo-pill" onclick="fillDemo('admin@globetrotter.in', 'password')">
          <span>🛡️ <strong>Aarav Sharma</strong> (Administrator)</span>
          <span class="badge badge-gold">Admin</span>
        </div>
      </div>

      <div style="text-align: center; margin-top: 1.75rem; font-size: 0.92rem; color: var(--text-muted);">
        Don't have an account? <a href="register.php" style="font-weight: 700; color: var(--accent);">Create Account Free →</a>
      </div>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="assets/js/api.js"></script>
<script>
  function fillDemo(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
    Toast.show('Demo credentials filled: ' + email, 'info');
  }
</script>
</body>
</html>
