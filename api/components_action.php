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
            // Fetch Main Components
            $stmt = $pdo->query("SELECT * FROM project_components ORDER BY name ASC");
            $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch Sub Components for each
            foreach ($components as &$comp) {
                $subStmt = $pdo->prepare("SELECT * FROM project_sub_components WHERE component_id = ? ORDER BY id ASC");
                $subStmt->execute([$comp['id']]);
                $comp['sub_components'] = $subStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode(['status' => 'success', 'data' => $components]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT * FROM project_components WHERE id = ?");
            $stmt->execute([$id]);
            $component = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($component) {
                $subStmt = $pdo->prepare("SELECT * FROM project_sub_components WHERE component_id = ? ORDER BY id ASC");
                $subStmt->execute([$id]);
                $component['sub_components'] = $subStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode(['status' => 'success', 'data' => $component]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
    case 'update':
        try {
            $pdo->beginTransaction();

            $name = sanitize($_POST['component_name']);
            $sub_components = isset($_POST['sub_components']) ? $_POST['sub_components'] : []; // Array of strings

            if (empty($name)) {
                throw new Exception("Main Component Name is required.");
            }

            if ($action === 'create') {
                // Insert Main
                $stmt = $pdo->prepare("INSERT INTO project_components (name) VALUES (?)");
                $stmt->execute([$name]);
                $component_id = $pdo->lastInsertId();
                $message = "Component created successfully";
            } else {
                // Update Main
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE project_components SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                $component_id = $id;

                // Delete existing sub-components to re-insert (Simplest strategy for full sync)
                $delStmt = $pdo->prepare("DELETE FROM project_sub_components WHERE component_id = ?");
                $delStmt->execute([$id]);
                
                $message = "Component updated successfully";
            }

            // Insert Sub Components
            if (!empty($sub_components)) {
                $subStmt = $pdo->prepare("INSERT INTO project_sub_components (component_id, name) VALUES (?, ?)");
                foreach ($sub_components as $sub_name) {
                    if (!empty(trim($sub_name))) {
                        $subStmt->execute([$component_id, sanitize($sub_name)]);
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => $message]);

        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            // FK with ON DELETE CASCADE handles sub-components
            $stmt = $pdo->prepare("DELETE FROM project_components WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Component deleted successfully']);
        } catch (Exception $e) {
             echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
