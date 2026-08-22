<?php
include 'includes/db_connect.php';
session_start();

// Fetch top regions (always visible)
$sql = "SELECT id, name, description, image_url FROM regions LIMIT 5";
$regions = $conn->query($sql);

// Fetch previous trips (only if logged in)
$prevTrips = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id, name, start_date, end_date, cover_photo 
            FROM trips 
            WHERE user_id=? AND status='completed' 
            ORDER BY end_date DESC 
            LIMIT 2";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $prevTrips = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GlobalTrutter - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Banner -->
    <div class="banner">
        <img src="assets/images/banner.jpg" alt="Banner" style="width:100%; height:200px;">
    </div>

    <!-- Search Bar -->
    <div class="search-bar">
        <input type="text" placeholder="Search trips or regions...">
        <button>Group by</button>
        <button>Filter</button>
        <button>Sort by</button>
    </div>

    <!-- Top Regional Selections -->
    <h2>Top Regional Selections</h2>
    <div class="regions">
        <?php while($row = $regions->fetch_assoc()) { ?>
            <div class="region-card">
                <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>" style="width:150px; height:100px;">
                <h3><?php echo $row['name']; ?></h3>
                <p><?php echo $row['description']; ?></p>
            </div>
        <?php } ?>
    </div>

    <!-- Previous Trips -->
    <h2>Previous Trips</h2>
    <div class="trips">
        <?php if ($prevTrips && $prevTrips->num_rows > 0) { ?>
            <?php while($trip = $prevTrips->fetch_assoc()) { ?>
                <div class="trip-card">
                    <img src="<?php echo $trip['cover_photo']; ?>" alt="<?php echo $trip['name']; ?>" style="width:200px; height:120px;">
                    <h3><?php echo $trip['name']; ?></h3>
                    <p><?php echo $trip['start_date']; ?> to <?php echo $trip['end_date']; ?></p>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>Please <a href="login.php">login</a> to see your previous trips.</p>
        <?php } ?>
    </div>

    <!-- Plan a Trip Button -->
    <div style="text-align:right; margin-top:20px;">
        <?php if (isset($_SESSION['user_id'])) { ?>
            <a href="create_trip.php"><button>+ Plan a trip</button></a>
        <?php } else { ?>
            <a href="login.php"><button>Login to plan a trip</button></a>
        <?php } ?>
    </div>
</body>
</html>
