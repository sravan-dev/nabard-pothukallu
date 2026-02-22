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
            $stmt = $pdo->query("SELECT s.*, u.username as user_account, u.email FROM staff s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.id DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT s.*, u.username as user_account, u.email FROM staff s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        try {
            $pdo->beginTransaction();

            // 1. Generate Staff ID
            // Format: JSS/MLPM/TRIBES/PTKL/001
            $stmt = $pdo->query("SELECT staff_id FROM staff ORDER BY id DESC LIMIT 1");
            $last_entry = $stmt->fetch();
            $next_num = 1;
            if ($last_entry && preg_match('/(\d+)$/', $last_entry['staff_id'], $matches)) {
                $next_num = intval($matches[1]) + 1;
            }
            $staff_id = "JSS/MLPM/TRIBES/PTKL/" . str_pad($next_num, 3, "0", STR_PAD_LEFT);

            // 2. Create User Account
            $username = $_POST['user_name']; // Use distinct field for login username
            $email = sanitize($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $full_name = sanitize($_POST['name']);
            
            // Check if username exists
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $check->execute([$username]);
            if ($check->rowCount() > 0) {
                throw new Exception("Username '$username' already exists.");
            }

            // Check if email exists
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkEmail->execute([$email]);
            if ($checkEmail->rowCount() > 0) {
                throw new Exception("Email '$email' already exists.");
            }

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, full_name, created_at) VALUES (?, ?, ?, 'staff', ?, NOW())");
            $stmt->execute([$username, $email, $password, $full_name]);
            $user_id = $pdo->lastInsertId();

            // 3. Create Staff Entry
            $name = sanitize($_POST['name']);
            $designation = sanitize($_POST['designation']);
            $doa = $_POST['date_of_appointment'];
            $dob = $_POST['date_of_birth'];
            $address = sanitize($_POST['address']);
            $mobile = sanitize($_POST['mobile']);
            $status = $_POST['status'];

            $sql = "INSERT INTO staff (staff_id, name, designation, date_of_appointment, date_of_birth, address, mobile, user_id, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$staff_id, $name, $designation, $doa, $dob, $address, $mobile, $user_id, $status]);

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Staff and User Account created successfully. Staff ID: ' . $staff_id]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        try {
            $pdo->beginTransaction();
            $id = $_POST['id'];
            
            // Update Staff Details
            $name = sanitize($_POST['name']);
            $designation = sanitize($_POST['designation']);
            $doa = $_POST['date_of_appointment'];
            $dob = $_POST['date_of_birth'];
            $address = sanitize($_POST['address']);
            $mobile = sanitize($_POST['mobile']);
            $status = $_POST['status'];

            $sql = "UPDATE staff SET name=?, designation=?, date_of_appointment=?, date_of_birth=?, address=?, mobile=?, status=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $designation, $doa, $dob, $address, $mobile, $status, $id]);
            
            // Update Linked User (Password/Username if needed)
            // Fetch linked user_id
            $stmt = $pdo->prepare("SELECT user_id FROM staff WHERE id = ?");
            $stmt->execute([$id]);
            $user_id = $stmt->fetchColumn();

            if ($user_id) {
                $email = sanitize($_POST['email']);
                // Check duplicate email
                $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $checkEmail->execute([$email, $user_id]);
                if ($checkEmail->rowCount() > 0) {
                    throw new Exception("Email '$email' already exists.");
                }

                // Update User Details
                $sqlUser = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
                $params = [$name, $email, $user_id];
                
                // Update Password if provided
                if (!empty($_POST['password'])) {
                    $sqlUser = "UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?";
                    $params = [$name, $email, password_hash($_POST['password'], PASSWORD_BCRYPT), $user_id];
                }
                $pdo->prepare($sqlUser)->execute($params);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Staff updated successfully']);
        } catch (Exception $e) {
             $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            $pdo->beginTransaction();
            
            // Get user_id before deleting staff
            $stmt = $pdo->prepare("SELECT user_id FROM staff WHERE id = ?");
            $stmt->execute([$id]);
            $user_id = $stmt->fetchColumn();

            // Delete Staff
            $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
            $stmt->execute([$id]);

            // Delete User
            if ($user_id) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Staff and Account deleted successfully']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
