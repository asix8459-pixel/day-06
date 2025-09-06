
<?php
require 'config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']); exit; }
require_once 'mailer.php';

$id = (int)($_POST['id'] ?? 0);
$datetime = trim($_POST['datetime'] ?? '');
$msg = trim($_POST['admin_message'] ?? '');
$acting_user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
if (!$id || !$datetime){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Missing fields']); exit; }
try{ $dt=new DateTime($datetime); } catch(Exception $e){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid datetime']); exit; }
$at = $dt->format('Y-m-d H:i:00');
// Permission: allow admin or owning counselor only
$own = $conn->prepare("SELECT user_id FROM appointments WHERE id = ?");
$own->bind_param('i', $id);
$own->execute();
$ownerRow = $own->get_result()->fetch_assoc();
if (!$ownerRow) { echo json_encode(['success'=>false,'message'=>'Not found']); exit; }
if ($role !== 'Guidance Admin' && $ownerRow['user_id'] !== $acting_user_id) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }

// Conflict check for counselor
$chk=$conn->prepare("SELECT COUNT(*) AS c FROM appointments WHERE user_id=? AND id<>? AND appointment_date < ? AND DATE_ADD(appointment_date, INTERVAL 1 HOUR) > ?");
$chk->bind_param('siss', $ownerRow['user_id'], $id, $at, $at);
$chk->execute();
$c=$chk->get_result()->fetch_assoc()['c'] ?? 0;
if ($c > 0) { echo json_encode(['success'=>false,'message'=>'Counselor slot is already booked.']); exit; }

$stmt=$conn->prepare("UPDATE appointments SET appointment_date=?, status='approved', admin_message=? WHERE id=?");
$stmt->bind_param('ssi', $at, $msg, $id);
$ok=$stmt->execute();
if ($ok) {
  // Notify student
  $stu = $conn->prepare("SELECT u.email, TRIM(CONCAT(u.first_name,' ',u.last_name)) AS name FROM appointments a JOIN users u ON a.student_id=u.user_id WHERE a.id=?");
  $stu->bind_param('i', $id);
  $stu->execute(); $s = $stu->get_result()->fetch_assoc();
  if ($s && !empty($s['email'])) {
    $html = '<p>Hello '.htmlspecialchars($s['name']).',</p><p>Your guidance appointment has been scheduled/updated.</p><p><strong>Date & Time:</strong> '.htmlspecialchars($at).'</p>'; 
    @send_email($s['email'], 'Appointment Scheduled', $html, strip_tags($html));
  }
}
echo json_encode(['success'=>$ok, 'message'=>$ok?'Scheduled and approved.':'Update failed']);