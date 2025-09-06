THIS SHOULD BE A LINTER ERROR<?php
include 'config.php';
include 'csrf.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Power Admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Invalid CSRF token'); }

    $user_id = trim($_POST['user_id'] ?? '');
    $new_status = trim($_POST['new_status'] ?? '');

    if ($user_id === '' || ($new_status !== 'Active' && $new_status !== 'Inactive')) {
        header("Location: power_admin_users.php?error=bad_request");
        exit();
    }

    // Prevent changing Power Admin's own status accidentally
    $self = $_SESSION['user_id'];
    if ($user_id === $self) {
        header("Location: power_admin_users.php?error=cannot_modify_self");
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->bind_param("ss", $new_status, $user_id);
    
    if ($stmt->execute()) {
        header("Location: power_admin_users.php?success=status_updated");
        exit();
    } else {
        header("Location: power_admin_users.php?error=update_failed");
        exit();
    }
}
?>
