<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db_connect.php';

$userId = require_auth();

$tripId = isset($_GET['trip_id']) ? intval($_GET['trip_id']) : 0;
if (!$tripId) {
    header("Location: my_trips.php");
    exit();
}

// Ensure trip belongs to current user
$tripStmt = $conn->prepare("SELECT t.*, c.name AS city_name, c.state AS city_state 
    FROM trips t 
    LEFT JOIN cities c ON t.city_id = c.id 
    WHERE t.id = ? AND t.user_id = ?");
$tripStmt->bind_param("ii", $tripId, $userId);
$tripStmt->execute();
$trip = $tripStmt->get_result()->fetch_assoc();

if (!$trip) {
    header("Location: my_trips.php?error=notfound");
    exit();
}

$message = '';

// Handle Add Stop / Itinerary Section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_stop') {
    $section_name = trim($_POST['section_name'] ?? '');
    $city_id      = !empty($_POST['city_id']) ? intval($_POST['city_id']) : null;
    $start_date   = $_POST['start_date'] ?? $trip['start_date'];
    $end_date     = $_POST['end_date'] ?? $trip['end_date'];
    $budget       = floatval($_POST['budget'] ?? 0.00);
    $notes        = trim($_POST['notes'] ?? '');

    if (!empty($section_name)) {
        // Calculate next order_index
        $orderQuery = $conn->query("SELECT COALESCE(MAX(order_index), 0) + 1 AS next_order FROM itinerary_sections WHERE trip_id = {$tripId}");
        $nextOrder = $orderQuery->fetch_assoc()['next_order'];

        $stmt = $conn->prepare("INSERT INTO itinerary_sections (trip_id, city_id, section_name, start_date, end_date, budget, order_index, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssdis", $tripId, $city_id, $section_name, $start_date, $end_date, $budget, $nextOrder, $notes);
        if ($stmt->execute()) {
            $message = "Itinerary stop added successfully!";
        }
    }
}

// Handle Add Activity to Stop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_activity') {
    $stop_id        = intval($_POST['stop_id'] ?? 0);
    $activity_id    = intval($_POST['activity_id'] ?? 0);
    $scheduled_time = trim($_POST['scheduled_time'] ?? '10:00 AM');
    $notes          = trim($_POST['notes'] ?? '');
    $cost           = floatval($_POST['cost'] ?? 0.00);

    if ($stop_id && $activity_id) {
        $stmt = $conn->prepare("INSERT INTO trip_activities (stop_id, activity_id, scheduled_time, notes, cost) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE scheduled_time = VALUES(scheduled_time), notes = VALUES(notes), cost = VALUES(cost)");
        $stmt->bind_param("iissd", $stop_id, $activity_id, $scheduled_time, $notes, $cost);
        if ($stmt->execute()) {
            $message = "Activity scheduled for stop!";
        }
    }
}

// Handle Delete Stop
if (isset($_POST['delete_stop_id'])) {
    $delStopId = intval($_POST['delete_stop_id']);
    $conn->query("DELETE FROM itinerary_sections WHERE id = {$delStopId} AND trip_id = {$tripId}");
    $message = "Stop removed from itinerary.";
}

