<?php
require_once '../includes/config.php';

try {
    $columns = [
        "ADD COLUMN block VARCHAR(100) AFTER households",
        "ADD COLUMN panchayat VARCHAR(100) AFTER block",
        "ADD COLUMN ward VARCHAR(100) AFTER panchayat",
        "ADD COLUMN ward_number VARCHAR(50) AFTER ward",
        "ADD COLUMN total_area VARCHAR(100) AFTER ward_number",
        "ADD COLUMN total_families INT DEFAULT 0 AFTER total_area",
        "ADD COLUMN tribal_category VARCHAR(100) AFTER total_families",
        "ADD COLUMN population_total INT DEFAULT 0 AFTER tribal_category",
        "ADD COLUMN population_male INT DEFAULT 0 AFTER population_total",
        "ADD COLUMN population_female INT DEFAULT 0 AFTER population_male",
        "ADD COLUMN public_facilities TEXT AFTER population_female",
        "ADD COLUMN road_access VARCHAR(255) AFTER public_facilities",
        "ADD COLUMN major_crops TEXT AFTER road_access",
        "ADD COLUMN major_issues TEXT AFTER major_crops",
        "ADD COLUMN nvs_formation_date DATE AFTER major_issues",
        "ADD COLUMN nvs_president VARCHAR(150) AFTER nvs_formation_date",
        "ADD COLUMN nvs_secretary VARCHAR(150) AFTER nvs_president",
        "ADD COLUMN animator_name VARCHAR(150) AFTER nvs_secretary",
        "ADD COLUMN animator_mobile VARCHAR(20) AFTER animator_name",
        "ADD COLUMN map_link TEXT AFTER animator_mobile",
        "ADD COLUMN photo1 VARCHAR(255) AFTER map_link",
        "ADD COLUMN photo2 VARCHAR(255) AFTER photo1",
        "ADD COLUMN photo3 VARCHAR(255) AFTER photo2"
    ];

    foreach ($columns as $col) {
        try {
            $pdo->exec("ALTER TABLE hamlets $col");
            echo "Executed: ALTER TABLE hamlets $col <br>";
        } catch (PDOException $e) {
            // Ignore if column exists (simple way for this context)
            if (strpos($e->getMessage(), "Duplicate column") !== false) {
                 echo "Skipped (Exists): $col <br>";
            } else {
                 echo "Error: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    // Attempt deletion separately
    try {
        $pdo->exec("ALTER TABLE hamlets DROP COLUMN charge_officer_name");
        echo "Dropped column: charge_officer_name <br>";
    } catch (PDOException $e) {
        // Ignore if doesn't exist
    }

    echo "Database migration completed.";

} catch (PDOException $e) {
    echo "Fatal Error: " . $e->getMessage();
}
?>
