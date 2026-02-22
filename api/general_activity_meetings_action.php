<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}
validate_csrf();

function ensure_general_activity_meetings_table($pdo)
{
    $sql = "CREATE TABLE IF NOT EXISTS general_activity_meetings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        meeting_date DATE NULL,
        activity_name VARCHAR(255) NOT NULL,
        participants INT NULL,
        location TEXT NULL,
        guest VARCHAR(255) NULL,
        brief_description TEXT NULL,
        nabard_grant DECIMAL(12,2) DEFAULT 0,
        other_grant DECIMAL(12,2) DEFAULT 0,
        total_sum DECIMAL(12,2) DEFAULT 0,
        photo1 VARCHAR(255) NULL,
        photo2 VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_meeting_date (meeting_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
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
    return strpos($normalized, 'uploads/general_activity_meetings/') === 0;
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

function handle_photo_upload($fieldName, $existingPath = null)
{
    if (!isset($_FILES[$fieldName])) {
        return $existingPath;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return $existingPath;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload failed for $fieldName: " . upload_error_message($file['error']));
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

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'general_activity_meetings';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        throw new Exception("Unable to create upload directory.");
    }

    $newName = 'meeting_' . time() . '_' . $fieldName . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $absolutePath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        throw new Exception("Failed to save uploaded file for $fieldName.");
    }

    if (!empty($existingPath)) {
        delete_uploaded_file_if_exists($existingPath);
    }

    return 'uploads/general_activity_meetings/' . $newName;
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

function post_number($key)
{
    $value = post_text($key);
    if ($value === '') {
        return 0;
    }
    return (float)$value;
}

try {
    ensure_general_activity_meetings_table($pdo);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'fetch_all':
        try {
            $stmt = $pdo->query("SELECT * FROM general_activity_meetings ORDER BY id DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM general_activity_meetings WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
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

            $existing = null;
            if ($isUpdate) {
                $stmt = $pdo->prepare("SELECT photo1, photo2 FROM general_activity_meetings WHERE id = ?");
                $stmt->execute([$id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$existing) {
                    throw new Exception('Record not found.');
                }
            }

            $photo1 = handle_photo_upload('photo1', $existing['photo1'] ?? null);
            $photo2 = handle_photo_upload('photo2', $existing['photo2'] ?? null);

            $nabardGrant = post_number('nabard_grant');
            $otherGrant = post_number('other_grant');
            $sumInput = post_number('total_sum');
            $calculatedSum = $nabardGrant + $otherGrant;
            $totalSum = $sumInput > 0 ? $sumInput : $calculatedSum;

            $payload = [
                'meeting_date' => post_text('meeting_date') ?: null,
                'activity_name' => post_text('activity_name'),
                'participants' => post_int_or_null('participants'),
                'location' => post_text('location'),
                'guest' => post_text('guest'),
                'brief_description' => post_text('brief_description'),
                'nabard_grant' => $nabardGrant,
                'other_grant' => $otherGrant,
                'total_sum' => $totalSum,
                'photo1' => $photo1,
                'photo2' => $photo2
            ];

            if ($payload['activity_name'] === '') {
                throw new Exception('Name of Activity / Meeting is required.');
            }

            if ($isUpdate) {
                $sql = "UPDATE general_activity_meetings SET
                            meeting_date = :meeting_date,
                            activity_name = :activity_name,
                            participants = :participants,
                            location = :location,
                            guest = :guest,
                            brief_description = :brief_description,
                            nabard_grant = :nabard_grant,
                            other_grant = :other_grant,
                            total_sum = :total_sum,
                            photo1 = :photo1,
                            photo2 = :photo2
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $payload['id'] = $id;
                $stmt->execute($payload);

                echo json_encode(['status' => 'success', 'message' => 'General Activity Meeting updated successfully']);
            } else {
                $sql = "INSERT INTO general_activity_meetings (
                            meeting_date, activity_name, participants, location, guest,
                            brief_description, nabard_grant, other_grant, total_sum, photo1, photo2
                        ) VALUES (
                            :meeting_date, :activity_name, :participants, :location, :guest,
                            :brief_description, :nabard_grant, :other_grant, :total_sum, :photo1, :photo2
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($payload);

                echo json_encode(['status' => 'success', 'message' => 'General Activity Meeting created successfully']);
            }
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

            $stmt = $pdo->prepare("SELECT photo1, photo2 FROM general_activity_meetings WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new Exception('Record not found.');
            }

            $del = $pdo->prepare("DELETE FROM general_activity_meetings WHERE id = ?");
            $del->execute([$id]);

            delete_uploaded_file_if_exists($row['photo1'] ?? '');
            delete_uploaded_file_if_exists($row['photo2'] ?? '');

            echo json_encode(['status' => 'success', 'message' => 'General Activity Meeting deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}

