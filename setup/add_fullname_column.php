<?php
require_once '../includes/config.php';

try {
    // Add full_name column if it doesn't exist
    // Checking existence usually requires a query info_schema or handling error,
    // but straight ADD COLUMN IF NOT EXISTS is supported in newer MySQL/MariaDB.
    // For XAMPP (MariaDB), let's try standard ALTER.
    
    $sql = "ALTER TABLE users ADD COLUMN full_name VARCHAR(100) AFTER username";
    $pdo->exec($sql);
    echo "Column 'full_name' added successfully.\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column 'full_name' already exists.\n";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
