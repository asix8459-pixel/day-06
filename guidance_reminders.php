<?php
// Cron-safe reminders script. Run every 15 minutes.
require 'config.php';
require_once 'mailer.php';

// Create table for logs if not exists (lightweight)
$conn->query("CREATE TABLE IF NOT EXISTS reminder_logs (id INT AUTO_INCREMENT PRIMARY KEY, appointment_id INT NOT NULL, kind VARCHAR(16) NOT NULL, sent_at DATETIME NOT NULL, UNIQUE KEY uniq_appt_kind(appointment_id, kind)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$now = new DateTime('now');
$in15 = (clone $now)->modify('+15 minutes');

// Fetch approved appointments happening in ~24h or ~1h
$sql = "SELECT a.id, a.appointment_date, a.student_id, a.user_id, s.email AS s_email, TRIM(CONCAT(s.first_name,' ',s.last_name)) AS s_name, c.email AS c_email, TRIM(CONCAT(c.first_name,' ',c.last_name)) AS c_name
        FROM appointments a
        JOIN users s ON a.student_id=s.user_id
        JOIN users c ON a.user_id=c.user_id
        WHERE a.status IN ('approved','Approved') AND a.appointment_date BETWEEN ? AND ?";
$from = $now->format('Y-m-d H:i:00');
$to = $in15->format('Y-m-d H:i:00');
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$rs = $stmt->get_result();

while ($row = $rs->fetch_assoc()) {
  $apptAt = new DateTime($row['appointment_date']);
  $diff = $apptAt->getTimestamp() - $now->getTimestamp();
  $kind = null;
  if ($diff >= 23*3600 && $diff <= 25*3600) { $kind = '24h'; }
  elseif ($diff >= 0 && $diff <= 90*60) { $kind = '1h'; }
  if (!$kind) { continue; }

  // Dedup check
  $chk = $conn->prepare('SELECT 1 FROM reminder_logs WHERE appointment_id=? AND kind=?');
  $chk->bind_param('is', $row['id'], $kind);
  $chk->execute(); $has = $chk->get_result()->fetch_row();
  if ($has) { continue; }

  $when = $apptAt->format('Y-m-d H:i');
  $sEmail = $row['s_email'];
  if ($sEmail) {
    $html = '<p>Hello '.htmlspecialchars($row['s_name']).',</p><p>This is a reminder for your guidance appointment on <strong>'.htmlspecialchars($when).'</strong>.</p>';
    @send_email($sEmail, 'Appointment Reminder', $html, strip_tags($html));
  }
  $cEmail = $row['c_email'];
  if ($cEmail) {
    $html = '<p>Hello '.htmlspecialchars($row['c_name']).',</p><p>Reminder: you have a guidance appointment scheduled with a student on <strong>'.htmlspecialchars($when).'</strong>.</p>';
    @send_email($cEmail, 'Appointment Reminder', $html, strip_tags($html));
  }

  $ins = $conn->prepare('INSERT IGNORE INTO reminder_logs (appointment_id, kind, sent_at) VALUES (?, ?, NOW())');
  $ins->bind_param('is', $row['id'], $kind);
  $ins->execute();
}

echo 'OK';
?>

