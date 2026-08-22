<?php
include 'includes/db_connect.php';
session_start();

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?auth=required");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $country_id = intval($_POST['country_id']);
    $city_id    = intval($_POST['city_id']);
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $notes      = trim($_POST['notes']);

    if (!empty($name) && $country_id && $city_id && !empty($start_date) && !empty($end_date)) {
        $sql = "INSERT INTO trips (user_id, name, country_id, city_id, start_date, end_date, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'planned')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiisss", $user_id, $name, $country_id, $city_id, $start_date, $end_date, $notes);

        if ($stmt->execute()) {
            // Log action
            $log = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, 'INSERT', 'trips', ?)");
            $log->bind_param("ii", $user_id, $stmt->insert_id);
            $log->execute();

            $message = "Trip created successfully!";
        } else {
            $message = "Error creating trip: " . $stmt->error;
        }
    } else {
        $message = "All fields are required (including Country and City).";
    }
}

// Fetch countries for dropdown
$sql = "SELECT id, name FROM country ORDER BY name ASC";
$countries = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create New Trip</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Plan a New Trip</h1>
    <?php if ($message) echo "<p>$message</p>"; ?>

    <form method="POST" action="">
        <label>Trip Name:</label>
        <input type="text" name="name" required><br>

        <label>Country:</label>
        <select name="country_id" id="country" required>
            <option value="">-- Select Country --</option>
            <?php while($row = $countries->fetch_assoc()) { ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
            <?php } ?>
        </select><br>

        <label>City:</label>
        <select name="city_id" id="city" required>
            <option value="">-- Select City --</option>
        </select><br>

        <label>Start Date:</label>
        <input type="date" name="start_date" required><br>

        <label>End Date:</label>
        <input type="date" name="end_date" required><br>

        <label>Notes:</label>
        <textarea name="notes"></textarea><br>

        <button type="submit">Create Trip</button>
    </form>

    <script>
    // Load cities dynamically based on selected country
    $('#country').change(function(){
        var countryId = $(this).val();
        if(countryId) {
            $.ajax({
                url: 'get_cities.php',
                type: 'POST',
                data: {country_id: countryId},
                success: function(data) {
                    $('#city').html(data);
                }
            });
        } else {
            $('#city').html('<option value="">-- Select City --</option>');
        }
    });
    </script>
</body>
</html>
