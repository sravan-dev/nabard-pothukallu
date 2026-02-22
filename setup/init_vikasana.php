<?php
require_once '../includes/config.php';

try {
    // 1. Create vikasana_members table
    // Reuses 'designations', 'hamlets', 'families' tables
    $sql = "CREATE TABLE IF NOT EXISTS vikasana_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        designation_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        age INT,
        hamlet_id INT NOT NULL,
        family_id INT NOT NULL,
        date_of_entry DATE NOT NULL,
        photo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (designation_id) REFERENCES designations(id),
        FOREIGN KEY (hamlet_id) REFERENCES hamlets(id),
        FOREIGN KEY (family_id) REFERENCES families(id)
    )";
    $pdo->exec($sql);
    echo "Table 'vikasana_members' created/verified.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
