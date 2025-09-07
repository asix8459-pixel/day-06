<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) { http_response_code(403); exit; }
header('Content-Type: application/json; charset=utf-8');

$res = $conn->query("SELECT id, appointment_date, status, student_id FROM appointments WHERE appointment_date IS NOT NULL");
$events = [];
while ($r = $res->fetch_assoc()) {
    $status = strtolower($r['status']);
    // UI/UX: Improved colors and status icons
    if ($status === 'approved') {
        $color = '#33cc99';
        $icon  = '✅';
    } elseif ($status === 'pending') {
        $color = '#ffc107';
        $icon  = '⏳';
    } elseif ($status === 'completed') {
        $color = '#0d6efd';
        $icon  = '✔️';
    } else {
        $color = '#adb5bd';
        $icon  = '❌';
    }
    $title = $icon . ' Student #' . $r['student_id'] . ' (' . ucfirst($status) . ')';
    $startIso = date('c', strtotime($r['appointment_date']));
    $endIso = date('c', strtotime($r['appointment_date'] . ' +1 hour'));
    $events[] = [
        'id'    => $r['id'],
        'title' => $title,
        'start' => $startIso,
        'end'   => $endIso,
        'color' => $color
    ];
}
echo json_encode($events);