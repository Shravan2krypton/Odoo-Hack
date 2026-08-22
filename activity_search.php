<?php
include 'includes/db_connect.php';
session_start();

$searchQuery    = trim($_GET['q'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$cityFilter     = isset($_GET['city_id']) ? intval($_GET['city_id']) : 0;

$sql = "SELECT a.*, c.name AS city_name, c.state AS city_state 
    FROM activities a 
    JOIN cities c ON a.city_id = c.id 
    WHERE 1=1";

$params = [];
$types  = "";

if (!empty($searchQuery)) {
    $sql .= " AND (a.name LIKE ? OR a.description LIKE ? OR c.name LIKE ?)";
    $like = "%{$searchQuery}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if (!empty($categoryFilter)) {
    $sql .= " AND a.category = ?";
    $params[] = $categoryFilter;
    $types .= "s";
}

if ($cityFilter > 0) {
    $sql .= " AND a.city_id = ?";
    $params[] = $cityFilter;
    $types .= "i";
}

$sql .= " ORDER BY a.cost DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch categories for filter
$categories = ['Sightseeing', 'Culture', 'Adventure', 'Spiritual', 'Wellness'];

// Fetch cities for filter
$cities = $conn->query("SELECT id, name FROM cities ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch user's planned trips if logged in (for 1-click add to itinerary)
$userTrips = [];
if (isset($_SESSION['user_id'])) {
    $uTripsQuery = $conn->query("SELECT id, name FROM trips WHERE user_id = {$_SESSION['user_id']} AND status = 'planned' ORDER BY start_date ASC");
    $userTrips = $uTripsQuery->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = "Curated Indian Experiences & Activities";
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container">
    
    <!-- Top Header -->
    <div style="text-align:center; max-width:700px; margin:0 auto 3rem;">
      <span class="badge badge-accent" style="margin-bottom:0.5rem;">Authentic Experiences</span>
      <h1 style="font-size:2.6rem; font-weight:900;">Activities &amp; Adventures</h1>
      <p class="text-muted" style="font-size:1.05rem;">
        Discover 36+ curated excursions — from sunrise Ganges boat rides to Ladakh desert camel safaris and scuba diving in Havelock.
      </p>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding:1.5rem; margin-bottom:2.5rem;">
      <form method="GET" action="activity_search.php" class="grid grid-4" style="align-items:center;">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Search Activity</label>
          <input type="text" name="q" placeholder="e.g. Scuba, Rafting, Aarti, Trek..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Experience Category</label>
          <select name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat; ?>" <?php echo ($categoryFilter === $cat) ? 'selected' : ''; ?>>
                <?php echo $cat; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Destination City</label>
          <select name="city_id">
            <option value="">All Cities</option>
            <?php foreach ($cities as $ct): ?>
              <option value="<?php echo $ct['id']; ?>" <?php echo ($cityFilter == $ct['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($ct['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display:flex; gap:0.5rem; align-items:flex-end;">
          <button type="submit" class="btn btn-primary btn-block" style="height:48px;">Filter Activities</button>
          <a href="activity_search.php" class="btn btn-secondary" style="height:48px;" title="Reset">↺</a>
        </div>
      </form>
    </div>

    <!-- Results Grid -->
    <?php if (empty($activities)): ?>
      <div class="card" style="text-align:center; padding:4rem 2rem;">
        <span style="font-size:3.5rem; display:block; margin-bottom:1rem;">🏄</span>
        <h3 style="font-size:1.5rem; margin-bottom:0.5rem;">No experiences match your criteria</h3>
        <p class="text-muted" style="margin-bottom:1.5rem;">Try relaxing your search terms or choosing all categories.</p>
        <a href="activity_search.php" class="btn btn-secondary">View All Activities</a>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($activities as $act): 
            $catBadge = [
                'Adventure'  => 'badge-accent',
                'Culture'    => 'badge-gold',
                'Spiritual'  => 'badge-teal',
                'Wellness'   => 'badge-primary',
                'Sightseeing'=> 'badge-primary'
            ][$act['category']] ?? 'badge-primary';
        ?>
          <div class="card card-interactive" style="padding:1.5rem; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.85rem;">
              <span class="badge <?php echo $catBadge; ?>"><?php echo htmlspecialchars($act['category']); ?></span>
              <span style="font-size:0.8rem; color:var(--text-dim); font-weight:600;">⏱️ <?php echo round($act['duration']/60, 1); ?> hrs duration</span>
            </div>

            <h3 style="font-size:1.25rem; color:#fff; margin-bottom:0.4rem;"><?php echo htmlspecialchars($act['name']); ?></h3>
            <div style="font-size:0.85rem; color:var(--teal); font-weight:700; margin-bottom:0.75rem;">
              📍 <?php echo htmlspecialchars($act['city_name']); ?><?php echo $act['city_state'] ? ', ' . htmlspecialchars($act['city_state']) : ''; ?>
            </div>

            <p style="font-size:0.88rem; color:var(--text-muted); line-height:1.5; margin-bottom:1.5rem;">
              <?php echo htmlspecialchars($act['description']); ?>
            </p>

            <div style="margin-top:auto; display:flex; justify-content:space-between; align-items:center; padding-top:1rem; border-top:1px solid var(--border);">
              <div>
                <span style="font-size:0.75rem; color:var(--text-dim);">Estimated Cost</span>
                <div style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; color:var(--gold);">
                  <?php echo ($act['cost'] > 0) ? '₹' . number_format($act['cost']) : '<span style="color:var(--success);">Free</span>'; ?>
                </div>
              </div>

              <?php if (isset($_SESSION['user_id']) && !empty($userTrips)): ?>
                <a href="itinerary_builder.php?trip_id=<?php echo $userTrips[0]['id']; ?>" class="btn btn-primary btn-sm">+ Add to Trip</a>
              <?php else: ?>
                <a href="create_trip.php?city_id=<?php echo $act['city_id']; ?>" class="btn btn-secondary btn-sm">Plan Visit →</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
