<?php
include 'includes/db_connect.php';

if (isset($_POST['country_id']) || isset($_GET['country_id'])) {
    $country_id = intval($_POST['country_id'] ?? $_GET['country_id']);
    $sql = "SELECT id, name, state FROM cities WHERE country_id=? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $country_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        $label = htmlspecialchars($row['name']);
        if (!empty($row['state'])) {
            $label .= ' (' . htmlspecialchars($row['state']) . ')';
        }
        echo "<option value='".$row['id']."'>".$label."</option>";
    }
}
?>
