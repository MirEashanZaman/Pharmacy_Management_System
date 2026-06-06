<?php
session_start();
require_once 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function requireLogin($redirect = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireRole($role, $redirect = 'index.php') {
    requireLogin();
    $user = getUser();
    if ($user['role'] !== $role) {
        header("Location: $redirect");
        exit;
    }
}

function getCartCount() {
    global $conn;
    if (!isLoggedIn()) return 0;
    $id = $_SESSION['user_id'];
    $res = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id=$id");
    $row = $res->fetch_assoc();
    return $row['total'] ?? 0;
}
function getCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
        return $data;
    }
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}
?>
