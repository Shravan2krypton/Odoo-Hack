<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db_connect.php';

$userId = require_auth();

// Handle trip deletion
if (isset($_POST['delete_trip_id'])) {
    $deleteId = intval($_POST['delete_trip_id']);
    $delStmt = $conn->prepare("DELETE FROM trips WHERE id = ? AND user_id = ?");
    $delStmt->bind_param("ii", $deleteId, $userId);
    $delStmt->execute();
    header("Location: my_trips.php?deleted=1");
    exit();
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$searchQuery  = trim($_GET['q'] ?? '');

$sql = "SELECT t.id, t.name, t.description, t.start_date, t.end_date, t.cover_photo, t.total_budget, t.status, t.is_public, t.share_slug, 
    c.name AS city_name, c.state AS city_state,
    (SELECT COUNT(*) FROM itinerary_sections WHERE trip_id = t.id) AS stop_count,
    (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE trip_id = t.id) AS spent_amount
    FROM trips t 
    LEFT JOIN cities c ON t.city_id = c.id 
    WHERE t.user_id = ?";

$params = [$userId];
$types  = "i";

if (!empty($statusFilter)) {
    $sql .= " AND t.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($searchQuery)) {
    $sql .= " AND (t.name LIKE ? OR c.name LIKE ? OR c.state LIKE ?)";
    $like = "%{$searchQuery}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

$sql .= " ORDER BY t.start_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$trips = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = "My Trips & Itineraries";
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container">
    
    <!-- Top Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem; margin-bottom:2rem;">
      <div>
        <span class="badge badge-primary" style="margin-bottom:0.4rem;">Travel Organizer</span>
        <h1 style="font-size:2.2rem; font-weight:800;">My Trips &amp; Itineraries</h1>
        <p class="text-muted">Manage your planned routes, track stops, and monitor expenses across India.</p>
      </div>

      <a href="create_trip.php" class="btn btn-primary btn-lg">+ Plan New Journey</a>
    </div>

    <!-- Filter & Search Controls -->
    <div class="card" style="padding:1rem 1.5rem; margin-bottom:2rem;">
      <form method="GET" action="my_trips.php" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
          <a href="my_trips.php" class="btn btn-sm <?php echo empty($statusFilter) ? 'btn-primary' : 'btn-secondary'; ?>">All Trips (<?php echo count($trips); ?>)</a>
          <a href="my_trips.php?status=planned" class="btn btn-sm <?php echo ($statusFilter === 'planned') ? 'btn-primary' : 'btn-secondary'; ?>">Planned</a>
          <a href="my_trips.php?status=ongoing" class="btn btn-sm <?php echo ($statusFilter === 'ongoing') ? 'btn-primary' : 'btn-secondary'; ?>">Ongoing</a>
          <a href="my_trips.php?status=completed" class="btn btn-sm <?php echo ($statusFilter === 'completed') ? 'btn-primary' : 'btn-secondary'; ?>">Completed</a>
        </div>

        <div style="display:flex; gap:0.5rem; min-width:280px;">
          <input type="text" name="q" placeholder="Search by trip name or city..." value="<?php echo htmlspecialchars($searchQuery); ?>" style="padding:0.5rem 0.9rem; font-size:0.88rem;">
          <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        </div>
      </form>
    </div>

    <!-- Trips Grid -->
    <?php if (empty($trips)): ?>
      <div class="card" style="text-align:center; padding:4rem 2rem;">
        <span style="font-size:3.5rem; display:block; margin-bottom:1rem;">🗺️</span>
        <h2 style="font-size:1.6rem; margin-bottom:0.5rem;">No journeys found</h2>
        <p class="text-muted" style="margin-bottom:2rem; max-width:440px; margin-left:auto; margin-right:auto;">
          <?php echo !empty($searchQuery) ? 'No trips match your search criteria. Try a different term.' : 'You haven’t planned any trips yet. Start your journey across Incredible India now!'; ?>
        </p>
        <a href="create_trip.php" class="btn btn-primary btn-lg">+ Plan Your First Journey</a>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($trips as $trip): 
            $statusBadge = [
                'planned'   => 'badge-primary',
                'ongoing'   => 'badge-teal',
                'completed' => 'badge-gold'
            ][$trip['status']] ?? 'badge-primary';
            $cover = $trip['cover_photo'] ?: 'https://images.unsplash.com/photo-1599661046289-e31897846e41?w=600';
            $budgetProgress = ($trip['total_budget'] > 0) ? min(100, round(($trip['spent_amount'] / $trip['total_budget']) * 100)) : 0;
        ?>
          <div class="dest-card">
            <div class="dest-img-wrap" style="height: 190px;">
              <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($trip['name']); ?>">
              <div class="dest-overlay">
                <span class="badge <?php echo $statusBadge; ?>"><?php echo ucfirst($trip['status']); ?></span>
                <span class="badge badge-teal"><?php echo $trip['stop_count']; ?> Itinerary Stops</span>
              </div>
            </div>

            <div class="dest-body">
              <div style="font-size:0.8rem; font-weight:700; color:var(--text-dim); text-transform:uppercase; margin-bottom:0.25rem;">
                📍 <?php echo htmlspecialchars($trip['city_name'] ?? 'India'); ?><?php echo $trip['city_state'] ? ', ' . htmlspecialchars($trip['city_state']) : ''; ?>
              </div>

              <h3 class="dest-title" style="font-size:1.2rem;"><?php echo htmlspecialchars($trip['name']); ?></h3>
              
              <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:1rem;">
                🗓️ <?php echo date('M d, Y', strtotime($trip['start_date'])); ?> → <?php echo date('M d, Y', strtotime($trip['end_date'])); ?>
              </div>

              <!-- Budget Progress Meter -->
              <div style="margin-bottom:1.25rem;">
                <div style="display:flex; justify-content:space-between; font-size:0.8rem; margin-bottom:0.25rem;">
                  <span style="color:var(--text-muted);">Spent: <strong style="color:var(--gold);">₹<?php echo number_format($trip['spent_amount']); ?></strong></span>
                  <span style="color:var(--text-dim);">Budget: ₹<?php echo number_format($trip['total_budget']); ?></span>
                </div>
                <div class="progress-bar-wrap">
                  <div class="progress-fill" style="width: <?php echo $budgetProgress; ?>%; background: <?php echo ($budgetProgress > 90) ? 'var(--danger)' : 'var(--grad-primary)'; ?>;"></div>
                </div>
              </div>

              <!-- Action Toolbar -->
              <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; padding-top:0.75rem; border-top:1px solid var(--border);">
                <div style="display:flex; gap:0.4rem;">
                  <a href="itinerary_builder.php?trip_id=<?php echo $trip['id']; ?>" class="btn btn-secondary btn-sm" title="Edit Itinerary">📝 Stops</a>
                  <a href="budget.php?trip_id=<?php echo $trip['id']; ?>" class="btn btn-secondary btn-sm" title="Track Expenses">💰 Budget</a>
                </div>

                <div style="display:flex; gap:0.4rem;">
                  <a href="itinerary_view.php?trip_id=<?php echo $trip['id']; ?>" class="btn btn-primary btn-sm">View</a>
                  <form method="POST" action="my_trips.php" onsubmit="return confirm('Are you sure you want to delete this journey?');" style="display:inline;">
                    <input type="hidden" name="delete_trip_id" value="<?php echo $trip['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Trip">🗑️</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
