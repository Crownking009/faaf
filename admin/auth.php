<?php
require_once __DIR__ . '/../config/db.php';
session_start();

function require_admin_login(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

function admin_login(string $username, string $password): bool {
    $conn = db();
    $stmt = $conn->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $username;
        log_admin_activity('login', 'Logged in');
        return true;
    }
    return false;
}

function admin_change_password(int $adminId, string $currentPassword, string $newPassword): array {
    $conn = db();
    $stmt = $conn->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
        return ['ok' => false, 'error' => 'Current password is incorrect.'];
    }
    if (strlen($newPassword) < 6) {
        return ['ok' => false, 'error' => 'New password must be at least 6 characters.'];
    }
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    $upd = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
    $upd->bind_param('si', $hash, $adminId);
    $upd->execute();
    return ['ok' => true];
}
