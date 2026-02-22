<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}
validate_csrf();

function ensure_jlg_group_table($pdo)
{
    $sql = "CREATE TABLE IF NOT EXISTS jlg_group_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        group_name VARCHAR(255) NOT NULL,
        group_type VARCHAR(50) NULL,
        no_of_members INT NULL,
        net_plan_nos VARCHAR(255) NULL,
        member_names TEXT NULL,
        activity_involved VARCHAR(255) NULL,
        account_name VARCHAR(255) NULL,
        bank_name VARCHAR(255) NULL,
        account_no VARCHAR(100) NULL,
        ifsc VARCHAR(30) NULL,
        group_photo VARCHAR(255) NULL,
        activity_photo VARCHAR(255) NULL,
        total_amount DECIMAL(12,2) DEFAULT 0,
        nabard_share DECIMAL(12,2) DEFAULT 0,
        beneficiary_share DECIMAL(12,2) DEFAULT 0,
        convergence DECIMAL(12,2) DEFAULT 0,
        agency_share DECIMAL(12,2) DEFAULT 0,
        total DECIMAL(12,2) DEFAULT 0,
        loans_availed_from VARCHAR(255) NULL,
        loan_amount DECIMAL(12,2) DEFAULT 0,
        date_of_sanction DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    return strpos($normalized, 'uploads/jlg_group/') === 0;
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

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'jlg_group';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        throw new Exception("Unable to create upload directory.");
    }

    $newName = 'jlg_' . time() . '_' . $fieldName . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $absolutePath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        throw new Exception("Failed to save uploaded file for $fieldName.");
    }

    if (!empty($existingPath)) {
        delete_uploaded_file_if_exists($existingPath);
    }

    return 'uploads/jlg_group/' . $newName;
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
    ensure_jlg_group_table($pdo);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'fetch_all':
        try {
            $stmt = $pdo->query("SELECT * FROM jlg_group_details ORDER BY id DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM jlg_group_details WHERE id = ?");
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
                $stmt = $pdo->prepare("SELECT group_photo, activity_photo FROM jlg_group_details WHERE id = ?");
                $stmt->execute([$id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$existing) {
                    throw new Exception('Record not found.');
                }
            }

            $groupPhoto = handle_photo_upload('group_photo', $existing['group_photo'] ?? null);
            $activityPhoto = handle_photo_upload('activity_photo', $existing['activity_photo'] ?? null);

            $payload = [
                'group_name' => post_text('group_name'),
                'group_type' => post_text('group_type'),
                'no_of_members' => post_int_or_null('no_of_members'),
                'net_plan_nos' => post_text('net_plan_nos'),
                'member_names' => post_text('member_names'),
                'activity_involved' => post_text('activity_involved'),
                'account_name' => post_text('account_name'),
                'bank_name' => post_text('bank_name'),
                'account_no' => post_text('account_no'),
                'ifsc' => post_text('ifsc'),
                'group_photo' => $groupPhoto,
                'activity_photo' => $activityPhoto,
                'total_amount' => post_number('total_amount'),
                'nabard_share' => post_number('nabard_share'),
                'beneficiary_share' => post_number('beneficiary_share'),
                'convergence' => post_number('convergence'),
                'agency_share' => post_number('agency_share'),
                'total' => post_number('total'),
                'loans_availed_from' => post_text('loans_availed_from'),
                'loan_amount' => post_number('loan_amount'),
                'date_of_sanction' => post_text('date_of_sanction') ?: null
            ];

            if ($payload['group_name'] === '') {
                throw new Exception('Name of Group is required.');
            }

            if ($isUpdate) {
                $sql = "UPDATE jlg_group_details SET
                            group_name = :group_name,
                            group_type = :group_type,
                            no_of_members = :no_of_members,
                            net_plan_nos = :net_plan_nos,
                            member_names = :member_names,
                            activity_involved = :activity_involved,
                            account_name = :account_name,
                            bank_name = :bank_name,
                            account_no = :account_no,
                            ifsc = :ifsc,
                            group_photo = :group_photo,
                            activity_photo = :activity_photo,
                            total_amount = :total_amount,
                            nabard_share = :nabard_share,
                            beneficiary_share = :beneficiary_share,
                            convergence = :convergence,
                            agency_share = :agency_share,
                            total = :total,
                            loans_availed_from = :loans_availed_from,
                            loan_amount = :loan_amount,
                            date_of_sanction = :date_of_sanction
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $payload['id'] = $id;
                $stmt->execute($payload);

                echo json_encode(['status' => 'success', 'message' => 'JLG/Group Details updated successfully']);
            } else {
                $sql = "INSERT INTO jlg_group_details (
                            group_name, group_type, no_of_members, net_plan_nos, member_names,
                            activity_involved, account_name, bank_name, account_no, ifsc,
                            group_photo, activity_photo, total_amount, nabard_share,
                            beneficiary_share, convergence, agency_share, total,
                            loans_availed_from, loan_amount, date_of_sanction
                        ) VALUES (
                            :group_name, :group_type, :no_of_members, :net_plan_nos, :member_names,
                            :activity_involved, :account_name, :bank_name, :account_no, :ifsc,
                            :group_photo, :activity_photo, :total_amount, :nabard_share,
                            :beneficiary_share, :convergence, :agency_share, :total,
                            :loans_availed_from, :loan_amount, :date_of_sanction
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($payload);

                echo json_encode(['status' => 'success', 'message' => 'JLG/Group Details created successfully']);
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

            $stmt = $pdo->prepare("SELECT group_photo, activity_photo FROM jlg_group_details WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new Exception('Record not found.');
            }

            $del = $pdo->prepare("DELETE FROM jlg_group_details WHERE id = ?");
            $del->execute([$id]);

            delete_uploaded_file_if_exists($row['group_photo'] ?? '');
            delete_uploaded_file_if_exists($row['activity_photo'] ?? '');

            echo json_encode(['status' => 'success', 'message' => 'JLG/Group Details deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}

