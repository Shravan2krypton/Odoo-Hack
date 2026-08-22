<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db_connect.php';

$userId = require_auth();

// Fetch all trips for user dropdown
$tripsQuery = $conn->prepare("SELECT id, name, total_budget FROM trips WHERE user_id = ? ORDER BY start_date DESC");
$tripsQuery->bind_param("i", $userId);
$tripsQuery->execute();
$userTrips = $tripsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Active trip ID
$selectedTripId = isset($_GET['trip_id']) ? intval($_GET['trip_id']) : ($userTrips[0]['id'] ?? 0);

// Handle Add Expense
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_expense') {
    $trip_id      = intval($_POST['trip_id']);
    $amount       = floatval($_POST['amount']);
    $category     = $_POST['category'] ?? 'other';
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');
    $note         = trim($_POST['note'] ?? '');
    $stop_id      = !empty($_POST['stop_id']) ? intval($_POST['stop_id']) : null;

    if ($trip_id && $amount > 0) {
        $stmt = $conn->prepare("INSERT INTO expenses (trip_id, trip_stop_id, amount, category, expense_date, note) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidsss", $trip_id, $stop_id, $amount, $category, $expense_date, $note);
        if ($stmt->execute()) {
            $message = "Expense of ₹" . number_format($amount) . " recorded!";
            $selectedTripId = $trip_id;
        }
    }
}

// Handle Delete Expense
if (isset($_POST['delete_expense_id'])) {
    $expId = intval($_POST['delete_expense_id']);
    $conn->query("DELETE FROM expenses WHERE id = {$expId} AND trip_id IN (SELECT id FROM trips WHERE user_id = {$userId})");
    $message = "Expense deleted.";
}

// Fetch active trip details
$activeTrip = null;
$expenses   = [];
$catTotals  = ['stay' => 0, 'transport' => 0, 'meals' => 0, 'activities' => 0, 'shopping' => 0, 'other' => 0];
$totalSpent = 0;
$tripStops  = [];

