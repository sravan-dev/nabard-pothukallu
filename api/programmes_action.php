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

function handleUpload($fileInputName, $existingFile = null) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/programmes/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES[$fileInputName]['name']);
        $targetPath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetPath)) {
            if ($existingFile && file_exists("../uploads/programmes/" . $existingFile)) {
                @unlink("../uploads/programmes/" . $existingFile);
            }
            return $fileName;
        }
    }
    return $existingFile;
}

switch ($action) {
    case 'fetch_all':
        try {
            $stmt = $pdo->query("SELECT * FROM programmes ORDER BY program_date DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT * FROM programmes WHERE id = ?");
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
            $program_date = $_POST['program_date'];
            $description = sanitize($_POST['description']);
            $photo = handleUpload('photo', $_POST['old_photo'] ?? null);

            if ($action === 'create') {
                $sql = "INSERT INTO programmes (program_date, description, photo) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$program_date, $description, $photo]);
                echo json_encode(['status' => 'success', 'message' => 'Programme added successfully']);
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE programmes SET program_date=?, description=?, photo=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$program_date, $description, $photo, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Programme updated successfully']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT photo FROM programmes WHERE id = ?");
            $stmt->execute([$id]);
            $photo = $stmt->fetchColumn();

            if ($photo && file_exists("../uploads/programmes/" . $photo)) {
                @unlink("../uploads/programmes/" . $photo);
            }

            $stmt = $pdo->prepare("DELETE FROM programmes WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Programme deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
