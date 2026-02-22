<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS nabard_db");
    $pdo->exec("USE nabard_db");

    // Create Table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('super_admin', 'admin', 'nabard') NOT NULL DEFAULT 'nabard',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Default Users
    $users = [
        [
            'username' => 'superadmin',
            'email' => 'super@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'super_admin'
        ],
        [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ],
        [
            'username' => 'nabard',
            'email' => 'nabard@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'nabard'
        ]
    ];

    foreach ($users as $user) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$user['email']]);
        if (!$stmt->fetch()) {
            $insert = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $insert->execute([$user['username'], $user['email'], $user['password'], $user['role']]);
            echo "Created user: {$user['username']} ({$user['role']})\n";
        } else {
            echo "User already exists: {$user['username']}\n";
        }
    }

    echo "Database setup completed successfully.";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
