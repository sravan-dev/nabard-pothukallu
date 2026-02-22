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
            $stmt = $pdo->query("SELECT id, username, full_name, email, role, created_at FROM users ORDER BY id DESC");
            $users = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $users]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT id, username, full_name, email, role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            echo json_encode(['status' => 'success', 'data' => $user]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
    case 'update':
        try {
            $username = sanitize($_POST['username']);
            $full_name = sanitize($_POST['full_name']);
            $email = sanitize($_POST['email']);
            $role = sanitize($_POST['role']);
            $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
            
            if ($action === 'create') {
                if (empty($password)) {
                     throw new Exception("Password is required for new users.");
                }
                // Check duplicate email
                $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $check->execute([$email]);
                if ($check->rowCount() > 0) {
                    throw new Exception("Email already exists.");
                }

                $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $full_name, $email, $password, $role]);
                echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
            } else {
                $id = $_POST['id'];
                // Check duplicate email excluding current user
                $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $check->execute([$email, $id]);
                 if ($check->rowCount() > 0) {
                    throw new Exception("Email already exists.");
                }

                if ($password) {
                    $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, email=?, password=?, role=? WHERE id=?");
                    $stmt->execute([$username, $full_name, $email, $password, $role, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, email=?, role=? WHERE id=?");
                    $stmt->execute([$username, $full_name, $email, $role, $id]);
                }
                echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            // Prevent deleting self
            if ($id == $_SESSION['user_id']) {
                 throw new Exception("You cannot delete your own account.");
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
        } catch (Exception $e) {
             echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_roles':
        try {
            $stmt = $pdo->query("SELECT role_name FROM roles ORDER BY role_name ASC");
            $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode(['status' => 'success', 'data' => $roles]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'add_role':
        try {
            $role_name = sanitize($_POST['role_name']);
            if (empty($role_name)) {
                throw new Exception("Role name is required.");
            }
            // Check duplicate
            $check = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
            $check->execute([$role_name]);
            if ($check->rowCount() > 0) {
                 throw new Exception("Role already exists.");
            }
            
            $stmt = $pdo->prepare("INSERT INTO roles (role_name) VALUES (?)");
            $stmt->execute([$role_name]);
            echo json_encode(['status' => 'success', 'message' => 'Role added successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
