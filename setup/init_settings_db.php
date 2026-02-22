<?php
require_once '../includes/config.php';

try {
    // Create site_settings table
    $sql = "CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) UNIQUE NOT NULL,
        setting_value TEXT
    )";
    $pdo->exec($sql);
    echo "Table 'site_settings' created.<br>";

    // Insert default values
    $defaults = [
        'site_title' => 'NABARD',
        'site_sup' => '(Nilambur)',
        'logo_path' => 'logo.png',
        'timezone' => 'Asia/Kolkata'
    ];

    foreach ($defaults as $key => $value) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }
    echo "Default settings inserted.<br>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
