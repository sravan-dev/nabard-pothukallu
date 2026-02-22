<?php
require_once '../includes/config.php';

$username = 'test_super';
$email = 'test_super@example.com';
$password = 'password123';
$role = 'super_admin';

try {
    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo "User with email $email already exists.\n";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $insert->execute([$username, $email, $hashed_password, $role]);
        echo "Successfully created Super Admin user:\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
