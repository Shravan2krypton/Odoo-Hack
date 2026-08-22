<?php
// get_cities_html.php – returns <option> HTML for city dropdown (used by create_trip.php via POST)

include __DIR__ . '/../includes/db_connect.php';
header('Content-Type: text/html; charset=UTF-8');

$country_id = isset($_POST['country_id']) ? intval($_POST['country_id']) : 0;
if ($country_id <= 0) {
    echo "<option value=\"\" disabled selected>-- Select City --</option>";
    exit;
}

$sql = "SELECT id, name FROM cities WHERE country_id = ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $country_id);
$stmt->execute();
$result = $stmt->get_result();

$options = "<option value=\"\" disabled selected>-- Select City --</option>";
while ($row = $result->fetch_assoc()) {
    $id = (int)$row['id'];
    $name = htmlspecialchars($row['name']);
    $options .= "<option value=\"{$id}\">{$name}</option>";
}

echo $options;
?>
