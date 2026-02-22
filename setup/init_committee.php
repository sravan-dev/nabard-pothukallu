<?php
require_once '../includes/config.php';

try {
    // 1. Create designations table
    $sql = "CREATE TABLE IF NOT EXISTS designations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE
    )";
    $pdo->exec($sql);
    echo "Table 'designations' created/verified.\n";

    // Seed Designations
    $roles = ['President', 'Secretary', 'Joint Secretary', 'Treasurer', 'Member'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO designations (name) VALUES (?)");
    foreach ($roles as $role) {
        $stmt->execute([$role]);
    }
    echo "Designations seeded.\n";

    // 2. Create committee_members table
    $sql = "CREATE TABLE IF NOT EXISTS committee_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        designation_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
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
    echo "Table 'committee_members' created/verified.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
