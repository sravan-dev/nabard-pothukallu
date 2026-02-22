<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}
validate_csrf();

function ensure_social_auditing_tables($pdo)
{
    $sqlMain = "CREATE TABLE IF NOT EXISTS social_auditing (
        id INT AUTO_INCREMENT PRIMARY KEY,
        audit_date DATE NULL,
        settlement_name VARCHAR(255) NOT NULL,
        participants TEXT NULL,
        households_covered INT NULL,
        major_findings TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $sqlPhotos = "CREATE TABLE IF NOT EXISTS social_auditing_photos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        audit_id INT NOT NULL,
        photo_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_audit_id (audit_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sqlMain);
    $pdo->exec($sqlPhotos);
}

function upload_error_message($code)
{
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

function is_safe_relative_upload_path($path)
{
    $normalized = str_replace('\\', '/', trim((string)$path));
    return strpos($normalized, 'uploads/social_auditing/') === 0;
}

function delete_uploaded_file_if_exists($path)
{
    if (!is_safe_relative_upload_path($path)) {
        return;
    }
    $absolute = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (is_file($absolute)) {
        @unlink($absolute);
    }
}

function post_text($key)
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

function post_int_or_null($key)
{
    $value = post_text($key);
    if ($value === '') {
        return null;
    }
    return (int)$value;
}

function get_uploaded_files($fieldName)
{
    if (!isset($_FILES[$fieldName])) {
        return [];
    }

    $files = $_FILES[$fieldName];
    if (!is_array($files['name'])) {
        return [$files];
    }

    $normalized = [];
    $total = count($files['name']);
    for ($i = 0; $i < $total; $i++) {
        $normalized[] = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
    }

    return $normalized;
}

function save_uploaded_photo($file, $fieldName = 'photos')
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new Exception("Upload failed for $fieldName: " . upload_error_message((int)$file['error']));
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new Exception("Invalid upload source for $fieldName.");
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMimes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/webp', 'image/gif'];
    $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));

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
        if (!function_exists('getimagesize') || @getimagesize($file['tmp_name']) === false) {
            throw new Exception("Invalid MIME type for $fieldName.");
        }
    }

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'social_auditing';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        throw new Exception("Unable to create upload directory.");
    }

    $newName = 'social_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $absolutePath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        throw new Exception("Failed to save uploaded photo.");
    }

    return 'uploads/social_auditing/' . $newName;
}

try {
    ensure_social_auditing_tables($pdo);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'fetch_all':
        try {
            $sql = "SELECT sa.*,
                           (SELECT COUNT(*) FROM social_auditing_photos sap WHERE sap.audit_id = sa.id) AS photo_count
                    FROM social_auditing sa
                    ORDER BY sa.id DESC";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid record ID.');
            }

            $stmt = $pdo->prepare("SELECT * FROM social_auditing WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new Exception('Record not found.');
            }

            $photosStmt = $pdo->prepare("SELECT id, photo_path FROM social_auditing_photos WHERE audit_id = ? ORDER BY id ASC");
            $photosStmt->execute([$id]);
            $row['photos'] = $photosStmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $row]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
    case 'update':
        try {
            $isUpdate = $action === 'update';
            $id = (int)($_POST['id'] ?? 0);
            if ($isUpdate && $id <= 0) {
                throw new Exception('Invalid record ID.');
            }

            if ($isUpdate) {
                $checkStmt = $pdo->prepare("SELECT id FROM social_auditing WHERE id = ?");
                $checkStmt->execute([$id]);
                if (!$checkStmt->fetch()) {
                    throw new Exception('Record not found.');
                }
            }

            $payload = [
                'audit_date' => post_text('audit_date') ?: null,
                'settlement_name' => post_text('settlement_name'),
                'participants' => post_text('participants'),
                'households_covered' => post_int_or_null('households_covered'),
                'major_findings' => post_text('major_findings')
            ];

            if ($payload['settlement_name'] === '') {
                throw new Exception('Name of Settlement is required.');
            }

            if ($isUpdate) {
                $sql = "UPDATE social_auditing SET
                            audit_date = :audit_date,
                            settlement_name = :settlement_name,
                            participants = :participants,
                            households_covered = :households_covered,
                            major_findings = :major_findings
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $payload['id'] = $id;
                $stmt->execute($payload);
                $auditId = $id;
            } else {
                $sql = "INSERT INTO social_auditing (
                            audit_date, settlement_name, participants, households_covered, major_findings
                        ) VALUES (
                            :audit_date, :settlement_name, :participants, :households_covered, :major_findings
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($payload);
                $auditId = (int)$pdo->lastInsertId();
            }

            if ($isUpdate && isset($_POST['remove_photo_ids']) && is_array($_POST['remove_photo_ids'])) {
                $removeIds = array_map('intval', $_POST['remove_photo_ids']);
                $removeIds = array_filter($removeIds, function ($val) {
                    return $val > 0;
                });

                if (!empty($removeIds)) {
                    $placeholders = implode(',', array_fill(0, count($removeIds), '?'));
                    $params = array_merge([$auditId], $removeIds);
                    $fetchSql = "SELECT id, photo_path FROM social_auditing_photos WHERE audit_id = ? AND id IN ($placeholders)";
                    $fetchStmt = $pdo->prepare($fetchSql);
                    $fetchStmt->execute($params);
                    $toDelete = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($toDelete)) {
                        foreach ($toDelete as $photoRow) {
                            delete_uploaded_file_if_exists($photoRow['photo_path'] ?? '');
                        }
                        $deleteSql = "DELETE FROM social_auditing_photos WHERE audit_id = ? AND id IN ($placeholders)";
                        $deleteStmt = $pdo->prepare($deleteSql);
                        $deleteStmt->execute($params);
                    }
                }
            }

            $uploadedFiles = get_uploaded_files('photos');
            if (!empty($uploadedFiles)) {
                $insertPhotoStmt = $pdo->prepare("INSERT INTO social_auditing_photos (audit_id, photo_path) VALUES (?, ?)");
                foreach ($uploadedFiles as $file) {
                    $photoPath = save_uploaded_photo($file, 'photos');
                    if ($photoPath !== '') {
                        $insertPhotoStmt->execute([$auditId, $photoPath]);
                    }
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => $isUpdate ? 'Social Auditing updated successfully' : 'Social Auditing created successfully'
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid record ID.');
            }

            $checkStmt = $pdo->prepare("SELECT id FROM social_auditing WHERE id = ?");
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                throw new Exception('Record not found.');
            }

            $photosStmt = $pdo->prepare("SELECT photo_path FROM social_auditing_photos WHERE audit_id = ?");
            $photosStmt->execute([$id]);
            $photos = $photosStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($photos as $photoRow) {
                delete_uploaded_file_if_exists($photoRow['photo_path'] ?? '');
            }

            $delPhotos = $pdo->prepare("DELETE FROM social_auditing_photos WHERE audit_id = ?");
            $delPhotos->execute([$id]);

            $delMain = $pdo->prepare("DELETE FROM social_auditing WHERE id = ?");
            $delMain->execute([$id]);

            echo json_encode(['status' => 'success', 'message' => 'Social Auditing deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}

