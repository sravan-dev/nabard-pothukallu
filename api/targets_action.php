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
            $sql = "SELECT t.*, s.name as staff_name FROM targets t JOIN staff s ON t.staff_id = s.id ORDER BY t.assignment_date DESC";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_staff':
        try {
            // If user is Staff role, allow only selecting self (or auto-select in UI)
            // Ideally backend filters this, but for simplicity we return all staff unless specific restriction needed
            // If we have logged in user ID, we could pre-select.
            
            $sql = "SELECT id, name FROM staff WHERE status='active' ORDER BY name";
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
            $stmt = $pdo->prepare("SELECT * FROM targets WHERE id = ?");
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
            $staff_id = $_POST['staff_id'];
            $target_description = sanitize($_POST['target_description']);
            $assignment_date = $_POST['assignment_date'];
            $proposed_completion_date = $_POST['proposed_completion_date'];
            $action_taken = isset($_POST['action_taken']) ? sanitize($_POST['action_taken']) : '';
            $actual_completion_date = !empty($_POST['actual_completion_date']) ? $_POST['actual_completion_date'] : null;
            
            // Determine status
            $status = 'Pending';
            if (!empty($action_taken)) $status = 'In Progress';
            if (!empty($actual_completion_date)) $status = 'Completed';

            if ($action === 'create') {
                $sql = "INSERT INTO targets (staff_id, target_description, assignment_date, proposed_completion_date, action_taken, actual_completion_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$staff_id, $target_description, $assignment_date, $proposed_completion_date, $action_taken, $actual_completion_date, $status]);
                echo json_encode(['status' => 'success', 'message' => 'Target assigned successfully']);
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE targets SET staff_id=?, target_description=?, assignment_date=?, proposed_completion_date=?, action_taken=?, actual_completion_date=?, status=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$staff_id, $target_description, $assignment_date, $proposed_completion_date, $action_taken, $actual_completion_date, $status, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Target updated successfully']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM targets WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Target deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
