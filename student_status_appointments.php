<?php
include 'config.php'; 
include 'csrf.php';
include 'mailer.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
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
$stmt->bind_param("s", $student_id);
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
    <div class="main-content">
        <h2>My Appointments</h2>
        <?php if (isset($_GET['success'])): ?>
            <p class="success-message" style="color:green; margin:10px 0;"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date/Time</th>
                    <th>Counselor</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Admin Message</th>
                    <th>Actions</th>
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
                            <?php $st=strtolower($row['status']); $cls=$st==='approved'?'bg-approved':($st==='completed'?'bg-completed':($st==='rejected'?'bg-rejected':($st==='cancelled'?'bg-rejected':'bg-pending'))); ?>
                            <span class="badge <?= $cls ?>"><?= htmlspecialchars($row['status']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($row['admin_message']) ?></td>
                        <td>
                            <div style="display:flex; gap:8px; align-items:center;">
                            <?php if (in_array(strtolower($row['status']), ['pending','approved'], true)): ?>
                                <form method="POST" action="cancel_guidance_request.php" onsubmit="return confirm('Cancel this appointment?');" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['id']) ?>">
                                    <button type="submit" style="background:#dc3545; color:#fff; border:none; padding:6px 10px; border-radius:4px; cursor:pointer;">Cancel</button>
                                </form>
                            <?php endif; ?>
                            <?php if (in_array(strtolower($row['status']), ['approved','completed'], true) && !empty($row['appointment_date'])): ?>
                                <?php
                                    $ics = ics_download_link((int)$row['id'], $row['appointment_date']);
                                    try {
                                        $dt = new DateTime($row['appointment_date']);
                                        $dtUtc = clone $dt; $dtUtc->setTimezone(new DateTimeZone('UTC'));
                                        $startUtc = $dtUtc->format('Ymd\THis\Z');
                                        $endUtc = (clone $dtUtc)->modify('+1 hour')->format('Ymd\THis\Z');
                                    } catch (Exception $e) {
                                        $startUtc = $endUtc = '';
                                    }
                                    $title = 'Guidance Appointment';
                                    $details = 'Reason: '.($row['reason'] ?? '');
                                    $gcal = $startUtc && $endUtc
                                      ? 'https://calendar.google.com/calendar/render?action=TEMPLATE&text='.rawurlencode($title).'&dates='.$startUtc.'/'.$endUtc.'&details='.rawurlencode($details)
                                      : '';
                                ?>
                                <a href="<?= htmlspecialchars($ics) ?>" title="Download .ics file for Outlook/Apple/Google" style="background:#0d6efd; color:#fff; padding:6px 10px; border-radius:4px; text-decoration:none;">Add to Calendar (.ics)</a>
                                <?php if ($gcal): ?>
                                <a href="<?= htmlspecialchars($gcal) ?>" target="_blank" rel="noopener" style="background:#198754; color:#fff; padding:6px 10px; border-radius:4px; text-decoration:none;">Google Calendar</a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!in_array(strtolower($row['status']), ['pending','approved'], true) && !(in_array(strtolower($row['status']), ['approved','completed'], true) && !empty($row['appointment_date']))): ?>
                                —
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>