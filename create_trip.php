<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db_connect.php';

$userId = require_auth();

$message = '';
$preselectedCityId = isset($_GET['city_id']) ? intval($_GET['city_id']) : null;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $country_id   = !empty($_POST['country_id']) ? intval($_POST['country_id']) : 1;
    $city_id      = !empty($_POST['city_id']) ? intval($_POST['city_id']) : null;
    $start_date   = $_POST['start_date'] ?? '';
    $end_date     = $_POST['end_date'] ?? '';
    $total_budget = !empty($_POST['total_budget']) ? floatval($_POST['total_budget']) : 0.00;
    $description  = trim($_POST['description'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');
    $cover_photo  = trim($_POST['cover_photo'] ?? '');

    if (empty($name) || empty($start_date) || empty($end_date)) {
        $message = "Trip name and travel dates are required.";
    } elseif ($start_date > $end_date) {
        $message = "End date cannot be earlier than start date.";
    } else {
        // If no cover photo provided, fetch default from destination city
        if (empty($cover_photo) && $city_id) {
            $cityImgQuery = $conn->query("SELECT image_url FROM cities WHERE id = {$city_id}");
            if ($cityRow = $cityImgQuery->fetch_assoc()) {
                $cover_photo = $cityRow['image_url'];
            }
        }
        if (empty($cover_photo)) {
            $cover_photo = 'https://images.unsplash.com/photo-1599661046289-e31897846e41?w=800';
        }

        // Generate unique share slug
        $slugBase = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $share_slug = $slugBase . '-' . substr(md5(uniqid()), 0, 6);

        $sql = "INSERT INTO trips 
            (user_id, country_id, city_id, name, description, notes, start_date, end_date, cover_photo, total_budget, status, is_public, share_slug) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'planned', 1, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiissssssds", $userId, $country_id, $city_id, $name, $description, $notes, $start_date, $end_date, $cover_photo, $total_budget, $share_slug);

        if ($stmt->execute()) {
            $newTripId = $stmt->insert_id;

            // Automatically create initial stop in itinerary_sections if city is chosen
            if ($city_id) {
                $cityInfo = $conn->query("SELECT name FROM cities WHERE id = {$city_id}")->fetch_assoc();
                $cityName = $cityInfo['name'] ?? 'Stop 1';
                $stopStmt = $conn->prepare("INSERT INTO itinerary_sections (trip_id, city_id, section_name, start_date, end_date, budget, order_index) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $stopStmt->bind_param("iisssd", $newTripId, $city_id, $cityName, $start_date, $end_date, $total_budget);
                $stopStmt->execute();
            }

            // Log action
            $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, 'CREATE_TRIP', 'trips', ?)");
            if ($logStmt) {
                $logStmt->bind_param("ii", $userId, $newTripId);
                $logStmt->execute();
            }

            header("Location: itinerary_builder.php?trip_id={$newTripId}&created=1");
            exit();
        } else {
            $message = "Error creating journey: " . $stmt->error;
        }
    }
}

// Fetch countries
$countries = $conn->query("SELECT id, name, phone_code FROM countries ORDER BY (id = 1) DESC, name ASC");

// Fetch Indian cities
$cities = $conn->query("SELECT id, name, state FROM cities WHERE country_id = 1 ORDER BY name ASC");

$pageTitle = "Plan a New Journey";
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container container-narrow">
    
    <div style="margin-bottom: 2rem;">
      <a href="dashboard.php" class="text-muted" style="font-size:0.88rem; display:inline-flex; align-items:center; gap:0.35rem; margin-bottom:0.75rem;">
        ← Back to Dashboard
      </a>
      <span class="badge badge-sunset" style="margin-bottom:0.5rem;">Trip Planner Wizard</span>
      <h1 style="font-size: 2.2rem; font-weight:800;">Plan Your Next Indian Odyssey</h1>
      <p class="text-muted">Set up your route, travel dates, and initial budget in Indian Rupees (₹).</p>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-error">
        <span>⚠️</span>
        <span><?php echo htmlspecialchars($message); ?></span>
      </div>
    <?php endif; ?>

    <div class="card" style="padding: 2.5rem;">
      <form method="POST" action="create_trip.php" id="createTripForm">
        
        <div class="form-group">
          <label class="form-label" for="tripName">Trip Title *</label>
          <input type="text" name="name" id="tripName" placeholder="e.g. Royal Rajasthan Palace Tour or Kerala Backwaters Escape" required>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
          <div class="form-group">
            <label class="form-label" for="countrySelect">Country</label>
            <select name="country_id" id="countrySelect" required>
              <?php while ($c = $countries->fetch_assoc()): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == 1) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($c['name']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="citySelect">Primary Destination</label>
            <select name="city_id" id="citySelect" required>
              <option value="" disabled <?php echo !$preselectedCityId ? 'selected' : ''; ?>>-- Select Primary City --</option>
              <?php while ($city = $cities->fetch_assoc()): ?>
                <option value="<?php echo $city['id']; ?>" <?php echo ($preselectedCityId == $city['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($city['name']) . ($city['state'] ? ' (' . htmlspecialchars($city['state']) . ')' : ''); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1.25rem;">
          <div class="form-group">
            <label class="form-label" for="startDate">Start Date *</label>
            <input type="date" name="start_date" id="startDate" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="endDate">End Date *</label>
            <input type="date" name="end_date" id="endDate" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="totalBudget">Total Budget (₹ INR)</label>
            <input type="number" name="total_budget" id="totalBudget" placeholder="35000" step="500" value="35000">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="tripDesc">Trip Overview &amp; Highlights</label>
          <textarea name="description" id="tripDesc" placeholder="Describe the purpose of this trip, companions, key sights to see..." rows="3"></textarea>
        </div>

        <div class="form-group">
          <label class="form-label" for="coverPhoto">Cover Photo URL (Optional)</label>
          <input type="text" name="cover_photo" id="coverPhoto" placeholder="https://images.unsplash.com/... (leave blank to auto-select)">
        </div>

        <!-- Quick Cover Image Presets -->
        <div style="margin-bottom: 2rem;">
          <label class="form-label" style="margin-bottom: 0.6rem;">Or Pick a High-Res Cover Preset:</label>
          <div style="display:flex; gap:0.75rem; overflow-x:auto; padding-bottom:0.5rem;">
            <img src="https://images.unsplash.com/photo-1599661046289-e31897846e41?w=200" alt="Rajasthan" class="card-interactive" style="width:90px; height:60px; object-fit:cover; border-radius:var(--radius-sm); cursor:pointer;" onclick="setCover(this.src)">
            <img src="https://images.unsplash.com/photo-1581793745862-99fde7fa73d2?w=200" alt="Ladakh" class="card-interactive" style="width:90px; height:60px; object-fit:cover; border-radius:var(--radius-sm); cursor:pointer;" onclick="setCover(this.src)">
            <img src="https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?w=200" alt="Kerala" class="card-interactive" style="width:90px; height:60px; object-fit:cover; border-radius:var(--radius-sm); cursor:pointer;" onclick="setCover(this.src)">
            <img src="https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=200" alt="Goa" class="card-interactive" style="width:90px; height:60px; object-fit:cover; border-radius:var(--radius-sm); cursor:pointer;" onclick="setCover(this.src)">
            <img src="https://images.unsplash.com/photo-1561361513-2d000a50f0dc?w=200" alt="Varanasi" class="card-interactive" style="width:90px; height:60px; object-fit:cover; border-radius:var(--radius-sm); cursor:pointer;" onclick="setCover(this.src)">
            <img src="https://images.unsplash.com/photo-1564507592333-c60657eea523?w=200" alt="Taj Mahal" class="card-interactive" style="width:90px; height:60px; object-fit:cover; border-radius:var(--radius-sm); cursor:pointer;" onclick="setCover(this.src)">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <span>Create Journey &amp; Build Itinerary 📝 →</span>
        </button>
      </form>
    </div>

  </div>
</div>

<script>
function setCover(url) {
  document.getElementById('coverPhoto').value = url;
  Toast.show('Cover photo preset selected! 📸', 'info');
}
</script>

<?php include 'includes/footer.php'; ?>
