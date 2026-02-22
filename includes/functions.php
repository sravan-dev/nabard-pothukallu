<?php

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function check_role($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Redirect to a specific denied page or back to dashboard
        // For simplicity, just existing acts as a "forbidden" check if we wanted
        // But usually we just redirect them to their own dashboard or logout
        redirect('dashboard.php'); 
    }
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

function validate_csrf() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verify_csrf_token($token)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Security validation failed (CSRF).']);
        exit;
    }
}

function get_site_settings($pdo) {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        return [];
    }
}
?>
