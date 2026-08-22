<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Search</title>
    <meta name="description" content="Search travel activities by type, cost and duration.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.jpg" type="image/jpeg" />
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/api.js" defer></script>
</head>
<body>
    <h1>Search Activities</h1>
    <form method="GET" action="" class="search-form">
        <select name="type" id="typeSelect">
            <option value="">All Types</option>
            <option value="Sightseeing">Sightseeing</option>
            <option value="Adventure">Adventure</option>
            <option value="Food">Food</option>
            <option value="Culture">Culture</option>
        </select>
        <input type="number" name="min_cost" placeholder="Min Cost" min="0" />
        <input type="number" name="max_cost" placeholder="Max Cost" min="0" />
        <input type="number" name="min_duration" placeholder="Min Duration (hrs)" min="0" />
        <input type="number" name="max_duration" placeholder="Max Duration (hrs)" min="0" />
        <button type="submit">Search</button>
    </form>
    <div class="cards-container">
    <?php
    // Build query based on filters
    $conditions = [];
    $params = [];
    $types = '';
    if (!empty($_GET['type'])) {
        $conditions[] = "type = ?";
        $params[] = $_GET['type'];
        $types .= 's';
    }
    if (!empty($_GET['min_cost'])) {
        $conditions[] = "cost >= ?";
        $params[] = (int)$_GET['min_cost'];
        $types .= 'i';
    }
    if (!empty($_GET['max_cost'])) {
        $conditions[] = "cost <= ?";
        $params[] = (int)$_GET['max_cost'];
        $types .= 'i';
    }
    if (!empty($_GET['min_duration'])) {
        $conditions[] = "duration >= ?";
        $params[] = (int)$_GET['min_duration'];
        $types .= 'i';
    }
    if (!empty($_GET['max_duration'])) {
        $conditions[] = "duration <= ?";
        $params[] = (int)$_GET['max_duration'];
        $types .= 'i';
    }
    $sql = "SELECT id, name, description, cost, duration FROM activities";
    if ($conditions) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<div class='card'>";
        echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
        echo "<p>Cost: $" . $row['cost'] . " | Duration: " . $row['duration'] . "h</p>";
        echo "<form method='POST' action='itinerary_builder.php'>";
        echo "<input type='hidden' name='activity_id' value='" . $row['id'] . "' />";
        echo "<button type='submit'>Add to Itinerary</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>
    </div>
</body>
</html>
