<?php
require_once '../includes/config.php';

try {
    // 1. Success Stories Table
    $sql = "CREATE TABLE IF NOT EXISTS success_stories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        story_date DATE NOT NULL,
        description TEXT,
        photo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'success_stories' created/verified.\n";

    // 2. Success Videos Table
    $sql = "CREATE TABLE IF NOT EXISTS success_videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        story_id INT NOT NULL,
        video_path VARCHAR(255) NOT NULL,
        remarks TEXT,
        FOREIGN KEY (story_id) REFERENCES success_stories(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'success_videos' created/verified.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
