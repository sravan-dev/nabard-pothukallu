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

function upload_error_message($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'File is too large.';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file uploaded.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary upload directory.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk.';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the upload.';
        default:
            return 'Unknown upload error.';
    }
}

function handle_photo_upload($fieldName) {
    if (!isset($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload failed for $fieldName: " . upload_error_message($file['error']));
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new Exception("Invalid upload source for $fieldName.");
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMimes = [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
        'image/webp',
        'image/gif'
    ];

    $originalName = basename($file['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
        throw new Exception("Invalid file type for $fieldName. Allowed: jpg, jpeg, png, webp, gif.");
    }

    $mime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);
    } elseif (function_exists('mime_content_type')) {
        $mime = (string)mime_content_type($file['tmp_name']);
    }

    if ($mime !== '' && !in_array($mime, $allowedMimes, true)) {
        // Some devices/browsers send unusual MIME values for valid images.
        if (!function_exists('getimagesize') || @getimagesize($file['tmp_name']) === false) {
            throw new Exception("Invalid MIME type for $fieldName.");
        }
    }

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        throw new Exception("Unable to create uploads directory.");
    }

    $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $safeBase = trim($safeBase, '_');
    if ($safeBase === '') {
        $safeBase = 'image';
    }

    $newName = 'hamlets_' . time() . '_' . $fieldName . '_' . bin2hex(random_bytes(4)) . '_' . $safeBase . '.' . $extension;
    $absolutePath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        throw new Exception("Failed to move uploaded file for $fieldName.");
    }

    return 'uploads/' . $newName;
}

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

    case 'fetch_tribal_categories':
        try {
            $defaultCategories = ['Paniya', 'Muthuva', 'Kaatunaika', 'Chola Naika', 'Aranadan', 'Kurumar', 'Alaar'];
            $stmt = $pdo->query("SELECT DISTINCT tribal_category FROM hamlets WHERE tribal_category IS NOT NULL AND TRIM(tribal_category) <> ''");
            $dbCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $allCategories = array_map('trim', array_merge($defaultCategories, $dbCategories));
            $filtered = [];
            foreach ($allCategories as $item) {
                if ($item !== '') {
                    $filtered[] = $item;
                }
            }
            $allCategories = array_values(array_unique($filtered));
            natcasesort($allCategories);

            echo json_encode(['status' => 'success', 'data' => array_values($allCategories)]);
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
                $uploadedPath = handle_photo_upload($field);
                if ($uploadedPath !== null) {
                    $filePaths[$field] = $uploadedPath;
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
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Hamlet created successfully',
                    'uploaded' => array_keys($filePaths)
                ]);
            } else {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new Exception("Invalid record ID.");
                }
                
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
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Hamlet updated successfully',
                    'uploaded' => array_keys($filePaths)
                ]);
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
