<?php
include 'config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'Dormitory Admin')) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$metrics = [
    'pending'=>0,'approved'=>0,'rejected'=>0,
    'occupiedBeds'=>0,'totalBeds'=>0,'availableBeds'=>0,
    'statusDist'=>['Pending'=>0,'Approved'=>0,'Rejected'=>0],
    'appsByDay'=>[],
    'recent'=>[]
];

// Counts
$r = $conn->query("SELECT SUM(occupied_beds) AS occ, SUM(total_beds) AS tot FROM rooms");
if ($r && ($row=$r->fetch_assoc())) {
    $metrics['occupiedBeds'] = (int)($row['occ'] ?? 0);
    $metrics['totalBeds'] = (int)($row['tot'] ?? 0);
    $metrics['availableBeds'] = max(0, $metrics['totalBeds'] - $metrics['occupiedBeds']);
}

foreach (['Pending','Approved','Rejected'] as $st) {
    $q = $conn->query("SELECT COUNT(*) AS c FROM student_room_applications WHERE status='".$conn->real_escape_string($st)."'");
    $c = $q ? (int)($q->fetch_assoc()['c'] ?? 0) : 0;
    $metrics[strtolower($st)] = $c;
    $metrics['statusDist'][$st] = $c;
}

// Apps by last 7 days
$days = [];
for ($i=6;$i>=0;$i--) { $days[] = date('Y-m-d', strtotime('-'.$i.' day')); }
$map = [];
$q2 = $conn->query("SELECT DATE(applied_at) AS d, COUNT(*) AS c FROM student_room_applications WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(applied_at)");
if ($q2) { while($r=$q2->fetch_assoc()){ $map[$r['d']] = (int)$r['c']; } }
foreach ($days as $d) { $metrics['appsByDay'][] = ['d'=>$d, 'c'=>($map[$d] ?? 0)]; }

// Recent applications
$recent = $conn->query("SELECT sra.id, sra.user_id, u.first_name, u.last_name, sra.room_id, r.name AS room_name, sra.status, sra.applied_at FROM student_room_applications sra JOIN users u ON sra.user_id=u.user_id JOIN rooms r ON sra.room_id=r.id ORDER BY sra.applied_at DESC LIMIT 6");
if ($recent) {
    while ($row = $recent->fetch_assoc()) {
        $metrics['recent'][] = [
            'id'=>(int)$row['id'],
            'student'=>$row['first_name'].' '.$row['last_name'],
            'room'=>$row['room_name'],
            'status'=>$row['status'],
            'applied_at'=>$row['applied_at']
        ];
    }
}

echo json_encode(['success'=>true,'data'=>$metrics]);
?>

