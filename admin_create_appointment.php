<?php
require 'config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']); exit; }

$student_id = $_POST['student_id'] ?? '';
$counselor_id = $_POST['counselor_id'] ?? '';
$datetime = $_POST['datetime'] ?? '';
$reason = trim($_POST['reason'] ?? '');
if ($student_id === '' || $counselor_id === '' || $datetime === '') { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Missing fields']); exit; }

try { $dt=new DateTime($datetime); } catch(Exception $e){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid date/time']); exit; }
$startStr = $dt->format('Y-m-d H:i:00');
$endStr = $dt->modify('+1 hour')->format('Y-m-d H:i:00');

// Conflict check for counselor
$chk=$conn->prepare("SELECT COUNT(*) AS c FROM appointments WHERE user_id=? AND appointment_date < ? AND DATE_ADD(appointment_date, INTERVAL 1 HOUR) > ?");
$chk->bind_param('sss', $counselor_id, $startStr, $endStr);
$chk->execute(); $c=$chk->get_result()->fetch_assoc()['c'] ?? 0;
if ($c > 0) { echo json_encode(['success'=>false,'message'=>'Counselor slot is already booked.']); exit; }

$stmt=$conn->prepare("INSERT INTO appointments (student_id, user_id, appointment_date, reason, status) VALUES (?, ?, ?, ?, 'approved')");
$stmt->bind_param('ssss', $student_id, $counselor_id, $startStr, $reason);
$ok=$stmt->execute();
echo json_encode(['success'=>$ok, 'message'=>$ok?'Appointment created.':'Failed to create appointment']);