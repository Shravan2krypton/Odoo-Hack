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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobalTrutter - Create New Trip</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary: #31A4FE;
            --primary-hover: #258ae0;
            --bg-color: #f0f4f8;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --error-color: #ef4444;
            --success-color: #10b981;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0f0ff 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
        }

        .container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            box-sizing: border-box;
        }

        .card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .card-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .card-header h1 {
            margin: 0 0 10px;
            font-size: 28px;
            color: var(--text-main);
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .card-header p {
            margin: 0;
            color: var(--text-muted);
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            font-size: 15px;
            color: var(--text-main);
            background-color: #f8fafc;
            transition: all 0.3s ease;
            box-sizing: border-box;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(49, 164, 254, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .submit-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(49, 164, 254, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn.loading {
            background-color: var(--primary-hover);
            pointer-events: none;
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        .submit-btn.loading .spinner {
            display: inline-block;
        }
        .submit-btn.loading .btn-text {
            display: none;
        }
        .submit-btn.loading::after {
            content: 'Creating Trip...';
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
        }
        
        .message.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .message.error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* Decorative blobs */
        .blob-1, .blob-2 {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            z-index: -1;
            opacity: 0.5;
        }
        .blob-1 {
            width: 300px;
            height: 300px;
            background: rgba(49, 164, 254, 0.3);
            top: -100px;
            right: -100px;
        }
        .blob-2 {
            width: 250px;
            height: 250px;
            background: rgba(226, 232, 240, 0.8);
            bottom: -80px;
            left: -80px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="card">
            <div class="blob-1"></div>
            <div class="blob-2"></div>
            
            <div class="card-header">
                <h1>Plan a New Trip</h1>
                <p>Fill out the details below to start your next adventure</p>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="createTripForm">
                <div class="form-group">
                    <label>Trip Name</label>
                    <input type="text" name="name" placeholder="e.g., Summer in Paris" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Country</label>
                        <select name="country_id" id="country" required>
                            <option value="">-- Select Country --</option>
                            <?php while($row = $countries->fetch_assoc()) { ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>City</label>
                        <select name="city_id" id="city" required>
                            <option value="">-- Select City --</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" required>
                    </div>

                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" placeholder="Any specific plans, flights, or accommodation info..."></textarea>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <div class="spinner"></div>
                    <span class="btn-text">Create Trip</span>
                </button>
            </form>

            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
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

            // Form submission loading effect
            $('#createTripForm').on('submit', function() {
                $('#submitBtn').addClass('loading');
            });
        });
    </script>
</body>
</html>
