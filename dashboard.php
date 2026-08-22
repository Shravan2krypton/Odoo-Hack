<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db_connect.php';

$userId = require_auth();

// Fetch user profile
$userStmt = $conn->prepare("SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.photo_url, u.extra_info, 
    c.name AS city_name, co.name AS country_name 
    FROM users u 
    LEFT JOIN cities c ON u.city_id = c.id 
    LEFT JOIN countries co ON u.country_id = co.id 
    WHERE u.id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// User Trips Count & Stats
$statsStmt = $conn->prepare("SELECT 
    COUNT(*) as total_trips,
    SUM(CASE WHEN status = 'planned' THEN 1 ELSE 0 END) as planned_trips,
    SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as ongoing_trips,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_trips,
    COALESCE(SUM(total_budget), 0) as total_budget
    FROM trips WHERE user_id = ?");
$statsStmt->bind_param("i", $userId);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();

// User Total Expenses in ₹
$expenseStmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_spent FROM expenses WHERE trip_id IN (SELECT id FROM trips WHERE user_id = ?)");
$expenseStmt->bind_param("i", $userId);
$expenseStmt->execute();
$expenseData = $expenseStmt->get_result()->fetch_assoc();
$totalSpent = $expenseData['total_spent'] ?? 0;

// Fetch all trips for user with city and region info
$tripsStmt = $conn->prepare("SELECT t.id, t.name, t.description, t.start_date, t.end_date, t.cover_photo, t.total_budget, t.status, t.is_public, t.share_slug, 
    c.name AS city_name, c.state AS city_state 
    FROM trips t 
    LEFT JOIN cities c ON t.city_id = c.id 
    WHERE t.user_id = ? 
    ORDER BY t.start_date ASC");
$tripsStmt->bind_param("i", $userId);
$tripsStmt->execute();
$allTrips = $tripsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Categorize trips
$plannedTrips   = array_filter($allTrips, fn($t) => $t['status'] === 'planned');
$ongoingTrips   = array_filter($allTrips, fn($t) => $t['status'] === 'ongoing');
$completedTrips = array_filter($allTrips, fn($t) => $t['status'] === 'completed');

// Fetch recent expenses
$recentExpenses = $conn->query("SELECT e.id, e.amount, e.category, e.expense_date, e.note, t.name as trip_name 
    FROM expenses e 
    JOIN trips t ON e.trip_id = t.id 
    WHERE t.user_id = {$userId} 
    ORDER BY e.expense_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Travel Dashboard";
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container">
    
    <!-- Welcome Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem; margin-bottom:2.5rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border);">
      <div style="display:flex; align-items:center; gap:1.25rem;">
        <img
          src="<?php echo !empty($user['photo_url']) ? htmlspecialchars($user['photo_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['first_name'] . ' ' . $user['last_name']) . '&background=6366f1&color=fff&bold=true'; ?>"
          alt="<?php echo htmlspecialchars($user['first_name']); ?>"
          style="width:68px; height:68px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); box-shadow:0 0 20px var(--primary-glow);"
        >
        <div>
          <div style="display:flex; align-items:center; gap:0.5rem;">
            <h1 style="font-size:1.85rem; font-weight:800;">Namaste, <?php echo htmlspecialchars($user['first_name']); ?>! 🙏</h1>
            <span class="badge badge-gold">Explorer</span>
          </div>
          <p class="text-muted" style="font-size:0.9rem;">
            <?php echo htmlspecialchars($user['city_name'] ?? 'India'); ?> • Welcome to your travel command center
          </p>
        </div>
      </div>

      <div style="display:flex; gap:0.75rem;">
        <a href="create_trip.php" class="btn btn-primary">+ Plan New Journey</a>
        <a href="city_search.php" class="btn btn-secondary">Explore Places 🧭</a>
      </div>
    </div>

    <!-- Travel Stats KPI Grid -->
    <div class="grid grid-4" style="margin-bottom:2.5rem;">
      <div class="card card-gradient" style="padding:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
          <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Total Journeys</span>
          <span style="font-size:1.4rem;">🗺️</span>
        </div>
        <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:#fff;">
          <?php echo $stats['total_trips'] ?? 0; ?>
        </div>
        <div style="font-size:0.8rem; color:var(--teal); font-weight:600; margin-top:0.25rem;">
          <?php echo count($plannedTrips); ?> upcoming • <?php echo count($ongoingTrips); ?> ongoing
        </div>
      </div>

      <div class="card card-gradient" style="padding:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
          <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Active Trips</span>
          <span style="font-size:1.4rem;">🚀</span>
        </div>
        <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:var(--teal);">
          <?php echo count($ongoingTrips); ?>
        </div>
        <div style="font-size:0.8rem; color:var(--text-dim); margin-top:0.25rem;">Currently in motion</div>
      </div>

      <div class="card card-gradient" style="padding:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
          <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Completed</span>
          <span style="font-size:1.4rem;">✨</span>
        </div>
        <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:var(--gold);">
          <?php echo count($completedTrips); ?>
        </div>
        <div style="font-size:0.8rem; color:var(--text-dim); margin-top:0.25rem;">Memories created</div>
      </div>

      <div class="card card-gradient" style="padding:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
          <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Total Expenses</span>
          <span style="font-size:1.4rem;">💰</span>
        </div>
        <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:var(--accent);">
          ₹<?php echo number_format($totalSpent); ?>
        </div>
        <div style="font-size:0.8rem; color:var(--text-dim); margin-top:0.25rem;">
          Budget: ₹<?php echo number_format($stats['total_budget'] ?? 0); ?>
        </div>
      </div>
    </div>

    <!-- Main Content Area: Trips Tabs & Right Sidebar -->
    <div style="display:grid; grid-template-columns: 2.2fr 1fr; gap:2rem; align-items:start;">
      
      <!-- Left Column: User Trips Manager -->
      <div>
        <div class="tabs">
          <button class="tab-btn active" onclick="showTab(event, 'tabPlanned')">
            <span>📅</span>
            <span>Planned Trips (<?php echo count($plannedTrips); ?>)</span>
          </button>
          <button class="tab-btn" onclick="showTab(event, 'tabOngoing')">
            <span>🚀</span>
            <span>Ongoing (<?php echo count($ongoingTrips); ?>)</span>
          </button>
          <button class="tab-btn" onclick="showTab(event, 'tabCompleted')">
            <span>✨</span>
            <span>Completed (<?php echo count($completedTrips); ?>)</span>
          </button>
        </div>

        <!-- Planned Trips Tab -->
        <div id="tabPlanned" class="tab-content" style="display:block;">
          <?php if (empty($plannedTrips)): ?>
            <div class="card" style="text-align:center; padding:3.5rem 1.5rem;">
              <span style="font-size:3rem; display:block; margin-bottom:1rem;">🏔️</span>
              <h3 style="margin-bottom:0.5rem;">No planned journeys yet</h3>
              <p class="text-muted" style="margin-bottom:1.5rem; max-width:400px; margin-left:auto; margin-right:auto;">
                Ready to explore Rajasthan, Ladakh, Kerala or Himachal? Create your first trip itinerary!
              </p>
              <a href="create_trip.php" class="btn btn-primary">+ Plan a New Journey</a>
            </div>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:1.25rem;">
              <?php foreach ($plannedTrips as $trip): ?>
                <?php renderTripCard($trip); ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Ongoing Trips Tab -->
        <div id="tabOngoing" class="tab-content" style="display:none;">
          <?php if (empty($ongoingTrips)): ?>
            <div class="card" style="text-align:center; padding:3rem 1.5rem;">
              <span style="font-size:3rem; display:block; margin-bottom:1rem;">🧳</span>
              <h3>No active journeys at the moment</h3>
              <p class="text-muted">When your trip dates arrive, they will appear here live.</p>
            </div>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:1.25rem;">
              <?php foreach ($ongoingTrips as $trip): ?>
                <?php renderTripCard($trip); ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Completed Trips Tab -->
        <div id="tabCompleted" class="tab-content" style="display:none;">
          <?php if (empty($completedTrips)): ?>
            <div class="card" style="text-align:center; padding:3rem 1.5rem;">
              <span style="font-size:3rem; display:block; margin-bottom:1rem;">📸</span>
              <h3>No completed journeys yet</h3>
              <p class="text-muted">Completed trips with past memories will be archived here.</p>
            </div>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:1.25rem;">
              <?php foreach ($completedTrips as $trip): ?>
                <?php renderTripCard($trip); ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right Column: Quick Travel Shortcuts & Recent Expenses -->
      <div style="display:flex; flex-direction:column; gap:1.5rem;">
        
        <!-- Quick Actions Card -->
        <div class="card" style="padding:1.5rem;">
          <h3 style="font-size:1.15rem; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
            <span>⚡</span><span>Quick Actions</span>
          </h3>
          <div style="display:flex; flex-direction:column; gap:0.6rem;">
            <a href="create_trip.php" class="btn btn-secondary" style="justify-content:flex-start;">
              <span>➕</span><span>Plan New Journey</span>
            </a>
            <a href="city_search.php" class="btn btn-secondary" style="justify-content:flex-start;">
              <span>🏛️</span><span>Browse Indian Cities</span>
            </a>
            <a href="activity_search.php" class="btn btn-secondary" style="justify-content:flex-start;">
              <span>🏄</span><span>Curated Activities</span>
            </a>
            <a href="budget.php" class="btn btn-secondary" style="justify-content:flex-start;">
              <span>💰</span><span>Manage Expenses (₹)</span>
            </a>
            <a href="calendar.php" class="btn btn-secondary" style="justify-content:flex-start;">
              <span>📅</span><span>Trip Calendar</span>
            </a>
          </div>
        </div>

        <!-- Recent Expenses in INR -->
        <div class="card" style="padding:1.5rem;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1.15rem;">Recent Expenses</h3>
            <a href="budget.php" style="font-size:0.8rem; font-weight:700; color:var(--primary);">View All →</a>
          </div>

          <?php if (empty($recentExpenses)): ?>
            <p class="text-muted" style="font-size:0.85rem;">No expenses logged yet.</p>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:0.75rem;">
              <?php foreach ($recentExpenses as $exp): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:0.6rem 0; border-bottom:1px solid var(--border); font-size:0.88rem;">
                  <div>
                    <div style="font-weight:600; color:#fff;"><?php echo htmlspecialchars($exp['note'] ?: ucfirst($exp['category'])); ?></div>
                    <div style="font-size:0.75rem; color:var(--text-dim);">
                      <?php echo htmlspecialchars($exp['trip_name']); ?> • <?php echo date('M d', strtotime($exp['expense_date'])); ?>
                    </div>
                  </div>
                  <div style="font-family:'Outfit',sans-serif; font-weight:800; color:var(--gold);">
                    ₹<?php echo number_format($exp['amount']); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
// Trip Card Render Helper
function renderTripCard($trip) {
    $statusClasses = [
        'planned'   => 'badge-primary',
        'ongoing'   => 'badge-teal',
        'completed' => 'badge-gold'
    ];
    $badgeClass = $statusClasses[$trip['status']] ?? 'badge-primary';
    $cover = $trip['cover_photo'] ?: 'https://images.unsplash.com/photo-1599661046289-e31897846e41?w=600';
?>
    <div class="card card-interactive" style="padding:0; overflow:hidden; display:flex; flex-direction:row; flex-wrap:wrap;">
      <div style="flex:1; min-width:200px; max-width:240px; height:180px; position:relative;">
        <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($trip['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
        <span class="badge <?php echo $badgeClass; ?>" style="position:absolute; top:0.75rem; left:0.75rem;">
          <?php echo ucfirst($trip['status']); ?>
        </span>
      </div>

      <div style="flex:2; min-width:280px; padding:1.25rem; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
          <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
              <h3 style="font-size:1.25rem; color:#fff; margin-bottom:0.25rem;"><?php echo htmlspecialchars($trip['name']); ?></h3>
              <div style="font-size:0.82rem; color:var(--teal); font-weight:600; margin-bottom:0.5rem;">
                📍 <?php echo htmlspecialchars($trip['city_name'] ?? 'India'); ?><?php echo $trip['city_state'] ? ', ' . htmlspecialchars($trip['city_state']) : ''; ?>
              </div>
            </div>
            <div style="font-family:'Outfit',sans-serif; font-size:1.15rem; font-weight:800; color:var(--gold);">
              ₹<?php echo number_format($trip['total_budget']); ?>
            </div>
          </div>

          <p style="font-size:0.86rem; color:var(--text-muted); line-height:1.4; margin-bottom:0.75rem;">
            <?php echo htmlspecialchars(substr($trip['description'] ?? '', 0, 120)) . (strlen($trip['description'] ?? '') > 120 ? '...' : ''); ?>
          </p>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; padding-top:0.75rem; border-top:1px solid var(--border); font-size:0.82rem; color:var(--text-dim);">
          <div>
            🗓️ <?php echo date('M d, Y', strtotime($trip['start_date'])); ?> → <?php echo date('M d, Y', strtotime($trip['end_date'])); ?>
          </div>

          <div style="display:flex; gap:0.5rem;">
            <a href="itinerary_builder.php?trip_id=<?php echo $trip['id']; ?>" class="btn btn-secondary btn-sm">Itinerary 📝</a>
            <a href="budget.php?trip_id=<?php echo $trip['id']; ?>" class="btn btn-secondary btn-sm">Budget (₹)</a>
            <a href="itinerary_view.php?trip_id=<?php echo $trip['id']; ?>" class="btn btn-primary btn-sm">View →</a>
          </div>
        </div>
      </div>
    </div>
<?php
}
?>

<script>
function showTab(e, tabId) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
  e.currentTarget.classList.add('active');
  document.getElementById(tabId).style.display = 'block';
}
</script>

<?php include 'includes/footer.php'; ?>
