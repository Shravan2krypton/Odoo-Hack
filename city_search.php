<?php
include 'includes/db_connect.php';
session_start();

$searchQuery = trim($_GET['q'] ?? '');
$regionFilter = isset($_GET['region']) ? intval($_GET['region']) : 0;
$budgetMax   = isset($_GET['budget_max']) ? intval($_GET['budget_max']) : 0;

$sql = "SELECT c.*, r.name AS region_name 
    FROM cities c 
    LEFT JOIN regions r ON c.region_id = r.id 
    WHERE 1=1";

$params = [];
$types  = "";

if (!empty($searchQuery)) {
    $sql .= " AND (c.name LIKE ? OR c.state LIKE ? OR c.description LIKE ?)";
    $like = "%{$searchQuery}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($regionFilter > 0) {
    $sql .= " AND c.region_id = ?";
    $params[] = $regionFilter;
    $types .= "i";
}

if ($budgetMax > 0) {
    if ($budgetMax <= 2000) {
        $sql .= " AND c.avg_daily_cost <= 2000";
    } elseif ($budgetMax <= 3500) {
        $sql .= " AND c.avg_daily_cost BETWEEN 2001 AND 3500";
    } else {
        $sql .= " AND c.avg_daily_cost > 3500";
    }
}

$sql .= " ORDER BY c.popularity_score DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$cities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch regions for dropdown
$regions = $conn->query("SELECT id, name FROM regions ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Explore Indian Destinations";
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container">
    
    <!-- Top Header -->
    <div style="text-align:center; max-width:700px; margin:0 auto 3rem;">
      <span class="badge badge-gold" style="margin-bottom:0.5rem;">Discover India</span>
      <h1 style="font-size:2.6rem; font-weight:900;">Iconic Indian Destinations</h1>
      <p class="text-muted" style="font-size:1.05rem;">
        Explore 18+ handpicked destinations spanning palaces, beaches, snow peaks, and holy river ghats.
      </p>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding:1.5rem; margin-bottom:2.5rem;">
      <form method="GET" action="city_search.php" class="grid grid-4" style="align-items:center;">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Search Name or State</label>
          <input type="text" name="q" placeholder="e.g. Jaipur, Kerala, Ladakh..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Indian Region</label>
          <select name="region">
            <option value="">All Regions</option>
            <?php foreach ($regions as $reg): ?>
              <option value="<?php echo $reg['id']; ?>" <?php echo ($regionFilter == $reg['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($reg['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Daily Budget (₹ INR)</label>
          <select name="budget_max">
            <option value="">Any Budget</option>
            <option value="2000" <?php echo ($budgetMax == 2000) ? 'selected' : ''; ?>>Under ₹2,000 / day</option>
            <option value="3500" <?php echo ($budgetMax == 3500) ? 'selected' : ''; ?>>₹2,000 - ₹3,500 / day</option>
            <option value="5000" <?php echo ($budgetMax == 5000) ? 'selected' : ''; ?>>₹3,500+ / day (Luxury)</option>
          </select>
        </div>

        <div style="display:flex; gap:0.5rem; align-items:flex-end;">
          <button type="submit" class="btn btn-primary btn-block" style="height:48px;">Apply Filters</button>
          <a href="city_search.php" class="btn btn-secondary" style="height:48px;" title="Reset Filters">↺</a>
        </div>
      </form>
    </div>

    <!-- Results Grid -->
    <?php if (empty($cities)): ?>
      <div class="card" style="text-align:center; padding:4rem 2rem;">
        <span style="font-size:3.5rem; display:block; margin-bottom:1rem;">🔍</span>
        <h3 style="font-size:1.5rem; margin-bottom:0.5rem;">No destinations match your criteria</h3>
        <p class="text-muted" style="margin-bottom:1.5rem;">Try adjusting your search terms or clearing the region/budget filters.</p>
        <a href="city_search.php" class="btn btn-secondary">View All Destinations</a>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($cities as $c): ?>
          <div class="dest-card card-interactive">
            <div class="dest-img-wrap">
              <img src="<?php echo htmlspecialchars($c['image_url']); ?>" alt="<?php echo htmlspecialchars($c['name']); ?>" loading="lazy">
              <div class="dest-overlay">
                <span class="badge badge-gold">★ <?php echo $c['popularity_score']; ?>/100 Score</span>
                <span class="badge badge-teal"><?php echo htmlspecialchars($c['best_time_to_visit'] ?? 'All Year'); ?></span>
              </div>
            </div>

            <div class="dest-body">
              <div style="font-size:0.8rem; text-transform:uppercase; font-weight:700; color:var(--text-dim); margin-bottom:0.25rem;">
                <?php echo htmlspecialchars($c['state']); ?> • <?php echo htmlspecialchars($c['region_name']); ?>
              </div>

              <h3 class="dest-title"><?php echo htmlspecialchars($c['name']); ?></h3>

              <p style="font-size:0.86rem; color:var(--text-muted); line-height:1.5; margin-bottom:1.25rem;">
                <?php echo htmlspecialchars($c['description']); ?>
              </p>

              <div class="dest-meta">
                <div>
                  <div style="font-size:0.75rem; color:var(--text-dim);">Estimated Daily Cost</div>
                  <div class="dest-price">₹<?php echo number_format($c['avg_daily_cost']); ?> <span style="font-size:0.75rem; font-weight:normal; color:var(--text-muted);">/ day</span></div>
                </div>

                <a href="create_trip.php?city_id=<?php echo $c['id']; ?>" class="btn btn-primary btn-sm">+ Plan Trip</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
