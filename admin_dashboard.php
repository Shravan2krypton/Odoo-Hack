<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db_connect.php';

// Strict Admin Auth Guard
$userId = require_admin();

$message = '';

// Handle Role Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_role') {
    $targetUserId = intval($_POST['target_user_id']);
    $newRole      = $_POST['new_role'] === 'admin' ? 'admin' : 'user';
    if ($targetUserId !== $userId) { // Prevent self-demotion
        $conn->query("UPDATE users SET role = '{$newRole}' WHERE id = {$targetUserId}");
        $message = "User role updated to {$newRole}.";
    }
}

// Handle Add Destination City
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_city') {
    $name          = trim($_POST['name'] ?? '');
    $state         = trim($_POST['state'] ?? '');
    $region_id     = intval($_POST['region_id'] ?? 1);
    $cost_index    = floatval($_POST['cost_index'] ?? 1.0);
    $avg_cost      = intval($_POST['avg_daily_cost'] ?? 2500);
    $image_url     = trim($_POST['image_url'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $best_time     = trim($_POST['best_time_to_visit'] ?? 'All Year');

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO cities (country_id, region_id, name, state, cost_index, avg_daily_cost, image_url, description, best_time_to_visit) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdisss", $region_id, $name, $state, $cost_index, $avg_cost, $image_url, $description, $best_time);
        if ($stmt->execute()) {
            $message = "Destination '{$name}' added to database!";
        }
    }
}

// Handle Delete User
if (isset($_POST['delete_user_id'])) {
    $delUserId = intval($_POST['delete_user_id']);
    if ($delUserId !== $userId) {
        $conn->query("DELETE FROM users WHERE id = {$delUserId}");
        $message = "User account removed.";
    }
}

// Metric Statistics
$totalUsers = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalTrips = $conn->query("SELECT COUNT(*) AS c FROM trips")->fetch_assoc()['c'];
$totalCities = $conn->query("SELECT COUNT(*) AS c FROM cities")->fetch_assoc()['c'];
$totalPosts = $conn->query("SELECT COUNT(*) AS c FROM community_posts")->fetch_assoc()['c'];

// Fetch Users List
$usersList = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM trips WHERE user_id = u.id) AS trips_count 
    FROM users u ORDER BY u.created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Fetch Cities List
