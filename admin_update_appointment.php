<?php
require 'config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']); exit; }

$id = (int)($_POST['id'] ?? 0);
$start = $_POST['start'] ?? '';
if (!$id || $start===''){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Bad request']); exit; }
try{ $dt=new DateTime($start); } catch(Exception $e){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid date']); exit; }
$date=$dt->format('Y-m-d H:i:s');

$stmt=$conn->prepare("UPDATE appointments SET appointment_date=? WHERE id=? AND status IN ('pending','approved','Pending','Approved')");
$stmt->bind_param('si', $date, $id);
$ok=$stmt->execute();
echo json_encode(['success'=>$ok, 'message'=>$ok?'Updated':'Update failed']);