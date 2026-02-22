<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}
validate_csrf();

function ensure_individual_distribution_table($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS individual_distribution_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        family_id INT NULL,
        net_plan_number VARCHAR(50) NOT NULL,
        settlement_name VARCHAR(150) NULL,
        family_head VARCHAR(150) NULL,
        beneficiary_name VARCHAR(150) NULL,
        insurance_no VARCHAR(100) NULL,
        insurance_date DATE NULL,
        insurance_amount DECIMAL(12,2) NULL,
        damage_death_date DATE NULL,
        insurance_claimed VARCHAR(100) NULL,
        insurance_claimed_date DATE NULL,
        substitute_purchased_date DATE NULL,
        component_id INT NULL,
        component_name VARCHAR(255) NULL,
        component_type_id INT NULL,
        component_type_name VARCHAR(255) NULL,
        quantity VARCHAR(100) NULL,
        distribution_date DATE NULL,
        bank_name VARCHAR(150) NULL,
        account_no VARCHAR(100) NULL,
        ifsc VARCHAR(30) NULL,
        photo1 VARCHAR(255) NULL,
        photo2 VARCHAR(255) NULL,
        nabard_share DECIMAL(12,2) DEFAULT 0,
        beneficiary_share DECIMAL(12,2) DEFAULT 0,
        convergence_share DECIMAL(12,2) DEFAULT 0,
        agency_share DECIMAL(12,2) DEFAULT 0,
        total_amount DECIMAL(12,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_net_plan_number (net_plan_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
}

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

function is_safe_relative_upload_path($path) {
    $normalized = str_replace('\\', '/', trim((string)$path));
    return strpos($normalized, 'uploads/individual_distribution/') === 0;
}

function delete_uploaded_file_if_exists($path) {
    if (!is_safe_relative_upload_path($path)) {
        return;
    }
    $absolute = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (is_file($absolute)) {
        @unlink($absolute);
    }
}

function handle_photo_upload($fieldName, $existingPath = null) {
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
        if (!function_exists('getimagesize') || @getimagesize($file['tmp_name']) === false) {
            throw new Exception("Invalid MIME type for $fieldName.");
        }
    }

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'individual_distribution';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        throw new Exception("Unable to create upload directory.");
    }

    $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $safeBase = trim((string)$safeBase, '_');
    if ($safeBase === '') {
        $safeBase = 'image';
    }

    $newName = 'dist_' . time() . '_' . $fieldName . '_' . bin2hex(random_bytes(4)) . '_' . $safeBase . '.' . $extension;
    $absolutePath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        throw new Exception("Failed to save uploaded file for $fieldName.");
    }

    if (!empty($existingPath)) {
        delete_uploaded_file_if_exists($existingPath);
    }

    return 'uploads/individual_distribution/' . $newName;
}

