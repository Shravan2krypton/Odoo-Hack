<?php
include 'includes/db_connect.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, first_name, password_hash, role, photo_url FROM users WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $row['id'];
        $_SESSION['first_name']= $row['first_name'];
        $_SESSION['role']      = $row['role'];
        $_SESSION['photo_url'] = $row['photo_url'] ?? '';
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid email or password. Please try again.';
    }
}

$registered = isset($_GET['registered']);
$loggedout  = isset($_GET['loggedout']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Sign in to GlobeTrotter — your personalized travel planning platform.">
  <title>Login — GlobeTrotter</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><text y='26' font-size='28'>🌍</text></svg>">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body { display: flex; min-height: 100vh; overflow: hidden; }
    .auth-left {
      flex: 1;
      display: none;
      background: linear-gradient(135deg, #0D0F1A 0%, #1a1040 100%);
      position: relative;
      overflow: hidden;
      align-items: center;
      justify-content: center;
    }
    @media (min-width: 900px) { .auth-left { display: flex; } }
    .auth-left-content { position: relative; z-index: 1; text-align: center; padding: 3rem; }
    .auth-globe { font-size: 6rem; display: block; animation: spin 20s linear infinite; }
    .auth-left h2 { font-size: 2rem; font-weight: 800; margin: 1rem 0 0.5rem; }
    .auth-left p { color: var(--text-muted); max-width: 320px; margin: 0 auto; }
    .auth-features { margin-top: 2rem; display: flex; flex-direction: column; gap: 0.75rem; text-align: left; }
    .auth-feature {
      display: flex; align-items: center; gap: 0.75rem;
      background: rgba(255,255,255,0.05); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 0.75rem 1rem;
    }
    .auth-feature span { font-size: 0.9rem; color: var(--text-muted); }
    .auth-right {
      width: 100%;
      max-width: 480px;
      background: var(--surface);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      overflow-y: auto;
    }
    .auth-form-wrap { width: 100%; max-width: 380px; }
    .auth-brand { display: flex; align-items: center; gap: 0.6rem; font-size: 1.6rem; font-weight: 800; margin-bottom: 2.5rem; }
    .auth-brand .brand-icon { width: 42px; height: 42px; background: linear-gradient(135deg, var(--primary), var(--teal)); border-radius: var(--radius-sm); display:grid;place-items:center;font-size:1.2rem; }
    .auth-brand span { color: var(--primary); }
    .auth-form-wrap h1 { font-size: 1.7rem; margin-bottom: 0.35rem; }
    .auth-form-wrap > p { color: var(--text-muted); margin-bottom: 2rem; font-size: 0.95rem; }
    .form-footer { text-align: center; margin-top: 1.5rem; font-size: 0.88rem; color: var(--text-muted); }
    .form-footer a { color: var(--primary); font-weight: 600; }
    .forgot-link { display: block; text-align: right; font-size: 0.8rem; color: var(--primary); margin-top: -0.75rem; margin-bottom: 1.25rem; }
    .divider-line { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; color: var(--text-dim); font-size: 0.8rem; }
    .divider-line::before, .divider-line::after { content:''; flex:1; border-top:1px solid var(--border); }
  </style>
</head>
<body>
  <!-- Left decorative panel -->
  <div class="auth-left">
    <div class="particles-wrap" id="particles"></div>
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 70% 70% at 50% 50%,rgba(108,99,255,0.2) 0%,transparent 70%);"></div>
    <div class="auth-left-content">
      <span class="auth-globe">🌍</span>
      <h2>Your World,<br>Your Way</h2>
      <p>Plan multi-city itineraries, track budgets, and share your adventures with the world.</p>
      <div class="auth-features">
        <div class="auth-feature"><span>🗺️</span><span>Multi-city itinerary builder</span></div>
        <div class="auth-feature"><span>💰</span><span>Smart budget & cost tracking</span></div>
        <div class="auth-feature"><span>🌐</span><span>Community trip sharing</span></div>
        <div class="auth-feature"><span>📅</span><span>Interactive travel calendar</span></div>
      </div>
    </div>
  </div>

  <!-- Right login form -->
  <div class="auth-right">
    <div class="auth-form-wrap">
      <div class="auth-brand">
        <div class="brand-icon">🌍</div>
        Globe<span>Trotter</span>
      </div>
      <h1>Welcome back</h1>
      <p>Sign in to continue planning your adventures.</p>

      <?php if ($registered): ?>
        <div class="alert alert-success">🎉 Account created! You can now sign in.</div>
      <?php endif; ?>
      <?php if ($loggedout): ?>
        <div class="alert alert-info">👋 You've been safely logged out.</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="" id="loginForm">
        <div class="input-float">
          <input type="email" name="email" id="email" placeholder=" " value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autocomplete="email">
          <label for="email">Email Address</label>
        </div>
        <div class="input-float">
          <input type="password" name="password" id="password" placeholder=" " required autocomplete="current-password">
          <label for="password">Password</label>
        </div>
        <a href="#" class="forgot-link">Forgot password?</a>
        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
          <span class="btn-text">Sign In →</span>
          <div class="spinner" id="btnSpinner" style="width:20px;height:20px;border-width:2px;display:none;"></div>
        </button>
      </form>

      <div class="divider-line">or continue with</div>

      <div class="form-footer">
        Don't have an account? <a href="register.php">Create one free →</a>
      </div>
      <div style="margin-top:0.75rem;text-align:center;font-size:0.78rem;color:var(--text-dim);">
        Demo login: <strong>jane@example.com</strong> / <strong>password</strong>
      </div>
    </div>
  </div>

  <script src="assets/js/api.js"></script>
  <script>
    document.getElementById('loginForm').addEventListener('submit', function() {
      const btn  = document.getElementById('submitBtn');
      const text = btn.querySelector('.btn-text');
      const spin = document.getElementById('btnSpinner');
      text.style.display = 'none';
      spin.style.display = 'block';
      btn.disabled = true;
    });
  </script>
</body>
</html>
