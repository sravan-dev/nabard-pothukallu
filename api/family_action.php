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
        $targetDir = "../uploads/families/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES[$fileInputName]['name']);
        $targetPath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetPath)) {
            // Delete old file if updating
            if ($existingFile && file_exists("../uploads/families/" . $existingFile)) {
                @unlink("../uploads/families/" . $existingFile);
            }
            return $fileName;
        }
    }
    return $existingFile;
}

switch ($action) {
    case 'fetch_all':
        try {
            // Join with hamlets to get settlement name
            $sql = "SELECT f.*, h.settlement_name 
                    FROM families f 
                    JOIN hamlets h ON f.hamlet_id = h.id 
                    ORDER BY f.net_plan_number ASC";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT * FROM families WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'search_net_plan':
        try {
            $query = $_POST['query'] ?? '';
            if (strlen($query) < 2) {
                echo json_encode(['status' => 'success', 'data' => []]);
                exit;
            }
            // Search by Net Plan or Name
            $sql = "SELECT id, net_plan_number, beneficiary_name 
                    FROM families 
                    WHERE net_plan_number LIKE ? OR beneficiary_name LIKE ? 
                    LIMIT 10";
            $stmt = $pdo->prepare($sql);
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $results = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $results]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_next_net_plan':
        try {
            $stmt = $pdo->query("SELECT MAX(CAST(net_plan_number AS UNSIGNED)) as max_np FROM families WHERE net_plan_number REGEXP '^[0-9]+$'");
            $row = $stmt->fetch();
            $next_np = isset($row['max_np']) && $row['max_np'] !== null ? ((int)$row['max_np'] + 1) : 1000;
            echo json_encode(['status' => 'success', 'next_net_plan' => $next_np]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
    case 'update':
        try {
            $hamlet_id = (int)$_POST['hamlet_id'];
            $net_plan_number = trim((string)($_POST['net_plan_number'] ?? ''));
            $net_plan_number = preg_replace('/^\s*#\s*/', '', $net_plan_number);
            if ($net_plan_number === '' || !preg_match('/^[A-Za-z0-9]+$/', $net_plan_number)) {
                throw new Exception("Net Plan Number must contain only letters and numbers.");
            }
            if (strlen($net_plan_number) > 50) {
                throw new Exception("Net Plan Number is too long.");
            }
            $beneficiary_name = sanitize($_POST['beneficiary_name']);
            $age = (int)$_POST['age'];
            $total_members = (int)$_POST['total_members'];

            // Handle Photos
            $photo_initial = handleUpload('photo_initial', $_POST['old_photo_initial'] ?? null);
            $photo_year1 = handleUpload('photo_year1', $_POST['old_photo_year1'] ?? null);
            $photo_year2 = handleUpload('photo_year2', $_POST['old_photo_year2'] ?? null);
            $photo_year3 = handleUpload('photo_year3', $_POST['old_photo_year3'] ?? null);
            $photo_year4 = handleUpload('photo_year4', $_POST['old_photo_year4'] ?? null);
            $photo_year5 = handleUpload('photo_year5', $_POST['old_photo_year5'] ?? null);

            if ($action === 'create') {
                // Check uniqueness
                $check = $pdo->prepare("SELECT id FROM families WHERE net_plan_number = ?");
                $check->execute([$net_plan_number]);
                if ($check->rowCount() > 0) {
                    throw new Exception("Net Plan Number already exists.");
                }

                $sql = "INSERT INTO families (hamlet_id, net_plan_number, beneficiary_name, age, total_members, 
                        photo_initial, photo_year1, photo_year2, photo_year3, photo_year4, photo_year5) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$hamlet_id, $net_plan_number, $beneficiary_name, $age, $total_members, 
                                $photo_initial, $photo_year1, $photo_year2, $photo_year3, $photo_year4, $photo_year5]);
                echo json_encode(['status' => 'success', 'message' => 'Family Details added successfully']);
            } else {
                $id = $_POST['id'];
                // Check uniqueness excluding current
                $check = $pdo->prepare("SELECT id FROM families WHERE net_plan_number = ? AND id != ?");
                $check->execute([$net_plan_number, $id]);
                if ($check->rowCount() > 0) {
                    throw new Exception("Net Plan Number already exists.");
                }

                $sql = "UPDATE families SET hamlet_id=?, net_plan_number=?, beneficiary_name=?, age=?, total_members=?, 
                        photo_initial=?, photo_year1=?, photo_year2=?, photo_year3=?, photo_year4=?, photo_year5=? 
                        WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$hamlet_id, $net_plan_number, $beneficiary_name, $age, $total_members, 
                                $photo_initial, $photo_year1, $photo_year2, $photo_year3, $photo_year4, $photo_year5, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Family Details updated successfully']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_photo':
        try {
            $id = $_POST['id'];
            $type = $_POST['type']; // e.g., 'photo_initial', 'photo_year1'
            
            // Validate type to prevent SQL injection or unauthorized deletion
            $allowed_types = ['photo_initial', 'photo_year1', 'photo_year2', 'photo_year3', 'photo_year4', 'photo_year5'];
            if (!in_array($type, $allowed_types)) {
                throw new Exception("Invalid photo type.");
            }

            // Get current file
            $stmt = $pdo->prepare("SELECT $type FROM families WHERE id = ?");
            $stmt->execute([$id]);
            $currentFile = $stmt->fetchColumn();

            if ($currentFile && file_exists("../uploads/families/" . $currentFile)) {
                @unlink("../uploads/families/" . $currentFile);
            }

            // Update DB
            $stmt = $pdo->prepare("UPDATE families SET $type = NULL WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['status' => 'success', 'message' => 'Photo deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'upload_photo':
        try {
            $id = $_POST['id'];
            $type = $_POST['type'];
             $allowed_types = ['photo_initial', 'photo_year1', 'photo_year2', 'photo_year3', 'photo_year4', 'photo_year5'];
            if (!in_array($type, $allowed_types)) {
                throw new Exception("Invalid photo type.");
            }

            // Handle upload
            // We expect the file input to be named 'photo'
            $key = 'photo'; 
            if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                // Get old file to delete
                $stmt = $pdo->prepare("SELECT $type FROM families WHERE id = ?");
                $stmt->execute([$id]);
                $oldFile = $stmt->fetchColumn();

                $newFileName = handleUpload($key, $oldFile); // Reuses existing helper, deletes oldFile if passed

                // Update DB
                $stmt = $pdo->prepare("UPDATE families SET $type = ? WHERE id = ?");
                $stmt->execute([$newFileName, $id]);
                
                echo json_encode(['status' => 'success', 'message' => 'Photo updated successfully', 'file' => $newFileName]);
            } else {
                throw new Exception("No file uploaded or upload error.");
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            // Fetch photos to delete
            $stmt = $pdo->prepare("SELECT photo_initial, photo_year1, photo_year2, photo_year3, photo_year4, photo_year5 FROM families WHERE id = ?");
            $stmt->execute([$id]);
            $photos = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($photos) {
                foreach ($photos as $photo) {
                    if ($photo && file_exists("../uploads/families/" . $photo)) {
                        @unlink("../uploads/families/" . $photo);
                    }
                }
            }

            $stmt = $pdo->prepare("DELETE FROM families WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Family Details deleted successfully']);
        } catch (Exception $e) {
             echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