function post_text($key) {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

function post_number($key) {
    $value = post_text($key);
    if ($value === '') return 0;
    return (float)$value;
}

function resolve_component_labels($pdo, $componentId, $componentTypeId) {
    $componentName = '';
    $componentTypeName = '';

    if ($componentId > 0) {
        $stmt = $pdo->prepare("SELECT name FROM project_components WHERE id = ?");
        $stmt->execute([$componentId]);
        $componentName = (string)($stmt->fetchColumn() ?: '');
    }

    if ($componentTypeId > 0) {
        if ($componentId > 0) {
            $stmt = $pdo->prepare("SELECT name FROM project_sub_components WHERE id = ? AND component_id = ?");
            $stmt->execute([$componentTypeId, $componentId]);
        } else {
            $stmt = $pdo->prepare("SELECT name FROM project_sub_components WHERE id = ?");
            $stmt->execute([$componentTypeId]);
        }
        $componentTypeName = (string)($stmt->fetchColumn() ?: '');
    }

    return [$componentName, $componentTypeName];
}

try {
    ensure_individual_distribution_table($pdo);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'fetch_all':
        try {
            $stmt = $pdo->query("SELECT * FROM individual_distribution_details ORDER BY id DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM individual_distribution_details WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $row]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_net_plans':
        try {
            $sql = "SELECT f.id AS family_id, f.net_plan_number, f.beneficiary_name, h.settlement_name
                    FROM families f
                    LEFT JOIN hamlets h ON h.id = f.hamlet_id
                    ORDER BY f.net_plan_number ASC";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'search_net_plans':
        try {
            $query = post_text('query');
            if ($query === '') {
                $sql = "SELECT f.id AS family_id, f.net_plan_number, f.beneficiary_name, h.settlement_name
                        FROM families f
                        LEFT JOIN hamlets h ON h.id = f.hamlet_id
                        ORDER BY f.net_plan_number ASC
                        LIMIT 50";
                $stmt = $pdo->query($sql);
            } else {
                $sql = "SELECT f.id AS family_id, f.net_plan_number, f.beneficiary_name, h.settlement_name
                        FROM families f
                        LEFT JOIN hamlets h ON h.id = f.hamlet_id
                        WHERE f.net_plan_number LIKE :term
                           OR f.beneficiary_name LIKE :term
                           OR h.settlement_name LIKE :term
                        ORDER BY f.net_plan_number ASC
                        LIMIT 50";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['term' => '%' . $query . '%']);
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_components':
        try {
            $componentsStmt = $pdo->query("SELECT id, name FROM project_components ORDER BY name ASC");
            $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($components as &$component) {
                $subStmt = $pdo->prepare("SELECT id, name FROM project_sub_components WHERE component_id = ? ORDER BY name ASC");
                $subStmt->execute([$component['id']]);
                $component['sub_components'] = $subStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode(['status' => 'success', 'data' => $components]);
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
                throw new Exception("Invalid record ID.");
            }

            $familyId = (int)($_POST['family_id'] ?? 0);
            $netPlanNumber = post_text('netplannumber');
            $componentId = (int)($_POST['mis_comp_item'] ?? 0);
            $componentTypeId = (int)($_POST['component_type_id'] ?? 0);

            list($componentName, $componentTypeName) = resolve_component_labels($pdo, $componentId, $componentTypeId);

            $existing = null;
            if ($isUpdate) {
                $stmt = $pdo->prepare("SELECT photo1, photo2 FROM individual_distribution_details WHERE id = ?");
                $stmt->execute([$id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$existing) {
                    throw new Exception("Record not found.");
                }
            }

            $photo1 = handle_photo_upload('photo1', $existing['photo1'] ?? null);
            $photo2 = handle_photo_upload('photo2', $existing['photo2'] ?? null);

            if ($netPlanNumber === '') {
                throw new Exception("Net Plan Number is required.");
            }

            $settlementName = post_text('nameofsettlement');
            $familyHead = post_text('family_head');
            $beneficiaryName = post_text('name_of_Beneficiary');

            if ($familyId <= 0) {
                $familyStmt = $pdo->prepare("
                    SELECT f.id, f.beneficiary_name, h.settlement_name
                    FROM families f
                    LEFT JOIN hamlets h ON h.id = f.hamlet_id
                    WHERE f.net_plan_number = ?
                    LIMIT 1
                ");
                $familyStmt->execute([$netPlanNumber]);
                $familyRow = $familyStmt->fetch(PDO::FETCH_ASSOC);

                if ($familyRow) {
                    $familyId = (int)$familyRow['id'];
                    if ($settlementName === '') {
                        $settlementName = (string)($familyRow['settlement_name'] ?? '');
                    }
                    if ($beneficiaryName === '') {
                        $beneficiaryName = (string)($familyRow['beneficiary_name'] ?? '');
                    }
                    if ($familyHead === '') {
                        $familyHead = (string)($familyRow['beneficiary_name'] ?? '');
                    }
                }
            }

            $payload = [
                'family_id' => $familyId > 0 ? $familyId : null,
                'net_plan_number' => $netPlanNumber,
                'settlement_name' => $settlementName,
                'family_head' => $familyHead,
                'beneficiary_name' => $beneficiaryName,
                'insurance_no' => post_text('InsuranceNo'),
                'insurance_date' => post_text('InsuranceDate') ?: null,
                'insurance_amount' => post_number('InsuranceAmount'),
                'damage_death_date' => post_text('DamageDeathDate') ?: null,
                'insurance_claimed' => post_text('InsuranceClamed'),
                'insurance_claimed_date' => post_text('InsuranceClamedDate') ?: null,
                'substitute_purchased_date' => post_text('SubstitutePuchacedDate') ?: null,
                'component_id' => $componentId > 0 ? $componentId : null,
                'component_name' => $componentName,
                'component_type_id' => $componentTypeId > 0 ? $componentTypeId : null,
                'component_type_name' => $componentTypeName,
                'quantity' => post_text('mis_qty'),
                'distribution_date' => post_text('mis_date_of_distribution') ?: null,
                'bank_name' => post_text('name_of_bank'),
                'account_no' => post_text('Account_no'),
                'ifsc' => post_text('ifsc'),
                'photo1' => $photo1,
                'photo2' => $photo2,
                'nabard_share' => post_number('Nabard_Share'),
                'beneficiary_share' => post_number('Beneficiary_Share'),
                'convergence_share' => post_number('Convergence'),
                'agency_share' => post_number('Agency_Share'),
                'total_amount' => post_number('Total_Amount')
            ];

            if ($isUpdate) {
                $sql = "UPDATE individual_distribution_details SET
                            family_id = :family_id,
                            net_plan_number = :net_plan_number,
                            settlement_name = :settlement_name,
                            family_head = :family_head,
                            beneficiary_name = :beneficiary_name,
                            insurance_no = :insurance_no,
                            insurance_date = :insurance_date,
                            insurance_amount = :insurance_amount,
                            damage_death_date = :damage_death_date,
                            insurance_claimed = :insurance_claimed,
                            insurance_claimed_date = :insurance_claimed_date,
                            substitute_purchased_date = :substitute_purchased_date,
                            component_id = :component_id,
                            component_name = :component_name,
                            component_type_id = :component_type_id,
                            component_type_name = :component_type_name,
                            quantity = :quantity,
                            distribution_date = :distribution_date,
                            bank_name = :bank_name,
                            account_no = :account_no,
                            ifsc = :ifsc,
                            photo1 = :photo1,
                            photo2 = :photo2,
                            nabard_share = :nabard_share,
                            beneficiary_share = :beneficiary_share,
                            convergence_share = :convergence_share,
                            agency_share = :agency_share,
                            total_amount = :total_amount
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $payload['id'] = $id;
                $stmt->execute($payload);

                echo json_encode(['status' => 'success', 'message' => 'Distribution record updated successfully']);
            } else {
                $sql = "INSERT INTO individual_distribution_details (
                            family_id, net_plan_number, settlement_name, family_head, beneficiary_name,
                            insurance_no, insurance_date, insurance_amount, damage_death_date, insurance_claimed,
                            insurance_claimed_date, substitute_purchased_date, component_id, component_name,
                            component_type_id, component_type_name, quantity, distribution_date, bank_name,
                            account_no, ifsc, photo1, photo2, nabard_share, beneficiary_share,
                            convergence_share, agency_share, total_amount
                        ) VALUES (
                            :family_id, :net_plan_number, :settlement_name, :family_head, :beneficiary_name,
                            :insurance_no, :insurance_date, :insurance_amount, :damage_death_date, :insurance_claimed,
                            :insurance_claimed_date, :substitute_purchased_date, :component_id, :component_name,
                            :component_type_id, :component_type_name, :quantity, :distribution_date, :bank_name,
                            :account_no, :ifsc, :photo1, :photo2, :nabard_share, :beneficiary_share,
                            :convergence_share, :agency_share, :total_amount
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($payload);

                echo json_encode(['status' => 'success', 'message' => 'Distribution record created successfully']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("Invalid record ID.");
            }

            $stmt = $pdo->prepare("SELECT photo1, photo2 FROM individual_distribution_details WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new Exception("Record not found.");
            }

            $del = $pdo->prepare("DELETE FROM individual_distribution_details WHERE id = ?");
            $del->execute([$id]);

            delete_uploaded_file_if_exists($row['photo1'] ?? '');
            delete_uploaded_file_if_exists($row['photo2'] ?? '');

            echo json_encode(['status' => 'success', 'message' => 'Distribution record deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
