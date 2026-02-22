<?php
require_once '../includes/config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS targets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id INT NOT NULL,
        target_description TEXT,
        assignment_date DATE,
        proposed_completion_date DATE,
        action_taken TEXT,
        actual_completion_date DATE,
        status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'targets' created/verified.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
