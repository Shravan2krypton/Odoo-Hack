<?php
// ============================================================
// GlobeTrotter — includes/db_connect.php
// Provides $conn (mysqli) for legacy pages + $pdo via getPDO()
// ============================================================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "globetrotter";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=localhost;dbname=globetrotter;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $pdo = new PDO($dsn, "root", "", $options);
        } catch (PDOException $e) {
            die("PDO connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}
?>