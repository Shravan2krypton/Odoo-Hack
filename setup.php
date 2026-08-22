<?php
/**
 * setup.php – Initialise MySQL database for GlobeTrotter.
 *
 * Run from command line (Windows) after installing PHP and MySQL:
 *   php setup.php
 *
 * It will:
 *   1. Connect to MySQL using the same credentials as includes/db_connect.php.
 *   2. Execute schema.sql to create tables (dropping existing ones).
 *   3. Execute seed.sql to populate reference data.
 *   4. Output success/failure messages.
 */

require_once __DIR__ . '/includes/db_connect.php'; // provides $conn (mysqli) and getPDO()

function runSqlFile(PDO $pdo, $filePath) {
    $sql = file_get_contents($filePath);
    if ($sql === false) {
        throw new Exception("Unable to read $filePath");
    }
    // Split on semicolon followed by optional whitespace and newline
    $statements = array_filter(array_map('trim', explode(";\n", $sql)));
    foreach ($statements as $stmt) {
        if ($stmt) {
            $pdo->exec($stmt);
        }
    }
}

try {
    $pdo = getPDO();
    echo "Connected to database successfully.\n";
    $schemaFile = __DIR__ . '/schema.sql';
    $seedFile   = __DIR__ . '/seed.sql';
    echo "Running schema.sql...\n";
    runSqlFile($pdo, $schemaFile);
    echo "Schema applied.\n";
    echo "Running seed.sql...\n";
    runSqlFile($pdo, $seedFile);
    echo "Seed data inserted.\n";
    echo "Database setup complete.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
