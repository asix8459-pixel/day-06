<?php
session_start();
include 'config.php';
// Require Dormitory Admin role
if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'Dormitory Admin')) {
    header('Location: login.php');
    exit;
}

// Fetch counts for dashboard stats
$pendingCount = (int)($conn->query("SELECT COUNT(*) AS total FROM student_room_applications WHERE `status` = 'Pending'")->fetch_assoc()['total'] ?? 0);
$approvedCount = (int)($conn->query("SELECT COUNT(*) AS total FROM student_room_applications WHERE `status` = 'Approved'")->fetch_assoc()['total'] ?? 0);
$rejectedCount = (int)($conn->query("SELECT COUNT(*) AS total FROM student_room_applications WHERE `status` = 'Rejected'")->fetch_assoc()['total'] ?? 0);

// Calculate occupied and available beds
$occupiedBeds = (int)($conn->query("SELECT SUM(occupied_beds) AS total FROM rooms")->fetch_assoc()['total'] ?? 0);
$totalBeds = (int)($conn->query("SELECT SUM(total_beds) AS total FROM rooms")->fetch_assoc()['total'] ?? 0);
$availableBeds = max(0, $totalBeds - $occupiedBeds);
// Status distribution for donut
$statusDist = ['Pending'=>0,'Approved'=>0,'Rejected'=>0];
$q = $conn->query("SELECT `status`, COUNT(*) AS c FROM student_room_applications GROUP BY `status`");
if ($q) { while($r=$q->fetch_assoc()){ $s=$r['status']; if(isset($statusDist[$s])) $statusDist[$s]=(int)$r['c']; } }
// Applications by day (last 7 days)
$appsByDay = [];
$days = [];
for($i=6;$i>=0;$i--){ $days[] = date('Y-m-d', strtotime('-'.$i.' day')); }
$map = [];
$q2 = $conn->query("SELECT DATE(applied_at) AS d, COUNT(*) AS c FROM student_room_applications WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(applied_at)");
if ($q2) { while($r=$q2->fetch_assoc()){ $map[$r['d']] = (int)$r['c']; } }
foreach($days as $d){ $appsByDay[] = ['d'=>$d, 'c'=>($map[$d] ?? 0)]; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dormitory Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter','Segoe UI',Arial,sans-serif;background:#0f172a;display:flex}
        .sidebar{width:260px;height:100vh;background:linear-gradient(180deg,#003366 80%,#005fa3 100%);color:#fff;position:fixed;padding-top:24px;box-shadow:0 0 24px rgba(0,44,77,.08)}
        .sidebar-header{display:flex;align-items:center;justify-content:center;padding:19px;background:#002855;font-size:1.5rem;font-weight:900;color:#FFD700;border-radius:12px 12px 0 0;box-shadow:0 4px 12px rgba(0,0,0,.08)}
        .sidebar-menu a{display:block;color:#fff;text-decoration:none;margin:8px 14px;padding:12px 16px;border-radius:10px;background:#004080;transition:.12s ease}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:#FFD700;color:#003366;transform:translateY(-1px)}
        .logout-btn{display:block;margin:32px 14px;padding:12px 16px;border-radius:10px;background:linear-gradient(90deg,#d9534f 60%,#c9302c 100%);color:#fff;text-decoration:none}
        .main-content{margin-left:260px;padding:24px;width:calc(100% - 260px);min-height:100vh;background:radial-gradient(1200px 600px at 10% -20%,#1b2a52 0%,rgba(27,42,82,0) 60%),radial-gradient(800px 400px at 120% 0%,#0b5ed7 0%,rgba(11,94,215,0) 55%)}
        .hero{position:relative;overflow:hidden;background:linear-gradient(135deg,#003366 0%,#0b5ed7 100%);color:#fff;border-radius:20px;padding:28px;box-shadow:0 22px 60px rgba(2,32,71,.35)}
        .hero h2{margin:0 0 6px;font-weight:900;letter-spacing:.3px;text-shadow:0 6px 22px rgba(0,0,0,.25)}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;margin-top:18px}
        .stat{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:18px;box-shadow:0 18px 50px rgba(2,32,71,.45);backdrop-filter:blur(8px) saturate(120%);display:flex;align-items:center;gap:12px;transition:transform .15s, box-shadow .15s}
        .stat:hover{transform:translateY(-2px);box-shadow:0 24px 70px rgba(2,32,71,.55)}
        .stat .ic{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 10px 26px rgba(2,32,71,.35)}
        .ic-pending{background:linear-gradient(135deg,#f59f00,#ffcd39)}
        .ic-approved{background:linear-gradient(135deg,#12b886,#38d9a9)}
        .ic-rejected{background:linear-gradient(135deg,#e03131,#ff6b6b)}
        .ic-occupied{background:linear-gradient(135deg,#845ef7,#b197fc)}
        .ic-available{background:linear-gradient(135deg,#0d6efd,#74c0fc)}
        .stat .txt{color:#e2e8f0}
        .stat .txt .k{font-weight:900;font-size:26px}
        .stat .txt .l{font-size:12px;color:#cbd5e1;text-transform:uppercase;letter-spacing:.8px}
        .card-glass{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:16px;box-shadow:0 18px 50px rgba(2,32,71,.45);backdrop-filter:blur(8px) saturate(120%);color:#e2e8f0}
        table{width:100%;border-collapse:collapse}
        thead th{position:sticky;top:0;background:rgba(255,255,255,.08);color:#cbd5e1;font-weight:700;padding:10px;border-bottom:1px solid rgba(255,255,255,.12)}
        tbody td{padding:10px;border-bottom:1px solid rgba(255,255,255,.08);color:#e2e8f0}
    </style>
    <script>
        function navigateTo(page) {
            window.location.href = page;
        }

        function logout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = "login.php";
            }
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa fa-building"></i>
            <span>Dormitory Management</span>
        </div>
        <div class="sidebar-menu">
            <a href="admin_dormitory_dashboard.php" class="active" style="background-color: #FFD700; color: #003366; font-weight: bold;"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="room_assignments.php"><i class="fas fa-bed"></i> Room Allocation</a>
            <a href="dormitory_manage_applications.php"><i class="fas fa-file-alt"></i> Room Applications</a>
            <a href="dormitory_room_management.php"><i class="fas fa-users"></i> View Boarders</a>
            <a href="admin_manage_dorm_agreements.php"><i class="fas fa-file-signature"></i> Agreements</a>
        </div>
        <a href="#" class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="hero">
            <h2><i class="fa-solid fa-building-columns"></i> Dormitory Admin Dashboard</h2>
            <p>Monitor room capacity, applications, and operations at a glance.</p>
        </div>
        <div class="grid">
            <div class="stat" onclick="navigateTo('dormitory_manage_applications.php')">
                <div class="ic ic-pending"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="txt"><div class="k"><?= $pendingCount ?></div><div class="l">Pending Applications</div></div>
            </div>
            <div class="stat" onclick="navigateTo('approved_applications.php')">
                <div class="ic ic-approved"><i class="fa-solid fa-check-circle"></i></div>
                <div class="txt"><div class="k"><?= $approvedCount ?></div><div class="l">Approved</div></div>
            </div>
            <div class="stat" onclick="navigateTo('rejected_applications.php')">
                <div class="ic ic-rejected"><i class="fa-solid fa-xmark"></i></div>
                <div class="txt"><div class="k"><?= $rejectedCount ?></div><div class="l">Rejected</div></div>
            </div>
            <div class="stat" onclick="navigateTo('occupied_rooms.php')">
                <div class="ic ic-occupied"><i class="fa-solid fa-bed"></i></div>
                <div class="txt"><div class="k"><?= $occupiedBeds ?></div><div class="l">Occupied Beds</div></div>
            </div>
            <div class="stat" onclick="navigateTo('available_rooms.php')">
                <div class="ic ic-available"><i class="fa-solid fa-door-open"></i></div>
                <div class="txt"><div class="k"><?= $availableBeds ?></div><div class="l">Available Beds</div></div>
            </div>
        </div>

        <div class="grid" style="margin-top:18px;">
            <div class="card-glass">
                <h5 style="margin:0 0 10px; color:#fff;">Applications by Status</h5>
                <canvas id="statusDonut" height="220"></canvas>
            </div>
            <div class="card-glass">
                <h5 style="margin:0 0 10px; color:#fff;">Applications (Last 7 days)</h5>
                <canvas id="appsLine" height="220"></canvas>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const donut = new Chart(document.getElementById('statusDonut'), {
            type:'doughnut', data:{ labels:['Pending','Approved','Rejected'], datasets:[{ data:[<?= (int)$statusDist['Pending'] ?>,<?= (int)$statusDist['Approved'] ?>,<?= (int)$statusDist['Rejected'] ?>], backgroundColor:['#ffcd39','#38d9a9','#ff6b6b'], borderWidth:0 }] }, options:{ plugins:{legend:{labels:{color:'#e2e8f0'}}}, cutout:'65%'}
        });
        const line = new Chart(document.getElementById('appsLine'), {
            type:'line', data:{ labels:[<?php echo implode(',', array_map(fn($x)=>'"'.date('M d', strtotime($x['d'])).'"', $appsByDay)); ?>], datasets:[{ label:'Apps', data:[<?php echo implode(',', array_map(fn($x)=>$x['c'], $appsByDay)); ?>], fill:true, tension:.35, borderColor:'#74c0fc', backgroundColor:'rgba(116,192,252,.18)', pointRadius:3, pointBackgroundColor:'#74c0fc' }] }, options:{ plugins:{legend:{labels:{color:'#e2e8f0'}}}, scales:{ x:{ ticks:{color:'#cbd5e1'}}, y:{ ticks:{color:'#cbd5e1'} } } }
        });
    </script>
</body>
</html>
