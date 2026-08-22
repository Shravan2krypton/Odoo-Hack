<?php
include 'includes/db_connect.php';

if (isset($_POST['country_id'])) {
    $country_id = intval($_POST['country_id']);
    $sql = "SELECT id, name FROM city WHERE country_id=? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $country_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        echo "<option value='".$row['id']."'>".$row['name']."</option>";
    }
}
?>
