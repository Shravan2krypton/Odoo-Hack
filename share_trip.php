<?php
include 'includes/db_connect.php';

$slug = trim($_GET['slug'] ?? '');
if (!empty($slug)) {
    $stmt = $conn->prepare("SELECT id FROM trips WHERE share_slug = ? AND is_public = 1");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        header("Location: itinerary_view.php?trip_id=" . $row['id']);
        exit();
    }
}

header("Location: index.php");
exit();
?>
