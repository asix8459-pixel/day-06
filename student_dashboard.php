<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}
// Database Connection
$host = "localhost"; 
$user = "root";
$password = "";
$dbname = "student_services_db";
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Get the user_id from session
$user_id = $_SESSION['user_id'];
// Fetch User Information
$query = "SELECT user_id, first_name, last_name FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();
if (!$student) {
    die("Error: Student record not found.");
}
// Function to count records
function getCount($conn, $table, $student_id, $request_type = null) {
    if ($request_type) {
        $query = "SELECT COUNT(*) AS total FROM requests WHERE student_id = ? AND request_type = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $student_id, $request_type);
    } else {
        $query = "SELECT COUNT(*) AS total FROM $table WHERE student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $student_id);
    }
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'] ?? 0;
    } else {
        die("Error executing query.");
    }
}
// Get statistics
$appointments = getCount($conn, "appointments", $user_id);
$requests = getCount($conn, "requests", $user_id);
$scholarships = getCount($conn, "requests", $user_id, "Scholarship");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Student Dashboard</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="assets/student_theme.css">
</head>
<body>
	<?php include('student_header.php'); ?>
	<div class="container">
		<div class="page-header">
			<h1><i class="fas fa-user-graduate"></i> Welcome, <?php echo htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?>!</h1>
			<p>User ID: <?php echo htmlspecialchars($student['user_id']); ?></p>
		</div>
		<section class="dashboard-grid">
			<a href="student_status_appointments.php" class="stat-card">
				<i class="fas fa-calendar-check"></i>
				<p>Appointments</p>
				<span><?php echo htmlspecialchars(intval($appointments)); ?></span>
			</a>
			<a href="guidance_request.php" class="stat-card">
				<i class="fas fa-headset"></i>
				<p>Request Guidance</p>
				<span>New</span>
			</a>
			<a href="track_applications.php" class="stat-card">
				<i class="fas fa-graduation-cap"></i>
				<p>Scholarships</p>
				<span><?php echo htmlspecialchars(intval($scholarships)); ?></span>
			</a>
		</section>
	</div>
</body>
</html>