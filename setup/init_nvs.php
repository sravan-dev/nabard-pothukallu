<?php
require_once '../includes/config.php';

try {
    // 1. NVS Meetings Table (Linked to Hamlets)
    $sql = "CREATE TABLE IF NOT EXISTS nvs_meetings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hamlet_id INT NOT NULL,
        meeting_date DATE NOT NULL,
        participants_count INT DEFAULT 0,
        major_decisions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (hamlet_id) REFERENCES hamlets(id)
    )";
    $pdo->exec($sql);
    echo "Table 'nvs_meetings' created/verified.\n";

    // 2. NVS Meeting Files
    $sql = "CREATE TABLE IF NOT EXISTS nvs_meeting_files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        meeting_id INT NOT NULL,
        file_type ENUM('photo', 'minutes') NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        FOREIGN KEY (meeting_id) REFERENCES nvs_meetings(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'nvs_meeting_files' created/verified.\n";

    // 3. NVS Participants
    $sql = "CREATE TABLE IF NOT EXISTS nvs_participants (
        meeting_id INT NOT NULL,
        family_id INT NOT NULL,
        PRIMARY KEY (meeting_id, family_id),
        FOREIGN KEY (meeting_id) REFERENCES nvs_meetings(id) ON DELETE CASCADE,
        FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'nvs_participants' created/verified.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
