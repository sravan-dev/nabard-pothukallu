<?php
require_once '../includes/config.php';

try {
    // 1. Create hamlets table
    $sql = "CREATE TABLE IF NOT EXISTS hamlets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        settlement_name VARCHAR(100) NOT NULL,
        households INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'hamlets' created/verified.\n";

    // 2. Seed data
    $data = [
        ['Anakallu', 8],
        ['Chembra', 45],
        ['Gramam', 28],
        ['Iruttukuthy', 46],
        ['Narangapoyil', 39],
        ['Thudimutty', 40],
        ['Chennanpotty', 22],
        ['Kavalappara', 27],
        ['Ettapara', 12],
        ['Ambalamkunnu', 5],
        ['Vellimuttam', 10],
        ['Kunippala', 8],
        ['Mathipotty', 5],
        ['Kodalippoyil', 4],
        ['Vaniyampuzha', 44],
        ['Tharippapotty', 19],
        ['Appankappu', 95],
        ['Ambittampotty', 13],
        ['Kumbalappara', 15]
    ];

    $stmt = $pdo->prepare("INSERT INTO hamlets (settlement_name, households) VALUES (?, ?)");
    
    // Clear table to avoid duplicates on re-run (optional, but good for dev)
    // $pdo->exec("TRUNCATE TABLE hamlets"); 

    foreach ($data as $row) {
        // Check if exists
        $check = $pdo->prepare("SELECT id FROM hamlets WHERE settlement_name = ?");
        $check->execute([$row[0]]);
        if ($check->rowCount() == 0) {
            $stmt->execute($row);
            echo "Inserted: {$row[0]}\n";
        }
    }
    echo "Data seeding completed.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
