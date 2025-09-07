<?php
include 'config.php';
session_start();

// Check if the user is logged in and is a guidance admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guidance Admin') {
    header("Location: login.php");
    exit();
}
// Dashboard metrics
$totalPending = 0; $totalApprovedToday = 0; $upcomingCount = 0; $nextAppt = null; $upcomingRows = [];
$statusCounts = ['pending'=>0,'approved'=>0,'completed'=>0,'rejected'=>0,'cancelled'=>0];
$approvalsByDay = [];
try {
    $q1 = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status IN ('pending','Pending')");
    if ($q1) { $totalPending = (int)($q1->fetch_assoc()['c'] ?? 0); }
    $q2 = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status IN ('approved','Approved') AND DATE(appointment_date) = CURDATE()");
    if ($q2) { $totalApprovedToday = (int)($q2->fetch_assoc()['c'] ?? 0); }
    $q3 = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status IN ('approved','Approved') AND appointment_date >= NOW()");
    if ($q3) { $upcomingCount = (int)($q3->fetch_assoc()['c'] ?? 0); }
    $q4 = $conn->query("SELECT a.id, a.appointment_date, a.status, u.first_name, u.last_name FROM appointments a JOIN users u ON a.student_id=u.user_id WHERE a.appointment_date >= NOW() AND a.status IN ('approved','Approved') ORDER BY a.appointment_date ASC LIMIT 1");
    if ($q4) { $nextAppt = $q4->fetch_assoc(); }
    $q5 = $conn->query("SELECT a.id, a.appointment_date, a.status, u.first_name, u.last_name, a.reason FROM appointments a JOIN users u ON a.student_id=u.user_id WHERE a.appointment_date >= NOW() AND a.status IN ('approved','Approved','pending','Pending') ORDER BY a.appointment_date ASC LIMIT 6");
    if ($q5) { while($r=$q5->fetch_assoc()){ $upcomingRows[]=$r; } }
    // Status distribution
    $q6 = $conn->query("SELECT LOWER(status) AS s, COUNT(*) AS c FROM appointments GROUP BY LOWER(status)");
    if ($q6) { while($r=$q6->fetch_assoc()){ $s=$r['s']; if(isset($statusCounts[$s])) $statusCounts[$s]=(int)$r['c']; } }
    // Approvals by day (last 7 days)
    $q7 = $conn->query("SELECT DATE(appointment_date) AS d, COUNT(*) AS c FROM appointments WHERE status IN ('approved','Approved') AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(appointment_date) ORDER BY d ASC");
    $days = [];
    for($i=6;$i>=0;$i--){ $days[] = date('Y-m-d', strtotime('-'.$i.' day')); }
    $map = [];
    if ($q7) { while($r=$q7->fetch_assoc()){ $map[$r['d']] = (int)$r['c']; } }
    foreach($days as $d){ $approvalsByDay[] = ['d'=>$d, 'c'=>($map[$d] ?? 0)]; }
} catch (Throwable $e) { /* ignore metrics errors */ }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guidance Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter','Segoe UI',Arial,sans-serif; background: #0f172a; }
        .main-content { margin-left: 280px; padding: 24px; background: radial-gradient(1200px 600px at 10% -20%, #1b2a52 0%, rgba(27,42,82,0) 60%), radial-gradient(800px 400px at 120% 0%, #0b5ed7 0%, rgba(11,94,215,0) 55%); }
        .hero { position:relative; overflow:hidden; background: linear-gradient(135deg, #003366 0%, #0b5ed7 100%); color:#fff; border-radius:20px; padding:28px; box-shadow:0 22px 60px rgba(2,32,71,.35); backdrop-filter: saturate(120%); }
        .hero::before, .hero::after { content:""; position:absolute; width:220px; height:220px; border-radius:50%; filter: blur(40px); opacity:.35; animation: float 9s ease-in-out infinite; }
        .hero::before { background:#12b886; top:-40px; left:-40px; }
        .hero::after { background:#845ef7; bottom:-60px; right:-40px; animation-duration: 11s; }
        .hero h2 { position:relative; margin:0 0 8px; font-weight:900; letter-spacing:.3px; text-shadow: 0 6px 22px rgba(0,0,0,.25); }
        .hero p { position:relative; margin:0; opacity:.96; }
        @keyframes float { 0%{ transform: translateY(0) } 50%{ transform: translateY(-12px) } 100%{ transform: translateY(0) } }
        .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px,1fr)); gap:18px; margin-top:18px; }
        .stat { position:relative; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); border-radius:16px; padding:20px; box-shadow: 0 18px 50px rgba(2,32,71,.45); backdrop-filter: blur(8px) saturate(120%); display:flex; align-items:center; gap:16px; transition: transform .15s ease, box-shadow .15s ease; }
        .stat:hover { transform: translateY(-2px); box-shadow: 0 24px 70px rgba(2,32,71,.55); }
        .stat .ic { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; box-shadow:0 10px 26px rgba(2,32,71,.35); }
        .ic-pending { background:linear-gradient(135deg, #f59f00, #ffcd39); }
        .ic-approved { background:linear-gradient(135deg, #12b886, #38d9a9); }
        .ic-upcoming { background:linear-gradient(135deg, #0d6efd, #74c0fc); }
        .ic-next { background:linear-gradient(135deg, #845ef7, #b197fc); }
        .stat .txt { color:#e2e8f0; }
        .stat .txt .k { font-size:28px; font-weight:900; line-height:1; letter-spacing:.2px; text-shadow: 0 4px 16px rgba(0,0,0,.25); }
        .stat .txt .l { font-size:12px; color:#cbd5e1; margin-top:4px; text-transform: uppercase; letter-spacing:.8px; }
        .card { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); border-radius:16px; padding:20px; box-shadow: 0 18px 50px rgba(2,32,71,.45); backdrop-filter: blur(8px) saturate(120%); color:#e2e8f0; }
        .card h3 { margin:0 0 14px; color:#fff; letter-spacing:.2px; }
        .quick { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); gap:12px; }
        .quick a { background:linear-gradient(135deg, #0d6efd, #74c0fc); color:#fff; text-decoration:none; padding:14px 16px; border-radius:14px; display:flex; align-items:center; gap:10px; box-shadow:0 14px 34px rgba(13,110,253,.35); font-weight:700; border:1px solid rgba(255,255,255,.18); transition: transform .12s ease, filter .12s ease; }
        .quick a:hover { transform: translateY(-2px); filter: brightness(1.02); }
        .quick a.secondary { background:linear-gradient(135deg, #12b886, #69db7c); box-shadow:0 14px 34px rgba(18,184,134,.35); }
        .quick a.warning { background:linear-gradient(135deg, #f59f00, #fcc419); box-shadow:0 14px 34px rgba(245,159,0,.35); }
        table { width:100%; border-collapse:collapse; }
        thead th { position:sticky; top:0; background:rgba(255,255,255,.08); color:#cbd5e1; font-weight:700; padding:12px; border-bottom:1px solid rgba(255,255,255,.12); backdrop-filter: blur(4px); }
        tbody td { padding:12px; border-bottom:1px solid rgba(255,255,255,.08); color:#e2e8f0; }
        .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800; }
        .approved { background:rgba(17,201,152,.18); color:#a7f3d0; border:1px solid rgba(17,201,152,.35); }
        .pending { background:rgba(245,159,0,.18); color:#ffe08a; border:1px solid rgba(245,159,0,.35); }
        .countup { opacity:0; transform: translateY(4px); transition: opacity .4s ease, transform .4s ease; }
        .countup.visible { opacity:1; transform:none; }
        .charts { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px,1fr)); gap:18px; margin-top:18px; }
        .glass { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); border-radius:16px; padding:16px; box-shadow: 0 18px 50px rgba(2,32,71,.45); backdrop-filter: blur(8px) saturate(120%); }
    </style>
</head>
<body>
    <?php include 'guidance_admin_header.php'; ?>

    <div class="main-content">
        <div class="hero">
            <h2><i class="fa-solid fa-shield-heart"></i> Guidance Admin Dashboard</h2>
            <p>Oversee requests, appointments, and reports with clarity.</p>
        </div>

        <div class="grid" style="margin-top:18px;">
            <div class="stat">
                <div class="ic ic-pending"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="txt">
                    <div class="k countup" data-target="<?php echo htmlspecialchars((string)$totalPending); ?>">0</div>
                    <div class="l">Pending Requests</div>
                </div>
            </div>
            <div class="stat">
                <div class="ic ic-approved"><i class="fa-solid fa-check-circle"></i></div>
                <div class="txt">
                    <div class="k countup" data-target="<?php echo htmlspecialchars((string)$totalApprovedToday); ?>">0</div>
                    <div class="l">Approved Today</div>
                </div>
            </div>
            <div class="stat">
                <div class="ic ic-upcoming"><i class="fa-solid fa-calendar-day"></i></div>
                <div class="txt">
                    <div class="k countup" data-target="<?php echo htmlspecialchars((string)$upcomingCount); ?>">0</div>
                    <div class="l">Upcoming Appts</div>
                </div>
            </div>
            <div class="stat">
                <div class="ic ic-next"><i class="fa-solid fa-bell"></i></div>
                <div class="txt">
                    <div class="k"><?php echo $nextAppt ? htmlspecialchars(date('M d, H:i', strtotime($nextAppt['appointment_date']))) : '—'; ?></div>
                    <div class="l">Next Appointment</div>
                </div>
            </div>
        </div>

        <div class="charts">
            <div class="glass">
                <h3 style="color:#fff; margin:0 0 10px;">Status Distribution</h3>
                <canvas id="statusDonut" height="240"></canvas>
            </div>
            <div class="glass">
                <h3 style="color:#fff; margin:0 0 10px;">Approvals (7 days)</h3>
                <canvas id="approvalsLine" height="240"></canvas>
            </div>
        </div>

        <div class="grid" style="margin-top:18px;">
            <div class="card">
                <h3>Quick Actions</h3>
                <div class="quick">
                    <a href="guidance_list_admin.php"><i class="fa-solid fa-list"></i> Manage Requests</a>
                    <a href="guidance_calendar_admin.php" class="secondary"><i class="fa-solid fa-calendar"></i> Open Calendar</a>
                    <a href="guidance_blackouts_admin.php" class="warning"><i class="fa-solid fa-cloud-slash"></i> Blackout Dates</a>
                    <a href="generate_reports.php"><i class="fa-solid fa-chart-line"></i> Reports</a>
                </div>
            </div>
            <div class="card">
                <h3>Upcoming Appointments</h3>
                <?php if (count($upcomingRows)): ?>
                <table>
                    <thead><tr><th>When</th><th>Student</th><th>Status</th><th>Reason</th></tr></thead>
                    <tbody>
                        <?php foreach($upcomingRows as $r): $st=strtolower($r['status']); $cls=$st==='approved'?'approved':'pending'; ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($r['appointment_date']))); ?></td>
                            <td><?php echo htmlspecialchars(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')); ?></td>
                            <td><span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars(ucfirst($st)); ?></span></td>
                            <td><?php echo htmlspecialchars($r['reason'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="color:#cbd5e1;">No upcoming appointments.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<script>
// Count-up animation
(function(){
  const els = Array.from(document.querySelectorAll('.countup'));
  const obs = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      if(entry.isIntersecting){
        const el = entry.target; const target = parseInt(el.getAttribute('data-target')||'0',10);
        let cur = 0; const dur = 900; const start = performance.now();
        function tick(t){ const p = Math.min(1, (t-start)/dur); cur = Math.floor(target * (0.5 - Math.cos(Math.PI*p)/2)); el.textContent = cur.toString(); if(p<1) requestAnimationFrame(tick); }
        el.classList.add('visible'); requestAnimationFrame(tick); obs.unobserve(el);
      }
    });
  }, { threshold: 0.6 });
  els.forEach(e=>obs.observe(e));
})();

// Charts
const donutCtx = document.getElementById('statusDonut').getContext('2d');
const donut = new Chart(donutCtx, {
  type: 'doughnut',
  data: {
    labels: ['Pending','Approved','Completed','Rejected','Cancelled'],
    datasets: [{
      data: [
        <?php echo (int)$statusCounts['pending']; ?>,
        <?php echo (int)$statusCounts['approved']; ?>,
        <?php echo (int)$statusCounts['completed']; ?>,
        <?php echo (int)$statusCounts['rejected']; ?>,
        <?php echo (int)$statusCounts['cancelled']; ?>
      ],
      backgroundColor: ['#ffcd39','#38d9a9','#74c0fc','#fa5252','#adb5bd'],
      borderWidth: 0
    }]
  },
  options: { plugins: { legend: { labels: { color:'#e2e8f0' } }}, cutout: '65%'}
});

const lineCtx = document.getElementById('approvalsLine').getContext('2d');
const line = new Chart(lineCtx, {
  type: 'line',
  data: {
    labels: [<?php echo implode(',', array_map(fn($x)=>'"'.date('M d', strtotime($x['d'])).'"', $approvalsByDay)); ?>],
    datasets: [{
      label: 'Approved', data: [<?php echo implode(',', array_map(fn($x)=>$x['c'], $approvalsByDay)); ?>],
      fill: true, tension: .35, borderColor: '#74c0fc', backgroundColor:'rgba(116,192,252,.18)', pointRadius: 3, pointBackgroundColor:'#74c0fc'
    }]
  },
  options: { plugins:{legend:{labels:{color:'#e2e8f0'}}}, scales:{ x:{ ticks:{color:'#cbd5e1'}}, y:{ ticks:{color:'#cbd5e1'} } } }
});
</script>
</body>
</html>