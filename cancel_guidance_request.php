<?php
include 'config.php';
include 'csrf.php';
session_start();

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: student_status_appointments.php'); exit; }
if (!csrf_validate($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Invalid CSRF token'); }

$student_id = $_SESSION['user_id'];
$request_id = (int)($_POST['request_id'] ?? 0);
if ($request_id <= 0) { header('Location: student_status_appointments.php?error=bad_request'); exit; }

// Allow cancellation only for own appointment and only if pending/approved
$stmt = $conn->prepare("UPDATE appointments SET status='cancelled' WHERE id=? AND student_id=? AND status IN ('pending','approved','Pending','Approved')");
$stmt->bind_param('is', $request_id, $student_id);
$ok = $stmt->execute() && $stmt->affected_rows > 0;

$msg = $ok ? 'Appointment cancelled.' : 'Unable to cancel appointment.';
header('Location: student_status_appointments.php?success=' . urlencode($msg));
exit;
?>

