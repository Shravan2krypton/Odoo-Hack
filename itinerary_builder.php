<?php
include 'includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$trip_id = intval($_GET['trip_id']);
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_name = trim($_POST['section_name']);
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];
    $budget       = floatval($_POST['budget']);

    if (!empty($section_name) && !empty($start_date) && !empty($end_date)) {
        $sql = "INSERT INTO itinerary_sections (trip_id, section_name, start_date, end_date, budget) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssd", $trip_id, $section_name, $start_date, $end_date, $budget);
        $stmt->execute();
        $message = "Section added successfully!";
    } else {
        $message = "All fields are required.";
    }
}

// Fetch existing sections
$sql = "SELECT * FROM itinerary_sections WHERE trip_id=? ORDER BY start_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$sections = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Build Itinerary</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Build Itinerary</h1>
    <?php if ($message) echo "<p>$message</p>"; ?>

    <form method="POST" action="">
        <label>Section Name:</label>
        <input type="text" name="section_name" required><br>

        <label>Date Range:</label>
        <input type="date" name="start_date" required> to 
        <input type="date" name="end_date" required><br>

        <label>Budget:</label>
        <input type="number" name="budget" step="0.01"><br>

        <button type="submit">+ Add Section</button>
    </form>

    <h2>Existing Sections</h2>
    <?php while($sec = $sections->fetch_assoc()) { ?>
        <div class="section-card">
            <p><strong><?php echo $sec['section_name']; ?></strong></p>
            <p>Date Range: <?php echo $sec['start_date']; ?> to <?php echo $sec['end_date']; ?></p>
            <p>Budget: <?php echo $sec['budget']; ?></p>
        </div>
    <?php } ?>
</body>
</html>
