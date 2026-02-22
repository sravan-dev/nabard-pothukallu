<?php
require_once '../includes/config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS staff (
        id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        designation VARCHAR(100),
        date_of_appointment DATE,
        date_of_birth DATE,
        address TEXT,
        mobile VARCHAR(20),
        user_id INT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    echo "Table 'staff' created/verified.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
