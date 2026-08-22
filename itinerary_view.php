<?php
include 'includes/db_connect.php';
session_start();

$tripId = isset($_GET['trip_id']) ? intval($_GET['trip_id']) : 0;
$slug   = trim($_GET['slug'] ?? '');

if (!$tripId && empty($slug)) {
    header("Location: index.php");
    exit();
}

// Fetch trip details
if ($tripId) {
    $tripStmt = $conn->prepare("SELECT t.*, u.first_name, u.last_name, u.photo_url, c.name AS city_name, c.state AS city_state 
        FROM trips t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN cities c ON t.city_id = c.id 
        WHERE t.id = ?");
    $tripStmt->bind_param("i", $tripId);
} else {
    $tripStmt = $conn->prepare("SELECT t.*, u.first_name, u.last_name, u.photo_url, c.name AS city_name, c.state AS city_state 
        FROM trips t 
        JOIN users u ON t.user_id = u.id 
        LEFT JOIN cities c ON t.city_id = c.id 
        WHERE t.share_slug = ?");
    $tripStmt->bind_param("s", $slug);
}

$tripStmt->execute();
$trip = $tripStmt->get_result()->fetch_assoc();

if (!$trip) {
    header("Location: index.php?error=tripnotfound");
    exit();
}

$tripId = $trip['id'];

// If trip is private and current user is not owner, require login & ownership
$currentUserId = $_SESSION['user_id'] ?? null;
if (!$trip['is_public'] && $trip['user_id'] != $currentUserId) {
    require_once 'includes/auth_guard.php';
    require_auth();
}

// Fetch itinerary sections and their activities
$stopsQuery = $conn->query("SELECT s.*, c.name AS city_name, c.state AS city_state, c.image_url AS city_image 
    FROM itinerary_sections s 
    LEFT JOIN cities c ON s.city_id = c.id 
    WHERE s.trip_id = {$tripId} 
    ORDER BY s.order_index ASC, s.start_date ASC");
$stops = $stopsQuery->fetch_all(MYSQLI_ASSOC);

// Fetch total expenses logged
$expenseTotal = $conn->query("SELECT COALESCE(SUM(amount), 0) AS total_spent FROM expenses WHERE trip_id = {$tripId}")->fetch_assoc()['total_spent'];

$pageTitle = htmlspecialchars($trip['name']);
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container container-narrow">
    
    <!-- Hero Banner with Trip Details -->
    <div class="dest-card" style="border-radius:var(--radius-lg); margin-bottom:2.5rem;">
      <div class="dest-img-wrap" style="height:320px;">
        <img src="<?php echo htmlspecialchars($trip['cover_photo']); ?>" alt="<?php echo htmlspecialchars($trip['name']); ?>">
        <div class="dest-overlay" style="padding:2rem;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span class="badge badge-gold">✨ <?php echo ucfirst($trip['status']); ?> Journey</span>
            <span class="badge badge-teal">🇮🇳 <?php echo htmlspecialchars($trip['city_name'] ?? 'Incredible India'); ?></span>
          </div>

          <div>
            <h1 style="color:#fff; font-size:2.4rem; font-weight:900; margin-bottom:0.5rem;"><?php echo htmlspecialchars($trip['name']); ?></h1>
            <div style="display:flex; align-items:center; gap:1rem; color:rgba(255,255,255,0.9); font-size:0.95rem;">
              <span>🗓️ <?php echo date('M d, Y', strtotime($trip['start_date'])); ?> → <?php echo date('M d, Y', strtotime($trip['end_date'])); ?></span>
              <span>•</span>
              <span>💰 Budget: ₹<?php echo number_format($trip['total_budget']); ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="dest-body" style="padding:1.75rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:1.25rem;">
          <!-- Explorer Info -->
          <div style="display:flex; align-items:center; gap:0.75rem;">
            <img
              src="<?php echo htmlspecialchars($trip['photo_url']); ?>"
              alt="<?php echo htmlspecialchars($trip['first_name']); ?>"
              style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid var(--primary);"
            >
            <div>
              <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($trip['first_name'] . ' ' . $trip['last_name']); ?></div>
              <div style="font-size:0.78rem; color:var(--text-dim);">Trip Creator</div>
            </div>
          </div>

          <!-- Share & Print Toolbar -->
          <div style="display:flex; gap:0.5rem;">
            <?php if ($currentUserId && $trip['user_id'] == $currentUserId): ?>
              <a href="itinerary_builder.php?trip_id=<?php echo $tripId; ?>" class="btn btn-secondary btn-sm">Edit Stops 📝</a>
              <a href="budget.php?trip_id=<?php echo $tripId; ?>" class="btn btn-secondary btn-sm">Budget (₹)</a>
            <?php endif; ?>
            <button class="btn btn-primary btn-sm" onclick="copyShareLink()">Share Link 🔗</button>
            <button class="btn btn-secondary btn-sm" onclick="window.print()">Print 🖨️</button>
          </div>
        </div>

        <?php if (!empty($trip['description'])): ?>
          <p style="font-size:0.95rem; color:var(--text-muted); line-height:1.6; margin-bottom:1rem;">
            <?php echo nl2br(htmlspecialchars($trip['description'])); ?>
          </p>
        <?php endif; ?>

        <?php if (!empty($trip['notes'])): ?>
          <div style="background:rgba(245,158,11,0.08); border-left:4px solid var(--gold); padding:0.85rem 1.15rem; border-radius:var(--radius-sm); font-size:0.88rem; color:var(--text-muted);">
            <strong>📌 Traveler Notes:</strong> <?php echo htmlspecialchars($trip['notes']); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Timeline of Stops -->
    <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.6rem;">
      <span>📍</span><span>Day-by-Day Schedule &amp; Stops</span>
    </h2>

    <?php if (empty($stops)): ?>
      <div class="card" style="text-align:center; padding:3rem;">
        <p class="text-muted">No itinerary sections have been created for this journey yet.</p>
      </div>
    <?php else: ?>
      <div style="position:relative; padding-left:2rem; border-left:2px dashed rgba(99,102,241,0.4); display:flex; flex-direction:column; gap:2rem; margin-left:1rem;">
        <?php foreach ($stops as $idx => $stop): 
            $stopActivities = $conn->query("SELECT ta.*, a.name AS activity_name, a.category, a.duration, a.image_url, a.description 
                FROM trip_activities ta 
                JOIN activities a ON ta.activity_id = a.id 
                WHERE ta.stop_id = {$stop['id']}")->fetch_all(MYSQLI_ASSOC);
        ?>
          <div style="position:relative;">
            <!-- Timeline Dot -->
            <div style="position:absolute; left:-2.65rem; top:0.25rem; width:20px; height:20px; border-radius:50%; background:var(--primary); box-shadow:0 0 12px var(--primary-glow); display:grid; place-items:center; font-size:0.65rem; color:#fff; font-weight:bold;">
              <?php echo $idx + 1; ?>
            </div>

            <div class="card" style="padding:1.5rem;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                <div>
                  <span class="badge badge-teal" style="margin-bottom:0.35rem;">
                    <?php echo date('M d', strtotime($stop['start_date'])); ?> → <?php echo date('M d, Y', strtotime($stop['end_date'])); ?>
                  </span>
                  <h3 style="font-size:1.35rem; color:#fff;"><?php echo htmlspecialchars($stop['section_name']); ?></h3>
                  <div style="font-size:0.85rem; color:var(--teal); font-weight:600;">
                    📍 <?php echo htmlspecialchars($stop['city_name'] ?? 'Destination'); ?><?php echo $stop['city_state'] ? ', ' . htmlspecialchars($stop['city_state']) : ''; ?>
                  </div>
                </div>

                <div style="text-align:right;">
                  <div style="font-family:'Outfit',sans-serif; font-size:1.15rem; font-weight:800; color:var(--gold);">
                    ₹<?php echo number_format($stop['budget']); ?>
                  </div>
                  <div style="font-size:0.75rem; color:var(--text-dim);">Stop Budget</div>
                </div>
              </div>

              <?php if (!empty($stop['notes'])): ?>
                <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:1.25rem;">
                  <?php echo htmlspecialchars($stop['notes']); ?>
                </p>
              <?php endif; ?>

              <!-- Activities in this Stop -->
              <?php if (!empty($stopActivities)): ?>
                <div style="display:flex; flex-direction:column; gap:0.75rem; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border);">
                  <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-dim);">
                    🏄 Scheduled Experiences:
                  </div>
                  <?php foreach ($stopActivities as $act): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; background:rgba(13,19,34,0.8); border:1px solid var(--border); border-radius:var(--radius-sm);">
                      <div>
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.2rem;">
                          <span style="font-size:0.82rem; font-weight:700; color:var(--primary);">⏱️ <?php echo htmlspecialchars($act['scheduled_time']); ?></span>
                          <span style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($act['activity_name']); ?></span>
                          <span class="badge badge-primary" style="font-size:0.68rem;"><?php echo htmlspecialchars($act['category']); ?></span>
                        </div>
                        <?php if (!empty($act['notes'])): ?>
                          <div style="font-size:0.78rem; color:var(--text-muted);"><?php echo htmlspecialchars($act['notes']); ?></div>
                        <?php endif; ?>
                      </div>
                      <div style="font-family:'Outfit',sans-serif; font-weight:800; color:var(--gold);">
                        ₹<?php echo number_format($act['cost']); ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
function copyShareLink() {
  const url = window.location.origin + window.location.pathname + '?slug=<?php echo $trip['share_slug']; ?>';
  navigator.clipboard.writeText(url).then(() => {
    Toast.show('Shareable itinerary link copied to clipboard! 📋', 'success');
  });
}
</script>

<?php include 'includes/footer.php'; ?>
