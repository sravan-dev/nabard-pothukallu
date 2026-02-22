<?php
require_once '../includes/config.php';

try {
    // Main Components Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS project_components (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'project_components' created/checked.<br>";

    // Sub Components Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS project_sub_components (
        id INT AUTO_INCREMENT PRIMARY KEY,
        component_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (component_id) REFERENCES project_components(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table 'project_sub_components' created/checked.<br>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
