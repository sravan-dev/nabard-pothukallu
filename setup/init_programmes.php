<?php
require_once '../includes/config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS programmes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        program_date DATE NOT NULL,
        description TEXT,
        photo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'programmes' created/verified.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
