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

switch ($action) {
    case 'fetch_all':
        try {
            $stmt = $pdo->query("SELECT * FROM hamlets ORDER BY settlement_name ASC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT * FROM hamlets WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
    case 'update':
        try {
            // Helper to get POST value
            $val = function($key) { return isset($_POST[$key]) ? trim($_POST[$key]) : ''; };

            $data = [
                $val('settlement_name'),
                $val('households'),
                $val('block'),
                $val('panchayat'),
                $val('ward'),
                $val('ward_number'),
                $val('total_area'),
                (int)$val('total_families'),
                $val('tribal_category'),
                (int)$val('population_total'),
                (int)$val('population_male'),
                (int)$val('population_female'),
                $val('public_facilities'),
                $val('road_access'),
                $val('major_crops'),
                $val('major_issues'),
                $val('nvs_formation_date') ?: null,
                $val('nvs_president'),
                $val('nvs_secretary'),
                $val('animator_name'),
                $val('animator_mobile'),
                $val('map_link')
            ];

            // File Uploads
            $uploadFields = ['photo1', 'photo2', 'photo3'];
            $filePaths = [];
            foreach ($uploadFields as $field) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $fileName = 'uploads/hamlets_' . time() . '_' . $field . '_' . basename($_FILES[$field]['name']);
                    if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], '../' . $fileName)) {
                        $filePaths[$field] = $fileName;
                    }
                }
            }

            if (empty($data[0])) throw new Exception("Settlement Name is required.");

            if ($action === 'create') {
                $cols = "settlement_name, households, block, panchayat, ward, ward_number, total_area, total_families, tribal_category, population_total, population_male, population_female, public_facilities, road_access, major_crops, major_issues, nvs_formation_date, nvs_president, nvs_secretary, animator_name, animator_mobile, map_link, photo1, photo2, photo3";
                $vals = str_repeat("?,", 24) . "?"; // 25 placeholders
                
                // Add files to data array
                $data[] = $filePaths['photo1'] ?? null;
                $data[] = $filePaths['photo2'] ?? null;
                $data[] = $filePaths['photo3'] ?? null;

                $stmt = $pdo->prepare("INSERT INTO hamlets ($cols) VALUES ($vals)");
                $stmt->execute($data);
                echo json_encode(['status' => 'success', 'message' => 'Hamlet created successfully']);
            } else {
                $id = $_POST['id'];
                
                // Dynamic Update Query Construction
                $updateFields = [
                    "settlement_name=?", "households=?", "block=?", "panchayat=?", "ward=?", "ward_number=?", 
                    "total_area=?", "total_families=?", "tribal_category=?", "population_total=?", 
                    "population_male=?", "population_female=?", "public_facilities=?", "road_access=?", 
                    "major_crops=?", "major_issues=?", "nvs_formation_date=?", "nvs_president=?", 
                    "nvs_secretary=?", "animator_name=?", "animator_mobile=?", "map_link=?"
                ];

                // Append file updates only if new file uploaded
                if (isset($filePaths['photo1'])) { $updateFields[] = "photo1=?"; $data[] = $filePaths['photo1']; }
                if (isset($filePaths['photo2'])) { $updateFields[] = "photo2=?"; $data[] = $filePaths['photo2']; }
                if (isset($filePaths['photo3'])) { $updateFields[] = "photo3=?"; $data[] = $filePaths['photo3']; }

                $sql = "UPDATE hamlets SET " . implode(", ", $updateFields) . " WHERE id=?";
                $data[] = $id;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($data);
                echo json_encode(['status' => 'success', 'message' => 'Hamlet updated successfully']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM hamlets WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Hamlet deleted successfully']);
        } catch (Exception $e) {
             echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
