<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}
validate_csrf();

$action = isset($_POST['action']) ? $_POST['action'] : '';

// Function to update a setting
function update_setting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
}

switch ($action) {
    case 'fetch_all':
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            echo json_encode(['status' => 'success', 'data' => $settings]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'update_general':
        try {
            update_setting($pdo, 'site_title', $_POST['site_title']);
            update_setting($pdo, 'site_sup', $_POST['site_sup']);
            update_setting($pdo, 'timezone', $_POST['timezone']);
            echo json_encode(['status' => 'success', 'message' => 'General settings updated']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'update_logo':
        try {
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['logo']['tmp_name'];
                $fileName = 'logo_' . time() . '.png'; // Force PNG or keep extension
                $destPath = '../' . $fileName;

                if(move_uploaded_file($fileTmpPath, $destPath)) {
                    // Update DB with just filename
                    update_setting($pdo, 'logo_path', $fileName);
                    echo json_encode(['status' => 'success', 'message' => 'Logo updated', 'path' => $fileName]);
                } else {
                    throw new Exception('Failed to modify file. Check permissions.');
                }
            } else {
                 throw new Exception('No file uploaded or upload error.');
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'check_db_status':
        try {
            $tables = ['families', 'hamlets', 'staff', 'users', 'ptdc_meetings', 'site_settings']; // Key tables
            $stats = [];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                $stats[$table] = $stmt->fetchColumn();
            }
            echo json_encode(['status' => 'success', 'data' => $stats]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'export_db':
        // Minimal Sql Dump Logic using exec with mysqldump if available, or simple PHP fallback
        // Since we can't rely on mysqldump path, we'll do a simple PHP dump for key tables
        try {
            $date = date('Y-m-d_H-i-s');
            $filename = "db_backup_$date.sql";
            $sqlContent = "-- Database Backup\n-- Date: $date\n\n";

            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $sqlContent .= "\n\n-- Table: $table\n";
                $createTable = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);
                $sqlContent .= $createTable['Create Table'] . ";\n";

                $rows = $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $cols = array_keys($row);
                    $vals = array_map(function($val) use ($pdo) {
                        return $val === null ? "NULL" : $pdo->quote($val);
                    }, array_values($row));
                    $sqlContent .= "INSERT INTO $table (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $vals) . ");\n";
                }
            }

            echo json_encode(['status' => 'success', 'download_url' => 'data:application/sql;base64,' . base64_encode($sqlContent), 'filename' => $filename]);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
