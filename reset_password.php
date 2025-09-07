<?php
include 'config.php';
include 'csrf.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Power Admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Invalid CSRF token'); }
    $user_id = trim($_POST['user_id'] ?? '');
    if ($user_id === '') { header('Location: power_admin_users.php?error=bad_request'); exit; }

    // Generate a temporary password and hash it
    $tempPassword = bin2hex(random_bytes(4)); // 8 hex chars ~ 8 bytes visible
    $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('UPDATE users SET password_hash=? WHERE user_id=?');
    $stmt->bind_param('ss', $hash, $user_id);
    if ($stmt->execute()) {
        // Redirect back and show the temp password via query string (admin-facing only)
        header('Location: power_admin_users.php?success=reset&temp=' . urlencode($tempPassword) . '&user=' . urlencode($user_id));
        exit;
    }
    header('Location: power_admin_users.php?error=reset_failed');
    exit;
}

http_response_code(405);
echo 'Method Not Allowed';
?>

