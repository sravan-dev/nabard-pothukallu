<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}
validate_csrf();

function ensure_group_jlg_distribution_table($pdo)
{
    $sql = "CREATE TABLE IF NOT EXISTS group_jlg_distribution_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        jlg_group_id INT NULL,
        jlg_group_name VARCHAR(255) NOT NULL,
        beneficiary_name VARCHAR(255) NULL,
        bank_name VARCHAR(255) NULL,
        account_no VARCHAR(100) NULL,
        ifsc VARCHAR(30) NULL,
        component_id INT NULL,
        component_name VARCHAR(255) NULL,
        quantity VARCHAR(100) NULL,
        distribution_date DATE NULL,
        photo1 VARCHAR(255) NULL,
        photo2 VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_jlg_group_id (jlg_group_id),
        INDEX idx_component_id (component_id)
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
    return strpos($normalized, 'uploads/group_jlg_distribution/') === 0;
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

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'group_jlg_distribution';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        throw new Exception("Unable to create upload directory.");
    }

    $newName = 'group_dist_' . time() . '_' . $fieldName . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $absolutePath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        throw new Exception("Failed to save uploaded file for $fieldName.");
    }

    if (!empty($existingPath)) {
        delete_uploaded_file_if_exists($existingPath);
    }

    return 'uploads/group_jlg_distribution/' . $newName;
}

function post_text($key)
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

function resolve_jlg_group_name($pdo, $groupId, $fallbackName = '')
{
    if ($groupId > 0) {
        try {
            $stmt = $pdo->prepare("SELECT group_name FROM jlg_group_details WHERE id = ?");
            $stmt->execute([$groupId]);
            $name = (string)($stmt->fetchColumn() ?: '');
            if ($name !== '') {
                return $name;
            }
        } catch (Exception $e) {
            // ignore and fallback
        }
    }
    return trim((string)$fallbackName);
}

function resolve_component_name($pdo, $componentId, $fallbackName = '')
{
    if ($componentId > 0) {
        try {
            $stmt = $pdo->prepare("SELECT name FROM project_components WHERE id = ?");
            $stmt->execute([$componentId]);
            $name = (string)($stmt->fetchColumn() ?: '');
            if ($name !== '') {
                return $name;
            }
        } catch (Exception $e) {
            // ignore and fallback
        }
    }
    return trim((string)$fallbackName);
}

try {
    ensure_group_jlg_distribution_table($pdo);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'fetch_all':
        try {
            $stmt = $pdo->query("SELECT * FROM group_jlg_distribution_details ORDER BY id DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM group_jlg_distribution_details WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $row]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_jlg_groups':
        try {
            $stmt = $pdo->query("SELECT id, group_name, account_name, bank_name, account_no, ifsc FROM jlg_group_details ORDER BY group_name ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'success', 'data' => []]);
        }
        break;

    case 'fetch_components':
        try {
            $stmt = $pdo->query("SELECT id, name FROM project_components ORDER BY name ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
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
                $stmt = $pdo->prepare("SELECT photo1, photo2 FROM group_jlg_distribution_details WHERE id = ?");
                $stmt->execute([$id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$existing) {
                    throw new Exception('Record not found.');
                }
            }

            $jlgGroupId = (int)($_POST['jlg_group_id'] ?? 0);
            $componentId = (int)($_POST['component_id'] ?? 0);
            $jlgGroupName = resolve_jlg_group_name($pdo, $jlgGroupId, post_text('jlg_group_name'));
            $componentName = resolve_component_name($pdo, $componentId, post_text('component_name'));

            if ($jlgGroupName === '') {
                throw new Exception('Name of JLG/Group is required.');
            }

            if ($componentName === '') {
                throw new Exception('Component/Item is required.');
            }

            $photo1 = handle_photo_upload('photo1', $existing['photo1'] ?? null);
            $photo2 = handle_photo_upload('photo2', $existing['photo2'] ?? null);

            $payload = [
                'jlg_group_id' => $jlgGroupId > 0 ? $jlgGroupId : null,
                'jlg_group_name' => $jlgGroupName,
                'beneficiary_name' => post_text('beneficiary_name'),
                'bank_name' => post_text('bank_name'),
                'account_no' => post_text('account_no'),
                'ifsc' => post_text('ifsc'),
                'component_id' => $componentId > 0 ? $componentId : null,
                'component_name' => $componentName,
                'quantity' => post_text('quantity'),
                'distribution_date' => post_text('distribution_date') ?: null,
                'photo1' => $photo1,
                'photo2' => $photo2
            ];

            if ($isUpdate) {
                $sql = "UPDATE group_jlg_distribution_details SET
                            jlg_group_id = :jlg_group_id,
                            jlg_group_name = :jlg_group_name,
                            beneficiary_name = :beneficiary_name,
                            bank_name = :bank_name,
                            account_no = :account_no,
                            ifsc = :ifsc,
                            component_id = :component_id,
                            component_name = :component_name,
                            quantity = :quantity,
                            distribution_date = :distribution_date,
                            photo1 = :photo1,
                            photo2 = :photo2
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $payload['id'] = $id;
                $stmt->execute($payload);

                echo json_encode(['status' => 'success', 'message' => 'Group/JLG Distribution Details updated successfully']);
            } else {
                $sql = "INSERT INTO group_jlg_distribution_details (
                            jlg_group_id, jlg_group_name, beneficiary_name, bank_name, account_no, ifsc,
                            component_id, component_name, quantity, distribution_date, photo1, photo2
                        ) VALUES (
                            :jlg_group_id, :jlg_group_name, :beneficiary_name, :bank_name, :account_no, :ifsc,
                            :component_id, :component_name, :quantity, :distribution_date, :photo1, :photo2
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($payload);

                echo json_encode(['status' => 'success', 'message' => 'Group/JLG Distribution Details created successfully']);
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

            $stmt = $pdo->prepare("SELECT photo1, photo2 FROM group_jlg_distribution_details WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new Exception('Record not found.');
            }

            $del = $pdo->prepare("DELETE FROM group_jlg_distribution_details WHERE id = ?");
            $del->execute([$id]);

            delete_uploaded_file_if_exists($row['photo1'] ?? '');
            delete_uploaded_file_if_exists($row['photo2'] ?? '');

            echo json_encode(['status' => 'success', 'message' => 'Group/JLG Distribution Details deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}

