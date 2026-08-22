<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db_connect.php';

$userId = require_auth();

// Determine current year and month
$year  = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));

if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$firstDayTimestamp = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth       = date('t', $firstDayTimestamp);
$dayOfWeekOffset   = date('w', $firstDayTimestamp); // 0 = Sunday, 1 = Monday...
$monthName         = date('F', $firstDayTimestamp);

// Fetch user's trips for this month range
$startDateMonth = sprintf('%04d-%02d-01', $year, $month);
$endDateMonth   = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

$tripsStmt = $conn->prepare("SELECT id, name, start_date, end_date, status, cover_photo 
    FROM trips 
    WHERE user_id = ? 
    AND (start_date <= ? AND end_date >= ?) 
    ORDER BY start_date ASC");
$tripsStmt->bind_param("iss", $userId, $endDateMonth, $startDateMonth);
$tripsStmt->execute();
$monthTrips = $tripsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch all scheduled activities in this month
$actsQuery = $conn->query("SELECT ta.*, a.name AS activity_name, a.category, s.start_date, s.section_name, t.name AS trip_name, t.id AS trip_id 
    FROM trip_activities ta 
    JOIN activities a ON ta.activity_id = a.id 
    JOIN itinerary_sections s ON ta.stop_id = s.id 
    JOIN trips t ON s.trip_id = t.id 
    WHERE t.user_id = {$userId} 
    AND (s.start_date BETWEEN '{$startDateMonth}' AND '{$endDateMonth}')");
$monthActivities = $actsQuery->fetch_all(MYSQLI_ASSOC);

// Map activities and trips by day
$dayEvents = [];
for ($d = 1; $d <= $daysInMonth; $d++) {
    $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
    $dayEvents[$d] = [
        'trips'      => [],
        'activities' => []
    ];

    foreach ($monthTrips as $t) {
        if ($currentDate >= $t['start_date'] && $currentDate <= $t['end_date']) {
            $dayEvents[$d]['trips'][] = $t;
        }
    }

    foreach ($monthActivities as $a) {
        if ($currentDate === $a['start_date']) {
            $dayEvents[$d]['activities'][] = $a;
        }
    }
}

$pageTitle = "Travel Calendar — {$monthName} {$year}";
include 'includes/header.php';
?>

<div class="main-content">
  <div class="container">
    
    <!-- Calendar Top Bar -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem; margin-bottom:2.5rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border);">
      <div>
        <span class="badge badge-primary" style="margin-bottom:0.4rem;">Schedule &amp; Dates</span>
        <h1 style="font-size:2.2rem; font-weight:800;">Travel Calendar</h1>
        <p class="text-muted">Visual timeline of upcoming journeys, stopovers, and scheduled Indian adventures.</p>
      </div>

      <!-- Month Navigation Controls -->
      <div style="display:flex; align-items:center; gap:0.75rem;">
        <a href="calendar.php?year=<?php echo ($month == 1 ? $year - 1 : $year); ?>&month=<?php echo ($month == 1 ? 12 : $month - 1); ?>" class="btn btn-secondary btn-sm">
          ← Previous
        </a>
        <div style="font-family:'Outfit',sans-serif; font-size:1.3rem; font-weight:800; min-width:180px; text-align:center; color:#fff;">
          <?php echo "{$monthName} {$year}"; ?>
        </div>
        <a href="calendar.php?year=<?php echo ($month == 12 ? $year + 1 : $year); ?>&month=<?php echo ($month == 12 ? 1 : $month + 1); ?>" class="btn btn-secondary btn-sm">
          Next →
        </a>
      </div>
    </div>

    <!-- Calendar Grid Card -->
    <div class="card" style="padding:1.5rem; overflow-x:auto;">
      
      <!-- Weekday Headers -->
      <div style="display:grid; grid-template-columns:repeat(7, minmax(130px, 1fr)); gap:0.5rem; text-align:center; font-family:'Outfit',sans-serif; font-size:0.85rem; font-weight:700; color:var(--text-dim); text-transform:uppercase; margin-bottom:0.75rem;">
        <div>Sun</div>
        <div>Mon</div>
        <div>Tue</div>
        <div>Wed</div>
        <div>Thu</div>
        <div>Fri</div>
        <div>Sat</div>
      </div>

      <!-- Calendar Days Grid -->
      <div style="display:grid; grid-template-columns:repeat(7, minmax(130px, 1fr)); gap:0.5rem;">
        
        <!-- Leading empty days offset -->
        <?php for ($i = 0; $i < $dayOfWeekOffset; $i++): ?>
          <div style="background:rgba(255,255,255,0.01); border-radius:var(--radius-sm); min-height:110px; opacity:0.3;"></div>
        <?php endfor; ?>

        <!-- Days of Month -->
        <?php for ($day = 1; $day <= $daysInMonth; $day++): 
            $isToday = ($year == intval(date('Y')) && $month == intval(date('m')) && $day == intval(date('d')));
            $events = $dayEvents[$day];
        ?>
          <div style="background:<?php echo $isToday ? 'rgba(99,102,241,0.15)' : 'rgba(13,19,34,0.7)'; ?>; border:1px solid <?php echo $isToday ? 'var(--primary)' : 'var(--border)'; ?>; border-radius:var(--radius-sm); padding:0.6rem; min-height:110px; display:flex; flex-direction:column; justify-content:space-between;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <span style="font-family:'Outfit',sans-serif; font-weight:800; font-size:0.95rem; color:<?php echo $isToday ? 'var(--primary)' : '#fff'; ?>;">
                <?php echo $day; ?>
              </span>
              <?php if ($isToday): ?>
                <span class="badge badge-primary" style="font-size:0.65rem; padding:0.1rem 0.35rem;">Today</span>
              <?php endif; ?>
            </div>

            <!-- Day Events Container -->
            <div style="display:flex; flex-direction:column; gap:0.35rem; margin-top:0.5rem;">
              <?php foreach ($events['trips'] as $t): ?>
                <a href="itinerary_view.php?trip_id=<?php echo $t['id']; ?>" style="background:rgba(99,102,241,0.25); border-left:3px solid var(--primary); padding:0.25rem 0.4rem; border-radius:4px; font-size:0.75rem; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-decoration:none;" title="<?php echo htmlspecialchars($t['name']); ?>">
                  ✈️ <?php echo htmlspecialchars($t['name']); ?>
                </a>
              <?php endforeach; ?>

              <?php foreach ($events['activities'] as $act): ?>
                <div style="background:rgba(20,184,166,0.25); border-left:3px solid var(--teal); padding:0.2rem 0.4rem; border-radius:4px; font-size:0.72rem; font-weight:600; color:var(--teal); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($act['activity_name']); ?>">
                  🏄 <?php echo htmlspecialchars($act['activity_name']); ?>
                </div>
              <?php endforeach; ?>
            </div>

            <div style="text-align:right;"></div>
          </div>
        <?php endfor; ?>

      </div>
    </div>

    <!-- Active Journeys in this month -->
    <div style="margin-top:2.5rem;">
      <h3 style="font-size:1.4rem; margin-bottom:1rem;">Journeys Scheduled in <?php echo "{$monthName} {$year}"; ?> (<?php echo count($monthTrips); ?>)</h3>
      <?php if (empty($monthTrips)): ?>
        <p class="text-muted">No trips scheduled during this month.</p>
      <?php else: ?>
        <div class="grid grid-3">
          <?php foreach ($monthTrips as $t): ?>
            <div class="card card-interactive" style="padding:1.25rem; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <h4 style="font-size:1.1rem; color:#fff; margin-bottom:0.25rem;"><?php echo htmlspecialchars($t['name']); ?></h4>
                <div style="font-size:0.8rem; color:var(--text-muted);">
                  🗓️ <?php echo date('M d', strtotime($t['start_date'])); ?> → <?php echo date('M d, Y', strtotime($t['end_date'])); ?>
                </div>
              </div>
              <a href="itinerary_view.php?trip_id=<?php echo $t['id']; ?>" class="btn btn-primary btn-sm">View →</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
