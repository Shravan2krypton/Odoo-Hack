<?php
// includes/navbar.php – shared navigation bar
// Responsive sticky navbar with hamburger menu for mobile
?>
<nav class="navbar" id="mainNavbar">
    <div class="logo">
        <a href="dashboard.php"><strong>GlobeTrotter</strong></a>
    </div>
    <div class="nav-toggle" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <ul class="nav-links" id="navMenu">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="trip_listing.php">My Trips</a></li>
        <li><a href="create_trip.php">Create Trip</a></li>
        <li><a href="activity_search.php">Search Activities</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="admin.php">Admin</a></li>
        <li><a class="logout-btn" href="logout.php">Logout</a></li>
    </ul>
</nav>
