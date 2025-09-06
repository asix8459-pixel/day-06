<?php
include 'config.php';
session_start();

// Require guidance role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Guidance Admin','Counselor'], true)) {
    header('Location: login.php');
    exit;
}

// Fetch guidance requests for the logged-in admin/counselor
$user_id = $_SESSION['user_id'];
$baseQuery = "SELECT appointments.*, students.first_name AS student_first_name, students.last_name AS student_last_name
          FROM appointments
          JOIN users AS students ON appointments.student_id = students.user_id
          WHERE appointments.user_id = ?";
$params = [$user_id];
$types = 's';
if (!empty($_GET['status'])) { $baseQuery .= " AND LOWER(appointments.status) = ?"; $params[] = strtolower($_GET['status']); $types .= 's'; }
if (!empty($_GET['from'])) { $baseQuery .= " AND DATE(appointments.appointment_date) >= ?"; $params[] = $_GET['from']; $types .= 's'; }
if (!empty($_GET['to'])) { $baseQuery .= " AND DATE(appointments.appointment_date) <= ?"; $params[] = $_GET['to']; $types .= 's'; }
$baseQuery .= " ORDER BY COALESCE(appointments.appointment_date, appointments.id) DESC";
$stmt = $conn->prepare($baseQuery);
@$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// CSRF token for modals
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guidance Requests</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            display: flex;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
            min-height: 100vh;
            background: white;
        }
        h2 { text-align: center; color: #333333; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
        }
        th { background-color: #f4f4f9; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .action-link, .delete-link {
            color: white;
            text-decoration: none;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            margin-right: 5px;
            display: inline-block;
        }
        .action-link { background-color: #007bff; }
        .action-link:hover { background-color: #0056b3; }
        .delete-link { background-color: #d9534f; }
        .delete-link:hover { background-color: #c9302c; }
        .badge { display:inline-block; padding:4px 8px; border-radius:12px; font-size:12px; }
        .bg-pending { background:#ffc107; color:#212529; }
        .bg-approved { background:#28a745; color:#fff; }
        .bg-completed { background:#0d6efd; color:#fff; }
        .bg-rejected { background:#dc3545; color:#fff; }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            text-align: center;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'guidance_admin_header.php'; ?>
    <div class="main-content">
        <h2>Guidance Requests</h2>
        <form method="GET" class="filters" style="display:flex; gap:12px; align-items:end; margin:10px 0;">
            <div>
                <label>Status</label><br>
                <select name="status" style="padding:8px 10px; border:1px solid #e5e7eb; border-radius:8px;">
                    <option value="">All</option>
                    <option value="pending" <?= isset($_GET['status']) && $_GET['status']==='pending'?'selected':'' ?>>Pending</option>
                    <option value="approved" <?= isset($_GET['status']) && $_GET['status']==='approved'?'selected':'' ?>>Approved</option>
                    <option value="completed" <?= isset($_GET['status']) && $_GET['status']==='completed'?'selected':'' ?>>Completed</option>
                    <option value="rejected" <?= isset($_GET['status']) && $_GET['status']==='rejected'?'selected':'' ?>>Rejected</option>
                    <option value="cancelled" <?= isset($_GET['status']) && $_GET['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                </select>
            </div>
            <div>
                <label>Date From</label><br>
                <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>" style="padding:8px 10px; border:1px solid #e5e7eb; border-radius:8px;">
            </div>
            <div>
                <label>Date To</label><br>
                <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>" style="padding:8px 10px; border:1px solid #e5e7eb; border-radius:8px;">
            </div>
            <div>
                <button type="submit" style="background:#0d6efd; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer;">Filter</button>
            </div>
        </form>
        <?php
        $resReqs = [];
        $conn->query("CREATE TABLE IF NOT EXISTS reschedule_requests (id INT AUTO_INCREMENT PRIMARY KEY, appointment_id INT NOT NULL, student_id VARCHAR(64) NOT NULL, requested_datetime DATETIME NOT NULL, note TEXT NULL, status VARCHAR(16) NOT NULL DEFAULT 'open', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX(appointment_id), INDEX(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $rr = $conn->query("SELECT appointment_id, requested_datetime, note FROM reschedule_requests WHERE status='open'");
        if ($rr) { while($r=$rr->fetch_assoc()){ $resReqs[(int)$r['appointment_id']]=$r; } }
        ?>
        <?php if (isset($_GET['success'])): ?>
            <p class="success-message"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Appointment Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Admin Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['student_first_name'] . ' ' . $row['student_last_name']) ?></td>
                            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td>
                                <?php
                                $st = strtolower($row['status'] ?? 'pending');
                                $cls = $st==='approved'?'bg-approved':($st==='completed'?'bg-completed':($st==='rejected'?'bg-rejected':'bg-pending'));
                                ?>
                                <span class="badge <?= $cls ?>"><?= htmlspecialchars($row['status']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['admin_message']) ?></td>
                            <td>
                                <button class="action-link" onclick="openUpdateModal('<?= htmlspecialchars($row['id']) ?>', '<?= htmlspecialchars($row['status']) ?>')">Update</button>
                                <button class="action-link" onclick="openScheduleModal('<?= htmlspecialchars($row['id']) ?>')">Schedule</button>
                                <?php if (isset($resReqs[(int)$row['id']])): $rq=$resReqs[(int)$row['id']]; ?>
                                <span class="badge bg-pending" title="Reschedule requested to <?= htmlspecialchars($rq['requested_datetime']) ?><?= $rq['note']?(' - '.htmlspecialchars($rq['note'])):'' ?>">Reschedule Req</span>
                                <?php endif; ?>
                                <?php if (strtolower($row['status']) === 'approved'): ?>
                                <button class="action-link" style="background:#0d6efd" onclick="markCompleted('<?= htmlspecialchars($row['id']) ?>')">Complete</button>
                                <?php endif; ?>
                                <button class="delete-link" onclick="openDeleteModal('<?= htmlspecialchars($row['id']) ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No guidance requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- Update Modal -->
        <div id="updateModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('updateModal')">&times;</span>
                <h2>Update Guidance Request Status</h2>
                <form method="POST" class="update-form" action="update_guidance_status.php">
                    <input type="hidden" id="update_request_id" name="request_id">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <label for="admin_message">Admin Message:</label>
                    <textarea id="admin_message" name="admin_message"></textarea>
                    <button type="submit" name="update_status">Update Status</button>
                </form>
            </div>
        </div>
        <!-- Schedule Modal -->
        <div id="scheduleModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('scheduleModal')">&times;</span>
                <h2>Schedule Appointment</h2>
                <form id="scheduleForm">
                    <input type="hidden" id="schedule_request_id" name="id">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <div class="mb-3">
                        <label class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" name="datetime" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Message (optional)</label>
                        <textarea class="form-control" name="admin_message" rows="2"></textarea>
                    </div>
                    <button type="button" onclick="submitSchedule()">Save</button>
                </form>
            </div>
        </div>
        <!-- Delete Modal -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('deleteModal')">&times;</span>
                <h2>Delete Guidance Request</h2>
                <form method="POST" class="delete-form" action="delete_guidance_request.php">
                    <input type="hidden" id="delete_request_id" name="request_id">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <p>Are you sure you want to delete this request?</p>
                    <button type="submit" name="delete_request">Delete</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function openUpdateModal(request_id, currentStatus) {
            document.getElementById('update_request_id').value = request_id;
            document.getElementById('status').value = currentStatus.toLowerCase();
            document.getElementById('updateModal').style.display = "flex";
        }
        function openScheduleModal(request_id){
            document.getElementById('schedule_request_id').value = request_id;
            try {
                const res = <?= json_encode($resReqs) ?>;
                if (res && res[request_id] && res[request_id].requested_datetime) {
                    const input = document.querySelector('#scheduleForm input[name="datetime"]');
                    const dt = new Date(res[request_id].requested_datetime.replace(' ', 'T'));
                    dt.setMinutes(dt.getMinutes() - dt.getTimezoneOffset());
                    input.value = dt.toISOString().slice(0,16);
                }
            } catch(e){}
            document.getElementById('scheduleModal').style.display = 'flex';
        }
        function submitSchedule(){
            const form = document.getElementById('scheduleForm');
            const data = new URLSearchParams(new FormData(form));
            fetch('admin_schedule_appointment.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: data })
              .then(r=>r.json()).then(d=>{ alert(d.message||'Updated'); if(d.success) location.reload(); });
        }
        function markCompleted(request_id){
            if(!confirm('Mark as completed?')) return;
            const data = new URLSearchParams({ request_id, status:'completed', admin_message:'', update_status:'1', csrf_token: '<?= htmlspecialchars($_SESSION['csrf_token']) ?>' });
            fetch('update_guidance_status.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: data })
              .then(r=>r.text()).then(()=> location.reload());
        }
        function openDeleteModal(request_id) {
            document.getElementById('delete_request_id').value = request_id;
            document.getElementById('deleteModal').style.display = "flex";
        }
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById('updateModal')) {
                closeModal('updateModal');
            } else if (event.target == document.getElementById('deleteModal')) {
                closeModal('deleteModal');
            } else if (event.target == document.getElementById('scheduleModal')) {
                closeModal('scheduleModal');
            }
        }
    </script>
</body>
</html>