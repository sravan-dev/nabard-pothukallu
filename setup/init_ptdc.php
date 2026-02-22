<?php
require_once '../includes/config.php';

try {
    // 1. PTDC Meetings Table
    $sql = "CREATE TABLE IF NOT EXISTS ptdc_meetings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venue VARCHAR(255) NOT NULL,
        meeting_date DATE NOT NULL,
        participants_count INT DEFAULT 0,
        guests TEXT,
        major_decisions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'ptdc_meetings' created/verified.\n";

    // 2. PTDC Meeting Files (Photos & Minutes)
    $sql = "CREATE TABLE IF NOT EXISTS ptdc_meeting_files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        meeting_id INT NOT NULL,
        file_type ENUM('photo', 'minutes') NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        FOREIGN KEY (meeting_id) REFERENCES ptdc_meetings(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'ptdc_meeting_files' created/verified.\n";

    // 3. PTDC Participants (Link to Families)
    $sql = "CREATE TABLE IF NOT EXISTS ptdc_participants (
        meeting_id INT NOT NULL,
        family_id INT NOT NULL,
        PRIMARY KEY (meeting_id, family_id),
        FOREIGN KEY (meeting_id) REFERENCES ptdc_meetings(id) ON DELETE CASCADE,
        FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'ptdc_participants' created/verified.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