if ($selectedTripId) {
    $tStmt = $conn->prepare("SELECT * FROM trips WHERE id = ? AND user_id = ?");
    $tStmt->bind_param("ii", $selectedTripId, $userId);
    $tStmt->execute();
    $activeTrip = $tStmt->get_result()->fetch_assoc();

    if ($activeTrip) {
        // Fetch expenses
        $expQuery = $conn->query("SELECT e.*, s.section_name 
            FROM expenses e 
            LEFT JOIN itinerary_sections s ON e.trip_stop_id = s.id 
            WHERE e.trip_id = {$selectedTripId} 
            ORDER BY e.expense_date DESC, e.id DESC");
        $expenses = $expQuery->fetch_all(MYSQLI_ASSOC);

        // Calculate category breakdowns
        foreach ($expenses as $exp) {
            $totalSpent += $exp['amount'];
            $cat = $exp['category'] ?? 'other';
            if (isset($catTotals[$cat])) {
                $catTotals[$cat] += $exp['amount'];
            } else {
                $catTotals['other'] += $exp['amount'];
            }
        }

        // Fetch stops for modal dropdown
        $tripStops = $conn->query("SELECT id, section_name FROM itinerary_sections WHERE trip_id = {$selectedTripId} ORDER BY order_index ASC")->fetch_all(MYSQLI_ASSOC);
    }
}

$pageTitle = "Travel Budget & Expense Tracker (₹)";
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container">
    
    <!-- Top Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem; margin-bottom:2.5rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border);">
      <div>
        <span class="badge badge-gold" style="margin-bottom:0.4rem;">Financial Tracker</span>
        <h1 style="font-size:2.2rem; font-weight:800;">Budget &amp; Expense Manager (₹ INR)</h1>
        <p class="text-muted">Track your travel spending across transport, stays, food, and activities in India.</p>
      </div>

      <!-- Trip Selector Dropdown -->
      <?php if (!empty($userTrips)): ?>
        <form method="GET" action="budget.php" style="display:flex; align-items:center; gap:0.75rem;">
          <label style="font-size:0.88rem; font-weight:600; color:var(--text-muted);">Select Journey:</label>
          <select name="trip_id" onchange="this.form.submit()" style="min-width:240px; padding:0.6rem 1rem;">
            <?php foreach ($userTrips as $t): ?>
              <option value="<?php echo $t['id']; ?>" <?php echo ($selectedTripId == $t['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($t['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
      <?php endif; ?>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-success">
        <span>✨</span>
        <span><?php echo htmlspecialchars($message); ?></span>
      </div>
    <?php endif; ?>

    <?php if (!$activeTrip): ?>
      <div class="card" style="text-align:center; padding:4rem 2rem;">
        <span style="font-size:3.5rem; display:block; margin-bottom:1rem;">💰</span>
        <h2 style="font-size:1.6rem; margin-bottom:0.5rem;">No journeys available</h2>
        <p class="text-muted" style="margin-bottom:1.5rem;">Create a trip first to start tracking expenses and budgets.</p>
        <a href="create_trip.php" class="btn btn-primary btn-lg">+ Plan a New Journey</a>
      </div>
    <?php else: 
        $budget = $activeTrip['total_budget'];
        $remaining = max(0, $budget - $totalSpent);
        $percentSpent = ($budget > 0) ? min(100, round(($totalSpent / $budget) * 100)) : 0;
    ?>
      
      <!-- Budget Overview Summary Cards -->
      <div class="grid grid-3" style="margin-bottom:2.5rem;">
        <div class="card card-gradient" style="padding:1.5rem;">
          <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Allocated Budget</span>
          <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:#fff; margin-top:0.25rem;">
            ₹<?php echo number_format($budget); ?>
          </div>
          <div style="font-size:0.8rem; color:var(--text-dim); margin-top:0.25rem;">Target limit for trip</div>
        </div>

        <div class="card card-gradient" style="padding:1.5rem;">
          <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Total Spent to Date</span>
          <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:var(--gold); margin-top:0.25rem;">
            ₹<?php echo number_format($totalSpent); ?>
          </div>
          <div style="font-size:0.8rem; color:var(--teal); font-weight:600; margin-top:0.25rem;">
            <?php echo $percentSpent; ?>% of total budget utilized
          </div>
        </div>

        <div class="card card-gradient" style="padding:1.5rem;">
          <span style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--text-muted);">Remaining Funds</span>
          <div style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:<?php echo ($remaining < 5000) ? 'var(--accent)' : 'var(--success)'; ?>; margin-top:0.25rem;">
            ₹<?php echo number_format($remaining); ?>
          </div>
          <div style="font-size:0.8rem; color:var(--text-dim); margin-top:0.25rem;">Available balance</div>
        </div>
      </div>

      <!-- Budget Visual Progress Bar -->
      <div class="card" style="padding:1.5rem; margin-bottom:2.5rem;">
        <div style="display:flex; justify-content:space-between; font-size:0.88rem; font-weight:600; margin-bottom:0.5rem;">
          <span>Budget Utilization Progress</span>
          <span style="color:var(--gold);"><?php echo $percentSpent; ?>%</span>
        </div>
        <div class="progress-bar-wrap" style="height:12px;">
          <div class="progress-fill" style="width: <?php echo $percentSpent; ?>%; background: <?php echo ($percentSpent > 90) ? 'var(--danger)' : 'var(--grad-primary)'; ?>;"></div>
        </div>
      </div>

      <!-- Two Column Layout: Categories & Log Table -->
      <div style="display:grid; grid-template-columns: 1fr 1.8fr; gap:2rem; align-items:start;">
        
        <!-- Left: Category Spending Breakdown & Log Expense Form -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
          
          <!-- Category Spending Breakdown -->
          <div class="card" style="padding:1.75rem;">
            <h3 style="font-size:1.2rem; margin-bottom:1.25rem;">Category Spending (₹)</h3>
            
            <?php
            $catIcons = [
                'stay'       => '🏨 Stays & Resorts',
                'transport'  => '🚕 Transport & Flights',
                'meals'      => '🍛 Food & Dining',
                'activities' => '🏄 Experiences & Guides',
                'shopping'   => '🛍️ Spices & Souvenirs',
                'other'      => '📦 Miscellaneous'
            ];
            foreach ($catTotals as $catKey => $catAmt): 
                $catPct = ($totalSpent > 0) ? round(($catAmt / $totalSpent) * 100) : 0;
            ?>
              <div style="margin-bottom:1rem;">
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:0.25rem;">
                  <span style="font-weight:600;"><?php echo $catIcons[$catKey] ?? $catKey; ?></span>
                  <span style="font-family:'Outfit',sans-serif; font-weight:700; color:#fff;">₹<?php echo number_format($catAmt); ?> (<?php echo $catPct; ?>%)</span>
                </div>
                <div class="progress-bar-wrap" style="height:6px;">
                  <div class="progress-fill" style="width:<?php echo $catPct; ?>%; background:var(--grad-sunset);"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Log Expense Form Card -->
          <div class="card" style="padding:1.75rem;">
            <span class="badge badge-teal" style="margin-bottom:0.4rem;">Quick Entry</span>
            <h3 style="font-size:1.2rem; margin-bottom:1.25rem;">+ Log New Expense (₹)</h3>

            <form method="POST" action="budget.php?trip_id=<?php echo $selectedTripId; ?>">
              <input type="hidden" name="action" value="add_expense">
              <input type="hidden" name="trip_id" value="<?php echo $selectedTripId; ?>">

              <div class="form-group">
                <label class="form-label">Amount (₹ INR) *</label>
                <input type="number" name="amount" placeholder="2500" step="50" required>
              </div>

              <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category" required>
                  <option value="stay">🏨 Hotel / Resort Stay</option>
                  <option value="transport">🚕 Taxi / Train / Flight</option>
                  <option value="meals">🍛 Meals &amp; Chai</option>
                  <option value="activities">🏄 Activity / Tour Ticket</option>
                  <option value="shopping">🛍️ Shopping / Souvenirs</option>
                  <option value="other">📦 Other Expense</option>
                </select>
              </div>

              <?php if (!empty($tripStops)): ?>
                <div class="form-group">
                  <label class="form-label">Linked Stop (Optional)</label>
                  <select name="stop_id">
                    <option value="">-- General Trip Expense --</option>
                    <?php foreach ($tripStops as $s): ?>
                      <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>

              <div class="form-group">
                <label class="form-label">Expense Date</label>
                <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
              </div>

              <div class="form-group">
                <label class="form-label">Description / Note</label>
                <input type="text" name="note" placeholder="e.g. Amber Fort guide + entry tickets">
              </div>

              <button type="submit" class="btn btn-primary btn-block">
                <span>Save Expense (₹) 💸</span>
              </button>
            </form>
          </div>

        </div>

        <!-- Right: Transaction History Table -->
        <div class="card" style="padding:1.75rem;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h3 style="font-size:1.3rem;">Logged Transactions (<?php echo count($expenses); ?>)</h3>
            <span class="badge badge-gold">₹ INR Currency</span>
          </div>

          <?php if (empty($expenses)): ?>
            <div style="text-align:center; padding:3rem 1rem;">
              <span style="font-size:2.5rem; display:block; margin-bottom:0.75rem;">🧾</span>
              <p class="text-muted">No expenses recorded for this journey yet. Use the form on the left to add one.</p>
            </div>
          <?php else: ?>
            <div style="overflow-x:auto;">
              <table style="width:100%; border-collapse:collapse; font-size:0.88rem;">
                <thead>
                  <tr style="border-bottom:1px solid var(--border); color:var(--text-dim); text-align:left;">
                    <th style="padding:0.75rem 0.5rem;">Date</th>
                    <th style="padding:0.75rem 0.5rem;">Category</th>
                    <th style="padding:0.75rem 0.5rem;">Description</th>
                    <th style="padding:0.75rem 0.5rem; text-align:right;">Amount (₹)</th>
                    <th style="padding:0.75rem 0.5rem; text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($expenses as $exp): ?>
                    <tr style="border-bottom:1px solid var(--border);">
                      <td style="padding:0.85rem 0.5rem; color:var(--text-muted);">
                        <?php echo date('M d, Y', strtotime($exp['expense_date'])); ?>
                      </td>
                      <td style="padding:0.85rem 0.5rem;">
                        <span class="badge badge-primary" style="font-size:0.72rem;">
                          <?php echo ucfirst($exp['category']); ?>
                        </span>
                      </td>
                      <td style="padding:0.85rem 0.5rem;">
                        <div style="font-weight:600; color:#fff;"><?php echo htmlspecialchars($exp['note'] ?: ucfirst($exp['category'])); ?></div>
                        <?php if (!empty($exp['section_name'])): ?>
                          <div style="font-size:0.75rem; color:var(--text-dim);">📍 <?php echo htmlspecialchars($exp['section_name']); ?></div>
                        <?php endif; ?>
                      </td>
                      <td style="padding:0.85rem 0.5rem; text-align:right; font-family:'Outfit',sans-serif; font-weight:800; color:var(--gold);">
                        ₹<?php echo number_format($exp['amount']); ?>
                      </td>
                      <td style="padding:0.85rem 0.5rem; text-align:center;">
                        <form method="POST" action="budget.php?trip_id=<?php echo $selectedTripId; ?>" onsubmit="return confirm('Delete this expense?');" style="display:inline;">
                          <input type="hidden" name="delete_expense_id" value="<?php echo $exp['id']; ?>">
                          <button type="submit" style="background:none; border:none; color:var(--danger); cursor:pointer;" title="Delete">🗑️</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

      </div>

    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
