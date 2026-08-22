<?php
include 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch trips
$sql = "SELECT * FROM trips WHERE user_id=? ORDER BY start_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$ongoing = $upcoming = $completed = [];

while ($trip = $result->fetch_assoc()) {
    $today = date("Y-m-d");
    if ($trip['start_date'] <= $today && $trip['end_date'] >= $today) {
        $ongoing[] = $trip;
    } elseif ($trip['start_date'] > $today) {
        $upcoming[] = $trip;
    } else {
        $completed[] = $trip;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Trips</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>GlobalTrotter - My Trips</h1>
    <div class="section">
        <h2>Ongoing</h2>
        <?php foreach ($ongoing as $trip) { ?>
            <div class="trip-card">
                <p><?php echo $trip['name']; ?></p>
                <a href="itinerary_view.php?trip_id=<?php echo $trip['id']; ?>">View</a>
            </div>
        <?php } ?>
    </div>

    <div class="section">
        <h2>Upcoming</h2>
        <?php foreach ($upcoming as $trip) { ?>
            <div class="trip-card">
                <p><?php echo $trip['name']; ?></p>
                <a href="itinerary_builder.php?trip_id=<?php echo $trip['id']; ?>">Plan</a>
            </div>
        <?php } ?>
    </div>

    <div class="section">
        <h2>Completed</h2>
        <?php foreach ($completed as $trip) { ?>
            <div class="trip-card">
                <p><?php echo $trip['name']; ?></p>
                <a href="itinerary_view.php?trip_id=<?php echo $trip['id']; ?>">View</a>
            </div>
        <?php } ?>
    </div>
</body>
</html>
