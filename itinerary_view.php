<?php
include 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$trip_id = intval($_GET['trip_id']);

// Fetch sections
$sql = "SELECT * FROM itinerary_sections WHERE trip_id=? ORDER BY start_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$sections = $stmt->get_result();

// Fetch activities linked to trip
$sql2 = "SELECT a.name, a.description, ta.scheduled_date, a.price 
         FROM trip_activities ta
         JOIN activities a ON ta.activity_id = a.id
         WHERE ta.trip_id=? ORDER BY ta.scheduled_date ASC";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $trip_id);
$stmt2->execute();
$activities = $stmt2->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Itinerary View</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Itinerary for Trip #<?php echo $trip_id; ?></h1>

    <h2>Sections</h2>
    <?php while($sec = $sections->fetch_assoc()) { ?>
        <div class="section-card">
            <p><strong><?php echo $sec['section_name']; ?></strong></p>
            <p>Date Range: <?php echo $sec['start_date']; ?> to <?php echo $sec['end_date']; ?></p>
            <p>Budget: <?php echo $sec['budget']; ?></p>
        </div>
    <?php } ?>

    <h2>Activities & Expenses</h2>
    <?php while($act = $activities->fetch_assoc()) { ?>
        <div class="activity-card">
            <p><strong><?php echo $act['name']; ?></strong></p>
            <p><?php echo $act['description']; ?></p>
            <p>Date: <?php echo $act['scheduled_date']; ?></p>
            <p>Expense: <?php echo $act['price']; ?></p>
        </div>
    <?php } ?>
</body>
</html>
