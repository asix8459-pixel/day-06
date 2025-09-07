<?php
include 'config.php'; 
session_start();

// Require role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) {
    header('Location: login.php');
    exit;
}

// If form is submitted to update status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $request_id = (int)($_POST['request_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $admin_message = trim($_POST['admin_message'] ?? '');

    $allowed = ['pending','approved','completed','rejected'];
    if ($request_id > 0 && in_array(strtolower($status), $allowed, true)) {
        $updateQuery = "UPDATE appointments SET status = ?, admin_message = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssi", $status, $admin_message, $request_id);

        if ($stmt->execute()) {
            header("Location: guidance_list_admin.php?success=" . urlencode('Guidance request updated successfully'));
            exit();
        } else {
            echo 'Update failed. Try again!';
        }
    } else {
        echo 'Invalid data. Please try again.';
    }
}
?>