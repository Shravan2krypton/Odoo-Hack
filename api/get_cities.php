<?php
include '../includes/db_connect.php';
header('Content-Type: application/json');

$country_id = isset($_GET['country_id']) ? intval($_GET['country_id']) : 0;
if ($country_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid country_id']);
    exit;
}

$sql = "SELECT id, name FROM cities WHERE country_id = ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $country_id);
$stmt->execute();
$result = $stmt->get_result();
$cities = [];
while ($row = $result->fetch_assoc()) {
    $cities[] = $row;
}

echo json_encode(['success' => true, 'cities' => $cities]);
?>
