
<?php
require 'config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']); exit; }

$id = (int)($_POST['id'] ?? 0);
$datetime = trim($_POST['datetime'] ?? '');
$msg = trim($_POST['admin_message'] ?? '');
if (!$id || !$datetime){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Missing fields']); exit; }
try{ $dt=new DateTime($datetime); } catch(Exception $e){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid datetime']); exit; }
$at = $dt->format('Y-m-d H:i:00');

$stmt=$conn->prepare("UPDATE appointments SET appointment_date=?, status='approved', admin_message=? WHERE id=?");
$stmt->bind_param('ssi', $at, $msg, $id);
$ok=$stmt->execute();
echo json_encode(['success'=>$ok, 'message'=>$ok?'Scheduled and approved.':'Update failed']);