<?php
require_once '../includes/config.php';

try {
    // Create families table
    $sql = "CREATE TABLE IF NOT EXISTS families (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hamlet_id INT NOT NULL,
        net_plan_number VARCHAR(50) NOT NULL UNIQUE,
        beneficiary_name VARCHAR(255) NOT NULL,
        age INT NOT NULL,
        total_members INT NOT NULL,
        photo_initial VARCHAR(255),
        photo_year1 VARCHAR(255),
        photo_year2 VARCHAR(255),
        photo_year3 VARCHAR(255),
        photo_year4 VARCHAR(255),
        photo_year5 VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hamlet_id) REFERENCES hamlets(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'families' created/verified.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
