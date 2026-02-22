<?php
require_once '../includes/config.php';

try {
    // 1. Create roles table
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(50) NOT NULL UNIQUE
    )";
    $pdo->exec($sql);
    echo "Table 'roles' created.\n";

    // 2. Insert default roles
    $roles = ['super_admin', 'admin', 'nabard'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO roles (role_name) VALUES (?)");
    foreach ($roles as $role) {
        $stmt->execute([$role]);
    }
    echo "Default roles inserted.\n";

    // 3. Alter users table to change ENUM to VARCHAR
    // This allows any string from the roles table (and keeps existing data)
    $sql = "ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL";
    $pdo->exec($sql);
    echo "Table 'users' altered (role column changed to VARCHAR).\n";

    echo "Migration completed successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
