<?php
include 'config.php'; 
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$student_id = $_SESSION['user_id'];
// Fetch the student's appointments with counselor name if present
$query = "SELECT a.*, u.first_name AS counselor_first, u.last_name AS counselor_last
          FROM appointments a
          LEFT JOIN users u ON a.user_id = u.user_id
          WHERE a.student_id = ?
          ORDER BY a.appointment_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .main-content {
            margin-top: 100px;
            margin-left: 350px; /* Adjust based on the width of the sidebar */
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333333;
        }

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

        th {
            background-color: #f4f4f9;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge { display:inline-block; padding:4px 8px; border-radius: 12px; font-size: 12px; }
        .bg-pending { background:#ffc107; color:#212529; }
        .bg-approved { background:#28a745; color:#fff; }
        .bg-completed { background:#0d6efd; color:#fff; }
        .bg-rejected { background:#dc3545; color:#fff; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/student_theme.css">
</head>
<body>
    <?php include 'student_header.php'; ?>
<?php include('student_header.php'); ?>
    <div class="main-content">
        <h2>My Appointments</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date/Time</th>
                    <th>Counselor</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Admin Message</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                        <td><?= htmlspecialchars(trim(($row['counselor_first'] ?? '').' '.($row['counselor_last'] ?? '')) ?: '—') ?></td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td>
                            <?php $st=strtolower($row['status']); $cls=$st==='approved'?'bg-approved':($st==='completed'?'bg-completed':($st==='rejected'?'bg-rejected':'bg-pending')); ?>
                            <span class="badge <?= $cls ?>"><?= htmlspecialchars($row['status']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($row['admin_message']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>