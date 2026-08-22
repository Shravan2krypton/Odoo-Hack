<?php
include 'includes/db_connect.php';
session_start();

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?auth=required");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sqlUser = "SELECT first_name, last_name, email, phone, city, country, extra_info, role 
            FROM users WHERE id=?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();

// Fetch trips by status
function fetchTrips($conn, $user_id, $status) {
    $sql = "SELECT id, name, start_date, end_date FROM trips WHERE user_id=? AND status=? ORDER BY start_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $status);
    $stmt->execute();
    return $stmt->get_result();
}
$plannedTrips = fetchTrips($conn, $user_id, 'planned');
$ongoingTrips = fetchTrips($conn, $user_id, 'ongoing');
$completedTrips = fetchTrips($conn, $user_id, 'completed');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>GlobalTraveler Dashboard</h1>
    <div class="profile-header">
        <img src="assets/images/user_placeholder.png" alt="User Image" width="100">
        <div class="user-details">
            <h2><?php echo $user['first_name']." ".$user['last_name']; ?></h2>
            <p>Email: <?php echo $user['email']; ?></p>
            <p>Phone: <?php echo $user['phone']; ?></p>
            <p>City: <?php echo $user['city']; ?></p>
            <p>Country: <?php echo $user['country']; ?></p>
            <p>Info: <?php echo $user['extra_info']; ?></p>
            <a href="edit_profile.php">Edit Profile</a>
        </div>
    </div>

    <h3>Upcoming Trips</h3>
    <?php while($trip = $plannedTrips->fetch_assoc()) { ?>
        <div class="trip-box">
            <p><?php echo $trip['name']." (".$trip['start_date']." - ".$trip['end_date'].")"; ?></p>
            <a href="itinerary_view.php?id=<?php echo $trip['id']; ?>">View</a>
        </div>
    <?php } ?>

    <h3>Ongoing Trips</h3>
    <?php while($trip = $ongoingTrips->fetch_assoc()) { ?>
        <div class="trip-box">
            <p><?php echo $trip['name']." (".$trip['start_date']." - ".$trip['end_date'].")"; ?></p>
            <a href="itinerary_view.php?id=<?php echo $trip['id']; ?>">View</a>
        </div>
    <?php } ?>

    <h3>Completed Trips</h3>
    <?php while($trip = $completedTrips->fetch_assoc()) { ?>
        <div class="trip-box">
            <p><?php echo $trip['name']." (".$trip['start_date']." - ".$trip['end_date'].")"; ?></p>
            <a href="itinerary_view.php?id=<?php echo $trip['id']; ?>">View</a>
        </div>
    <?php } ?>
</body>
</html>