$citiesList = $conn->query("SELECT c.*, r.name AS region_name FROM cities c LEFT JOIN regions r ON c.region_id = r.id ORDER BY c.name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch Regions for Add Form
$regions = $conn->query("SELECT id, name FROM regions ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch Audit Logs
$auditLogs = $conn->query("SELECT a.*, u.first_name, u.email FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Admin Command Center";
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container">
    
    <!-- Top Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem; margin-bottom:2.5rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border);">
      <div>
        <span class="badge badge-gold" style="margin-bottom:0.4rem;">System Administration</span>
        <h1 style="font-size:2.2rem; font-weight:800;">Platform Command Center</h1>
        <p class="text-muted">Manage Indian destinations, review explorer profiles, and monitor system metrics.</p>
      </div>

      <div style="display:flex; gap:0.75rem;">
        <a href="dashboard.php" class="btn btn-secondary btn-sm">User Dashboard</a>
        <a href="index.php" class="btn btn-primary btn-sm">View Live Site 🌐</a>
      </div>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-success">
        <span>✨</span>
        <span><?php echo htmlspecialchars($message); ?></span>
      </div>
    <?php endif; ?>

    <!-- KPI Metric Cards -->
    <div class="grid grid-4" style="margin-bottom:2.5rem;">
      <div class="card card-gradient" style="padding:1.5rem;">
        <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Registered Users</span>
        <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:#fff; margin-top:0.25rem;">
          <?php echo $totalUsers; ?>
        </div>
      </div>

      <div class="card card-gradient" style="padding:1.5rem;">
        <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Trips Created</span>
        <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:var(--teal); margin-top:0.25rem;">
          <?php echo $totalTrips; ?>
        </div>
      </div>

      <div class="card card-gradient" style="padding:1.5rem;">
        <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Active Destinations</span>
        <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:var(--gold); margin-top:0.25rem;">
          <?php echo $totalCities; ?>
        </div>
      </div>

      <div class="card card-gradient" style="padding:1.5rem;">
        <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Community Posts</span>
        <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:var(--accent); margin-top:0.25rem;">
          <?php echo $totalPosts; ?>
        </div>
      </div>
    </div>

    <!-- Two Column Layout: User Management & Destination Manager -->
    <div style="display:grid; grid-template-columns: 1.8fr 1.2fr; gap:2rem; align-items:start; margin-bottom:2.5rem;">
      
      <!-- Users Table -->
      <div class="card" style="padding:1.75rem;">
        <h3 style="font-size:1.3rem; margin-bottom:1.25rem;">User Management (<?php echo count($usersList); ?>)</h3>
        
        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
            <thead>
              <tr style="border-bottom:1px solid var(--border); color:var(--text-dim); text-align:left;">
                <th style="padding:0.75rem 0.5rem;">User</th>
                <th style="padding:0.75rem 0.5rem;">Email</th>
                <th style="padding:0.75rem 0.5rem;">Role</th>
                <th style="padding:0.75rem 0.5rem;">Trips</th>
                <th style="padding:0.75rem 0.5rem; text-align:center;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($usersList as $u): ?>
                <tr style="border-bottom:1px solid var(--border);">
                  <td style="padding:0.75rem 0.5rem; font-weight:600; color:#fff;">
                    <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>
                  </td>
                  <td style="padding:0.75rem 0.5rem; color:var(--text-muted);">
                    <?php echo htmlspecialchars($u['email']); ?>
                  </td>
                  <td style="padding:0.75rem 0.5rem;">
                    <span class="badge <?php echo ($u['role'] === 'admin') ? 'badge-gold' : 'badge-primary'; ?>" style="font-size:0.68rem;">
                      <?php echo $u['role']; ?>
                    </span>
                  </td>
                  <td style="padding:0.75rem 0.5rem; font-weight:700; color:var(--teal);">
                    <?php echo $u['trips_count']; ?>
                  </td>
                  <td style="padding:0.75rem 0.5rem; text-align:center;">
                    <?php if ($u['id'] !== $userId): ?>
                      <form method="POST" action="admin_dashboard.php" onsubmit="return confirm('Delete this user?');" style="display:inline;">
                        <input type="hidden" name="delete_user_id" value="<?php echo $u['id']; ?>">
                        <button type="submit" style="background:none; border:none; color:var(--danger); cursor:pointer;" title="Delete User">🗑️</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add New Indian Destination Form -->
      <div class="card" style="padding:1.75rem;">
        <span class="badge badge-teal" style="margin-bottom:0.4rem;">Expansion</span>
        <h3 style="font-size:1.3rem; margin-bottom:1.25rem;">+ Add New Destination City</h3>

        <form method="POST" action="admin_dashboard.php">
          <input type="hidden" name="action" value="add_city">

          <div class="form-group">
            <label class="form-label">City Name *</label>
            <input type="text" name="name" placeholder="e.g. Rishikesh or Coorg" required>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label">State *</label>
              <input type="text" name="state" placeholder="e.g. Uttarakhand" required>
            </div>
            <div class="form-group">
              <label class="form-label">Region</label>
              <select name="region_id">
                <?php foreach ($regions as $reg): ?>
                  <option value="<?php echo $reg['id']; ?>"><?php echo htmlspecialchars($reg['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label">Avg Daily Cost (₹)</label>
              <input type="number" name="avg_daily_cost" placeholder="2500" value="2500">
            </div>
            <div class="form-group">
              <label class="form-label">Best Season</label>
              <input type="text" name="best_time_to_visit" placeholder="Oct - Mar" value="Oct - Mar">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Image URL</label>
            <input type="text" name="image_url" placeholder="https://images.unsplash.com/...">
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" rows="2" placeholder="Highlights of this destination..."></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <span>Publish City to Catalog 🚀</span>
          </button>
        </form>
      </div>

    </div>

    <!-- Audit Logs Section -->
    <div class="card" style="padding:1.75rem;">
      <h3 style="font-size:1.3rem; margin-bottom:1rem;">System Audit Trail</h3>
      <div style="display:flex; flex-direction:column; gap:0.5rem;">
        <?php foreach ($auditLogs as $log): ?>
          <div style="display:flex; justify-content:space-between; align-items:center; padding:0.5rem 0.75rem; background:rgba(13,19,34,0.6); border-radius:6px; font-size:0.85rem;">
            <div>
              <span class="badge badge-primary" style="font-size:0.7rem;"><?php echo htmlspecialchars($log['action']); ?></span>
              <span style="color:var(--text); margin-left:0.5rem;">Table: <code><?php echo htmlspecialchars($log['table_name']); ?></code></span>
              <span style="color:var(--text-dim); margin-left:0.5rem;">(User: <?php echo htmlspecialchars($log['first_name'] ?? 'System'); ?>)</span>
            </div>
            <span style="color:var(--text-dim); font-size:0.78rem;"><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
