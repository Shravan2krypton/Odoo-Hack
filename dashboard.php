<?php
include 'includes/db_connect.php';
session_start();

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?auth=required");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details with joins for city/country names
$sqlUser = "SELECT u.first_name, u.last_name, u.email, u.phone, 
                   c.name AS country_name, ci.name AS city_name, 
                   u.extra_info, u.role
            FROM users u
            LEFT JOIN country c ON u.country_id = c.id
            LEFT JOIN city ci ON u.city_id = ci.id
            WHERE u.id=?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();

// Fetch trips by status
function fetchTrips($conn, $user_id, $status) {
    $sql = "SELECT id, name, start_date, end_date 
            FROM trips 
            WHERE user_id=? AND status=? 
            ORDER BY start_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $status);
    $stmt->execute();
    return $stmt->get_result();
}
$plannedTrips   = fetchTrips($conn, $user_id, 'planned');
$ongoingTrips   = fetchTrips($conn, $user_id, 'ongoing');
$completedTrips = fetchTrips($conn, $user_id, 'completed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GlobalTraveler</title>
    <style>
        :root {
            --primary: #31A4FE;
            --primary-hover: #258ae0;
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-main: #333333;
            --text-muted: #666666;
            --border-color: #e0e0e0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
        }
        
        /* Navbar */
        .navbar {
            background-color: var(--card-bg);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar h1 {
            margin: 0;
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
        }
        .nav-links {
            display: flex;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            margin-left: 20px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a:hover {
            color: var(--primary);
        }
        .nav-links .logout-btn {
            background-color: #fff1f0;
            color: #ff4d4f;
            padding: 8px 16px;
            border-radius: 6px;
        }
        .nav-links .logout-btn:hover {
            background-color: #ff4d4f;
            color: white;
        }

        /* Layout */
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        /* Profile Card */
        .profile-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(49, 164, 254, 0.08);
            border-top: 4px solid var(--primary);
            text-align: center;
            align-self: start;
        }
        .profile-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f0f7ff;
            margin-bottom: 15px;
        }
        .profile-card h2 {
            margin: 0 0 5px;
            font-size: 20px;
            color: var(--text-main);
        }
        .profile-card .email {
            color: var(--primary);
            font-size: 14px;
            margin-bottom: 20px;
            word-break: break-all;
        }
        .profile-details {
            text-align: left;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        .profile-details p {
            margin: 10px 0;
            font-size: 14px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
        }
        .profile-details strong {
            color: var(--text-main);
            width: 70px;
            display: inline-block;
        }
        .edit-profile-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: var(--primary);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: 600;
            transition: background-color 0.2s;
            box-sizing: border-box;
        }
        .edit-profile-btn:hover {
            background-color: var(--primary-hover);
        }

        /* Content Area */
        .content-area h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
            color: var(--text-main);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .trip-section {
            margin-bottom: 40px;
        }
        .trip-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .trip-box {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid rgba(49, 164, 254, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .trip-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(49, 164, 254, 0.12);
        }
        .trip-name {
            font-weight: 600;
            font-size: 16px;
            margin: 0 0 10px 0;
            color: var(--text-main);
        }
        .trip-dates {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
            background: #f0f7ff;
            padding: 6px 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .view-btn {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            text-align: center;
            padding: 8px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .view-btn:hover {
            background-color: var(--primary);
            color: white;
        }
        .empty-state {
            color: var(--text-muted);
            font-style: italic;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px dashed var(--border-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
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

    <div class="dashboard-container">
        <!-- Sidebar / Profile -->
        <div class="profile-card">
            <!-- Dynamically load avatar with initials if real image is missing -->
            <img src="assets/images/user_placeholder.png" alt="User Image" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['first_name'].' '.$user['last_name']); ?>&background=31A4FE&color=fff'">
            <h2><?php echo htmlspecialchars($user['first_name']." ".$user['last_name']); ?></h2>
            <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
            
            <div class="profile-details">
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
                <p><strong>Country:</strong> <?php echo htmlspecialchars($user['country']); ?></p>
                <p><strong>Info:</strong> <?php echo htmlspecialchars($user['extra_info'] ?: 'None'); ?></p>
            </div>
            
            <a href="edit_profile.php" class="edit-profile-btn">Edit Profile</a>
        </div>

        <!-- Main Content -->
        <div class="content-area">
            
            <!-- Ongoing Trips -->
            <div class="trip-section">
                <h3><span style="color: #ff9800;">●</span> Ongoing Trips</h3>
                <?php if($ongoingTrips->num_rows > 0): ?>
                    <div class="trip-grid">
                        <?php while($trip = $ongoingTrips->fetch_assoc()) { ?>
                            <div class="trip-box" style="border-left: 4px solid #ff9800;">
                                <div>
                                    <h4 class="trip-name"><?php echo htmlspecialchars($trip['name']); ?></h4>
                                    <div class="trip-dates"><?php echo htmlspecialchars($trip['start_date'])." - ".htmlspecialchars($trip['end_date']); ?></div>
                                </div>
                                <a href="itinerary_view.php?id=<?php echo $trip['id']; ?>" class="view-btn">View Itinerary</a>
                            </div>
                        <?php } ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">No ongoing trips at the moment.</div>
                <?php endif; ?>
            </div>

            <!-- Upcoming Trips -->
            <div class="trip-section">
                <h3><span style="color: var(--primary);">●</span> Upcoming Trips</h3>
                <?php if($plannedTrips->num_rows > 0): ?>
                    <div class="trip-grid">
                        <?php while($trip = $plannedTrips->fetch_assoc()) { ?>
                            <div class="trip-box" style="border-left: 4px solid var(--primary);">
                                <div>
                                    <h4 class="trip-name"><?php echo htmlspecialchars($trip['name']); ?></h4>
                                    <div class="trip-dates"><?php echo htmlspecialchars($trip['start_date'])." - ".htmlspecialchars($trip['end_date']); ?></div>
                                </div>
                                <a href="itinerary_view.php?id=<?php echo $trip['id']; ?>" class="view-btn">View Itinerary</a>
                            </div>
                        <?php } ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">No upcoming trips planned.</div>
                <?php endif; ?>
            </div>

            <!-- Completed Trips -->
            <div class="trip-section">
                <h3><span style="color: #4caf50;">●</span> Completed Trips</h3>
                <?php if($completedTrips->num_rows > 0): ?>
                    <div class="trip-grid">
                        <?php while($trip = $completedTrips->fetch_assoc()) { ?>
                            <div class="trip-box" style="border-left: 4px solid #4caf50; opacity: 0.8;">
                                <div>
                                    <h4 class="trip-name"><?php echo htmlspecialchars($trip['name']); ?></h4>
                                    <div class="trip-dates"><?php echo htmlspecialchars($trip['start_date'])." - ".htmlspecialchars($trip['end_date']); ?></div>
                                </div>
                                <a href="itinerary_view.php?id=<?php echo $trip['id']; ?>" class="view-btn">View Itinerary</a>
                            </div>
                        <?php } ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">No completed trips yet.</div>
                <?php endif; ?>
            </div>

        </div>
    </div>
    
    <script>
        // Simple entrance animation on load
        document.addEventListener("DOMContentLoaded", function() {
            const boxes = document.querySelectorAll('.trip-box');
            boxes.forEach((box, index) => {
                box.style.opacity = '0';
                box.style.transform = 'translateY(15px)';
                setTimeout(() => {
                    box.style.transition = 'all 0.4s ease';
                    box.style.opacity = '1';
                    box.style.transform = 'translateY(0)';
                }, index * 80 + 100);
            });
        });
    </script>
</body>
</html>
