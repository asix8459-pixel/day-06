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
    $acting_user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'] ?? '';

    $allowed = ['pending','approved','completed','rejected'];
    $status_lower = strtolower($status);
    if ($request_id > 0 && in_array($status_lower, $allowed, true)) {
        // Ownership/permission check
        $own = $conn->prepare("SELECT user_id FROM appointments WHERE id = ?");
        $own->bind_param('i', $request_id);
        $own->execute();
        $ownerRow = $own->get_result()->fetch_assoc();
        if (!$ownerRow) { echo 'Invalid data. Please try again.'; exit; }
        if ($role !== 'Guidance Admin' && $ownerRow['user_id'] !== $acting_user_id) {
            http_response_code(403);
            exit('Forbidden');
        }

        $updateQuery = "UPDATE appointments SET status = ?, admin_message = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssi", $status_lower, $admin_message, $request_id);

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