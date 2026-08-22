<?php
// ============================================================
// GlobeTrotter — Shared Header / Navbar
// Usage: include 'includes/header.php'; at the top of each page
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin    = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName   = $_SESSION['first_name'] ?? 'User';
$userId     = $_SESSION['user_id'] ?? null;

// Detect current file for active nav highlighting
$currentFile = basename($_SERVER['PHP_SELF']);

function navLink(string $href, string $label, string $current): string {
    $active = basename($href) === $current ? 'active' : '';
    return "<a href=\"{$href}\" class=\"nav-link {$active}\">{$label}</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="GlobeTrotter — Plan multi-city trips, track budgets, and share your adventures with the world.">
  <title><?php echo $pageTitle ?? 'GlobeTrotter'; ?></title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><text y='26' font-size='28'>🌍</text></svg>">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo isset($cssPath) ? $cssPath : 'assets/css/style.css'; ?>">
  <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<nav class="navbar">
  <a href="<?php echo $isLoggedIn ? 'index.php' : 'login.php'; ?>" class="navbar-brand">
    <div class="brand-icon">🌍</div>
    Globe<span>Trotter</span>
  </a>

  <div class="navbar-nav" id="navMenu">
    <?php if ($isLoggedIn): ?>
      <?php echo navLink('index.php',          '🏠 Home',        $currentFile); ?>
      <?php echo navLink('my_trips.php',        '🗺️ My Trips',   $currentFile); ?>
      <?php echo navLink('city_search.php',     '🔭 Explore',     $currentFile); ?>
      <?php echo navLink('budget.php',          '💰 Budget',      $currentFile); ?>
      <?php echo navLink('community.php',       '🌐 Community',   $currentFile); ?>
      <?php echo navLink('calendar.php',        '📅 Calendar',    $currentFile); ?>
      <?php if ($isAdmin): ?>
        <?php echo navLink('admin.php',         '⚙️ Admin',       $currentFile); ?>
      <?php endif; ?>
    <?php else: ?>
      <?php echo navLink('login.php',    '🔑 Login',    $currentFile); ?>
      <?php echo navLink('register.php', '✨ Sign Up',  $currentFile); ?>
    <?php endif; ?>
  </div>

  <div class="navbar-right">
    <?php if ($isLoggedIn): ?>
      <a href="create_trip.php" class="btn btn-primary btn-sm">+ Plan Trip</a>
      <a href="profile.php" class="avatar-btn" title="Your Profile">
        <img
          src="<?php echo htmlspecialchars($_SESSION['photo_url'] ?? ''); ?>"
          alt="<?php echo htmlspecialchars($userName); ?>"
          onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=6C63FF&color=fff&bold=true'"
        >
      </a>
    <?php else: ?>
      <a href="login.php" class="btn btn-ghost btn-sm">Login</a>
      <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
    <?php endif; ?>
    <button class="hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
