<?php
// ============================================================
// GlobeTrotter India — Shared Header & Navigation
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin    = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName   = $_SESSION['first_name'] ?? 'Traveler';
$userId     = $_SESSION['user_id'] ?? null;
$userPhoto  = $_SESSION['photo_url'] ?? '';

// Detect current file for active link highlight
$currentFile = basename($_SERVER['PHP_SELF']);

function navItem(string $href, string $label, string $icon, string $current): string {
    $isActive = (basename($href) === $current) ? 'active' : '';
    return "<a href=\"{$href}\" class=\"nav-link {$isActive}\"><span>{$icon}</span><span>{$label}</span></a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="GlobeTrotter India — Discover incredible destinations, plan multi-city itineraries, and track travel budgets across India.">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | GlobeTrotter India' : 'GlobeTrotter India — Discover Incredible India'; ?></title>
  
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><text y='26' font-size='28'>🇮🇳</text></svg>">
  <link rel="stylesheet" href="assets/css/style.css">
  <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <div class="brand-icon">🇮🇳</div>
    Globe<span>Trotter</span>
  </a>

  <div class="navbar-nav" id="navMenu">
    <?php echo navItem('index.php', 'Explore', '🧭', $currentFile); ?>
    <?php echo navItem('city_search.php', 'Destinations', '🏛️', $currentFile); ?>
    <?php echo navItem('activity_search.php', 'Activities', '🏄', $currentFile); ?>
    <?php echo navItem('community.php', 'Community', '💬', $currentFile); ?>
    
    <?php if ($isLoggedIn): ?>
      <?php echo navItem('dashboard.php', 'Dashboard', '📊', $currentFile); ?>
      <?php echo navItem('my_trips.php', 'My Trips', '🗺️', $currentFile); ?>
      <?php echo navItem('budget.php', 'Budget (₹)', '💰', $currentFile); ?>
      <?php echo navItem('calendar.php', 'Calendar', '📅', $currentFile); ?>
      <?php if ($isAdmin): ?>
        <?php echo navItem('admin_dashboard.php', 'Admin', '⚙️', $currentFile); ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="navbar-right">
    <?php if ($isLoggedIn): ?>
      <a href="create_trip.php" class="btn btn-primary btn-sm">+ Plan Trip</a>
      <a href="profile.php" class="avatar-btn" title="View Profile">
        <img
          src="<?php echo !empty($userPhoto) ? htmlspecialchars($userPhoto) : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=6366f1&color=fff&bold=true'; ?>"
          alt="<?php echo htmlspecialchars($userName); ?>"
        >
        <span><?php echo htmlspecialchars($userName); ?></span>
      </a>
      <a href="logout.php" class="btn btn-ghost btn-sm" title="Logout" style="color:var(--danger);">Exit ↗</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-ghost btn-sm">Sign In</a>
      <a href="register.php" class="btn btn-sunset btn-sm">Join Free →</a>
    <?php endif; ?>
    <button class="hamburger" id="hamburger" aria-label="Toggle Menu">☰</button>
  </div>
</nav>
