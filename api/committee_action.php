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

// Helper to handle uploads
function handleUpload($fileInputName, $existingFile = null) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/committee/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES[$fileInputName]['name']);
        $targetPath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetPath)) {
            if ($existingFile && file_exists("../uploads/committee/" . $existingFile)) {
                @unlink("../uploads/committee/" . $existingFile);
            }
            return $fileName;
        }
    }
    return $existingFile;
}

switch ($action) {
    case 'fetch_all':
        try {
            $sql = "SELECT c.*, d.name as designation_name, h.settlement_name, f.net_plan_number 
                    FROM committee_members c 
                    JOIN designations d ON c.designation_id = d.id 
                    JOIN hamlets h ON c.hamlet_id = h.id 
                    LEFT JOIN families f ON c.family_id = f.id
                    ORDER BY c.created_at DESC";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_designations':
        try {
            $stmt = $pdo->query("SELECT * FROM designations ORDER BY name ASC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'add_designation':
        try {
            $name = sanitize($_POST['name']);
            if (empty($name)) throw new Exception("Name required.");

            $stmt = $pdo->prepare("INSERT INTO designations (name) VALUES (?)");
            $stmt->execute([$name]);
            echo json_encode(['status' => 'success', 'message' => 'Designation added', 'id' => $pdo->lastInsertId()]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_families_by_hamlet':
        try {
            $hamlet_id = $_POST['hamlet_id'];
            $stmt = $pdo->prepare("SELECT id, net_plan_number, beneficiary_name FROM families WHERE hamlet_id = ? ORDER BY net_plan_number ASC");
            $stmt->execute([$hamlet_id]);
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
             echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
        
    case 'fetch_single':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT * FROM committee_members WHERE id = ?");
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
            $designation_id = (int)$_POST['designation_id'];
            $name = sanitize($_POST['name']);
            $hamlet_id = (int)$_POST['hamlet_id'];
            $family_id = (int)($_POST['family_id'] ?? 0);
            $net_plan_number = trim((string)($_POST['net_plan_number'] ?? ''));
            $net_plan_number = preg_replace('/^\s*#\s*/', '', $net_plan_number);
            $date_of_entry = $_POST['date_of_entry'];

            if ($family_id <= 0 && $net_plan_number !== '') {
                $lookup = $pdo->prepare("SELECT id FROM families WHERE hamlet_id = ? AND net_plan_number = ? LIMIT 1");
                $lookup->execute([$hamlet_id, $net_plan_number]);
                $family_id = (int)($lookup->fetchColumn() ?: 0);
            }

            if ($family_id <= 0) {
                throw new Exception("Select a valid Net Plan Number from Family Details.");
            }

            $familyCheck = $pdo->prepare("SELECT COUNT(*) FROM families WHERE id = ? AND hamlet_id = ?");
            $familyCheck->execute([$family_id, $hamlet_id]);
            if ((int)$familyCheck->fetchColumn() === 0) {
                throw new Exception("Selected Net Plan Number does not belong to the selected settlement.");
            }

            $photo = handleUpload('photo', $_POST['old_photo'] ?? null);

            if ($action === 'create') {
                $sql = "INSERT INTO committee_members (designation_id, name, hamlet_id, family_id, date_of_entry, photo) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$designation_id, $name, $hamlet_id, $family_id, $date_of_entry, $photo]);
                echo json_encode(['status' => 'success', 'message' => 'Member added successfully']);
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE committee_members SET designation_id=?, name=?, hamlet_id=?, family_id=?, date_of_entry=?, photo=? 
                        WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$designation_id, $name, $hamlet_id, $family_id, $date_of_entry, $photo, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Member updated successfully']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            // Get photo to unlink
            $stmt = $pdo->prepare("SELECT photo FROM committee_members WHERE id = ?");
            $stmt->execute([$id]);
            $photo = $stmt->fetchColumn();

            if ($photo && file_exists("../uploads/committee/" . $photo)) {
                @unlink("../uploads/committee/" . $photo);
            }

            $stmt = $pdo->prepare("DELETE FROM committee_members WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Member deleted successfully']);
        } catch (Exception $e) {
             echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