// Fetch all stops for this trip
$stopsQuery = $conn->query("SELECT s.*, c.name AS city_name, c.state AS city_state 
    FROM itinerary_sections s 
    LEFT JOIN cities c ON s.city_id = c.id 
    WHERE s.trip_id = {$tripId} 
    ORDER BY s.order_index ASC, s.start_date ASC");
$stops = $stopsQuery->fetch_all(MYSQLI_ASSOC);

// Fetch all Indian cities for stop selector
$cities = $conn->query("SELECT id, name, state FROM cities ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch activities for activity modal
$activities = $conn->query("SELECT a.id, a.name, a.category, a.cost, a.duration, c.name AS city_name 
    FROM activities a 
    JOIN cities c ON a.city_id = c.id 
    ORDER BY c.name ASC, a.name ASC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Build Itinerary — " . $trip['name'];
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container">
    
    <!-- Header with Quick Action Bar -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1.5rem; margin-bottom:2.5rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border);">
      <div>
        <a href="my_trips.php" class="text-muted" style="font-size:0.88rem; display:inline-flex; align-items:center; gap:0.35rem; margin-bottom:0.5rem;">
          ← Back to My Trips
        </a>
        <div style="display:flex; align-items:center; gap:0.75rem;">
          <h1 style="font-size:2.2rem; font-weight:800;"><?php echo htmlspecialchars($trip['name']); ?></h1>
          <span class="badge badge-primary">Itinerary Builder</span>
        </div>
        <p class="text-muted" style="margin-top:0.25rem;">
          📍 <?php echo htmlspecialchars($trip['city_name'] ?? 'India'); ?> • 🗓️ <?php echo date('M d', strtotime($trip['start_date'])); ?> to <?php echo date('M d, Y', strtotime($trip['end_date'])); ?> • 💰 Total Budget: ₹<?php echo number_format($trip['total_budget']); ?>
        </p>
      </div>

      <div style="display:flex; gap:0.75rem;">
        <a href="budget.php?trip_id=<?php echo $tripId; ?>" class="btn btn-secondary">💰 Budget Tracker</a>
        <a href="itinerary_view.php?trip_id=<?php echo $tripId; ?>" class="btn btn-primary">Preview &amp; Share 🚀</a>
      </div>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-success">
        <span>✨</span>
        <span><?php echo htmlspecialchars($message); ?></span>
      </div>
    <?php endif; ?>

    <!-- Two Column Layout: Stops List & Add Stop Form -->
    <div style="display:grid; grid-template-columns: 1.8fr 1.2fr; gap:2rem; align-items:start;">
      
      <!-- Left Column: Stops & Attached Activities -->
      <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
          <h2 style="font-size:1.4rem; font-weight:800;">Itinerary Schedule &amp; Stops (<?php echo count($stops); ?>)</h2>
        </div>

        <?php if (empty($stops)): ?>
          <div class="card" style="text-align:center; padding:3rem 1.5rem;">
            <span style="font-size:3rem; display:block; margin-bottom:1rem;">📍</span>
            <h3 style="margin-bottom:0.5rem;">No stops added yet</h3>
            <p class="text-muted">Add your first destination stop using the form on the right.</p>
          </div>
        <?php else: ?>
          <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <?php foreach ($stops as $idx => $stop): 
                // Fetch activities for this stop
                $stopActivities = $conn->query("SELECT ta.*, a.name AS activity_name, a.category, a.duration 
                    FROM trip_activities ta 
                    JOIN activities a ON ta.activity_id = a.id 
                    WHERE ta.stop_id = {$stop['id']}")->fetch_all(MYSQLI_ASSOC);
            ?>
              <div class="card" style="padding:1.5rem; position:relative; border-left: 4px solid var(--primary);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem;">
                  <div>
                    <span class="badge badge-gold" style="margin-bottom:0.4rem;">Stop #<?php echo $idx + 1; ?></span>
                    <h3 style="font-size:1.3rem; color:#fff;"><?php echo htmlspecialchars($stop['section_name']); ?></h3>
                    <div style="font-size:0.85rem; color:var(--teal); font-weight:600;">
                      📍 <?php echo htmlspecialchars($stop['city_name'] ?? 'Custom Location'); ?><?php echo $stop['city_state'] ? ', ' . htmlspecialchars($stop['city_state']) : ''; ?>
                    </div>
                  </div>

                  <div style="text-align:right;">
                    <div style="font-family:'Outfit',sans-serif; font-size:1.15rem; font-weight:800; color:var(--gold);">
                      ₹<?php echo number_format($stop['budget']); ?>
                    </div>
                    <div style="font-size:0.75rem; color:var(--text-dim);">Allocated Budget</div>
                  </div>
                </div>

                <div style="display:flex; align-items:center; gap:1rem; font-size:0.85rem; color:var(--text-muted); margin-bottom:1.25rem;">
                  <span>🗓️ <?php echo date('M d', strtotime($stop['start_date'])); ?> → <?php echo date('M d, Y', strtotime($stop['end_date'])); ?></span>
                  <?php if (!empty($stop['notes'])): ?>
                    <span>• 💬 <?php echo htmlspecialchars($stop['notes']); ?></span>
                  <?php endif; ?>
                </div>

                <!-- Attached Activities List -->
                <div style="background:rgba(13,19,34,0.6); padding:1rem; border-radius:var(--radius-sm); border:1px solid var(--border); margin-bottom:1.25rem;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span style="font-size:0.82rem; font-weight:700; text-transform:uppercase; color:var(--text-dim); letter-spacing:0.5px;">
                      🏄 Scheduled Experiences (<?php echo count($stopActivities); ?>)
                    </span>
                  </div>

                  <?php if (empty($stopActivities)): ?>
                    <p style="font-size:0.82rem; color:var(--text-dim); margin:0;">No activities attached yet. Click below to add one!</p>
                  <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:0.5rem;">
                      <?php foreach ($stopActivities as $sAct): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:0.4rem 0.6rem; background:rgba(255,255,255,0.03); border-radius:6px; font-size:0.85rem;">
                          <div style="display:flex; align-items:center; gap:0.5rem;">
                            <span>⏱️ <?php echo htmlspecialchars($sAct['scheduled_time']); ?></span>
                            <span style="font-weight:600; color:#fff;"><?php echo htmlspecialchars($sAct['activity_name']); ?></span>
                            <span class="badge badge-primary" style="font-size:0.7rem; padding:0.1rem 0.4rem;"><?php echo htmlspecialchars($sAct['category']); ?></span>
                          </div>
                          <div style="font-family:'Outfit',sans-serif; font-weight:700; color:var(--gold);">
                            ₹<?php echo number_format($sAct['cost']); ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Stop Action Buttons -->
                <div style="display:flex; justify-content:space-between; align-items:center;">
                  <button class="btn btn-secondary btn-sm" onclick="openActivityModal(<?php echo $stop['id']; ?>, '<?php echo addslashes($stop['section_name']); ?>')">
                    + Add Experience / Activity
                  </button>

                  <form method="POST" action="itinerary_builder.php?trip_id=<?php echo $tripId; ?>" onsubmit="return confirm('Remove this stop and its activities?');">
                    <input type="hidden" name="delete_stop_id" value="<?php echo $stop['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete Stop 🗑️</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right Column: Add New Stop Form -->
      <div class="card" style="padding:1.75rem; position:sticky; top:5.5rem;">
        <span class="badge badge-teal" style="margin-bottom:0.5rem;">Route Planning</span>
        <h3 style="font-size:1.3rem; margin-bottom:1rem;">+ Add Itinerary Stop</h3>
        
        <form method="POST" action="itinerary_builder.php?trip_id=<?php echo $tripId; ?>">
          <input type="hidden" name="action" value="add_stop">

          <div class="form-group">
            <label class="form-label">Stop / Section Name *</label>
            <input type="text" name="section_name" placeholder="e.g. Day 1-3: Jaipur Palaces & Bazaars" required>
          </div>

          <div class="form-group">
            <label class="form-label">City / Destination</label>
            <select name="city_id" required>
              <option value="" disabled selected>-- Select Indian Destination --</option>
              <?php foreach ($cities as $ct): ?>
                <option value="<?php echo $ct['id']; ?>">
                  <?php echo htmlspecialchars($ct['name']) . ($ct['state'] ? ' (' . htmlspecialchars($ct['state']) . ')' : ''); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" value="<?php echo $trip['start_date']; ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" value="<?php echo $trip['end_date']; ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Stop Budget (₹ INR)</label>
            <input type="number" name="budget" placeholder="10000" step="500" value="10000">
          </div>

          <div class="form-group">
            <label class="form-label">Notes &amp; Stay Info</label>
            <textarea name="notes" placeholder="Hotel name, travel tips, landmarks to see..." rows="2"></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <span>Add Stop to Route 📌</span>
          </button>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- Add Activity Modal Dialog -->
<div id="activityModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); z-index:9999; align-items:center; justify-content:center; padding:1.5rem;">
  <div class="card" style="width:100%; max-width:540px; background:var(--surface-elevated); padding:2rem; box-shadow:var(--shadow-lg);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
      <h3 id="modalStopTitle" style="font-size:1.3rem;">Add Experience to Stop</h3>
      <button onclick="closeActivityModal()" style="background:none; border:none; color:var(--text); font-size:1.5rem; cursor:pointer;">✕</button>
    </div>

    <form method="POST" action="itinerary_builder.php?trip_id=<?php echo $tripId; ?>">
      <input type="hidden" name="action" value="add_activity">
      <input type="hidden" name="stop_id" id="modalStopId">

      <div class="form-group">
        <label class="form-label">Select Curated Experience *</label>
        <select name="activity_id" id="activitySelect" required onchange="updateActivityCost(this)">
          <option value="" disabled selected>-- Pick an Activity in India --</option>
          <?php foreach ($activities as $act): ?>
            <option value="<?php echo $act['id']; ?>" data-cost="<?php echo $act['cost']; ?>">
              <?php echo htmlspecialchars($act['city_name']) . ' — ' . htmlspecialchars($act['name']) . ' (₹' . number_format($act['cost']) . ')'; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="form-group">
          <label class="form-label">Scheduled Time</label>
          <input type="text" name="scheduled_time" placeholder="09:00 AM or Sunset" value="10:00 AM">
        </div>
        <div class="form-group">
          <label class="form-label">Estimated Cost (₹ INR)</label>
          <input type="number" name="cost" id="activityCostInput" placeholder="500" step="50" value="500">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Notes</label>
        <input type="text" name="notes" placeholder="e.g. Booking ref, guide name, morning slot...">
      </div>

      <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
        <button type="button" class="btn btn-secondary" onclick="closeActivityModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Attach Experience ✨</button>
      </div>
    </form>
  </div>
</div>

<script>
function openActivityModal(stopId, stopTitle) {
  document.getElementById('modalStopId').value = stopId;
  document.getElementById('modalStopTitle').innerText = 'Add Experience — ' + stopTitle;
  document.getElementById('activityModal').style.display = 'flex';
}

function closeActivityModal() {
  document.getElementById('activityModal').style.display = 'none';
}

function updateActivityCost(select) {
  const opt = select.options[select.selectedIndex];
  const cost = opt.getAttribute('data-cost');
  if (cost) {
    document.getElementById('activityCostInput').value = cost;
  }
}
</script>

<?php include 'includes/footer.php'; ?>
