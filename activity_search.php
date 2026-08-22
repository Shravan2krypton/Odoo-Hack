<?php
include 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$city_id = isset($_GET['city_id']) ? intval($_GET['city_id']) : 0;
$activities = [];

if ($city_id) {
    $sql = "SELECT * FROM activities WHERE city_id=? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $city_id);
    $stmt->execute();
    $activities = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Activity Search</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>GlobalTrotter - Activity Search</h1>
    <form method="GET" action="">
        <label>Select City:</label>
        <select name="city_id" onchange="this.form.submit()">
            <option value="">-- Choose City --</option>
            <?php
            $cities = $conn->query("SELECT id, name FROM city ORDER BY name ASC");
            while ($c = $cities->fetch_assoc()) {
                $selected = ($c['id'] == $city_id) ? "selected" : "";
                echo "<option value='{$c['id']}' $selected>{$c['name']}</option>";
            }
            ?>
        </select>
    </form>

    <div class="results">
        <h2>Results</h2>
        <?php if ($activities && $activities->num_rows > 0) {
            while ($act = $activities->fetch_assoc()) { ?>
                <div class="activity-card">
                    <p><?php echo $act['name']; ?></p>
                    <p><?php echo $act['description']; ?></p>
                    <a href="itinerary_builder.php?activity_id=<?php echo $act['id']; ?>">Add to Trip</a>
                </div>
        <?php } } else { echo "<p>No activities found.</p>"; } ?>
    </div>
</body>
</html>
