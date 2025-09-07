<?php
include 'config.php'; 
session_start();

// Require role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) {
    header('Location: login.php');
    exit;
}

// If form is submitted to delete a request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_request'])) {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $request_id = (int)($_POST['request_id'] ?? 0);

    if ($request_id > 0) {
        $deleteQuery = "DELETE FROM appointments WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $request_id);

        if ($stmt->execute()) {
            header("Location: guidance_list_admin.php?success=" . urlencode('Guidance request deleted successfully'));
            exit();
        } else {
            echo 'Delete failed. Try again!';
        }
    } else {
        echo 'Invalid data. Please try again.';
    }
}
?>
