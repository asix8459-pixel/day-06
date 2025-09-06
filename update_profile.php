<?php
session_start();
require_once "config.php"; // Database connection file
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Check if user is logged in
	if (!isset($_SESSION['user_id'])) {
		echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
		exit;
	}
	// Get user ID from session
	$user_id = $_SESSION['user_id'];
	// Get form data and sanitize
	$first_name = htmlspecialchars(strip_tags($_POST['first_name']));
	$middle_name = isset($_POST['middle_name']) ? htmlspecialchars(strip_tags($_POST['middle_name'])) : NULL; // ✅ Middle Name (nullable)
	$last_name = htmlspecialchars(strip_tags($_POST['last_name']));
	$birth_date = htmlspecialchars(strip_tags($_POST['birth_date']));
	$nationality = htmlspecialchars(strip_tags($_POST['nationality']));
	$religion = htmlspecialchars(strip_tags($_POST['religion']));
	$biological_sex = htmlspecialchars(strip_tags($_POST['biological_sex']));
	$email = htmlspecialchars(strip_tags($_POST['email']));
	$phone = htmlspecialchars(strip_tags($_POST['phone']));
	$current_address = htmlspecialchars(strip_tags($_POST['current_address']));
	$permanent_address = htmlspecialchars(strip_tags($_POST['permanent_address']));
	$mother_name = htmlspecialchars(strip_tags($_POST['mother_name'] ?? ''));
	$mother_work = htmlspecialchars(strip_tags($_POST['mother_work'] ?? ''));
	$mother_contact = htmlspecialchars(strip_tags($_POST['mother_contact'] ?? ''));
	$father_name = htmlspecialchars(strip_tags($_POST['father_name'] ?? ''));
	$father_work = htmlspecialchars(strip_tags($_POST['father_work'] ?? ''));
	$father_contact = htmlspecialchars(strip_tags($_POST['father_contact'] ?? ''));
	$siblings_count = intval($_POST['siblings_count'] ?? 0); // ✅ Integer type
	$unit = isset($_POST['unit']) ? htmlspecialchars(strip_tags($_POST['unit'])) : NULL; // ✅ Nullable field
	// Validate phone number (should be 11 digits)
	if (!preg_match("/^[0-9]{11}$/", $phone)) {
		echo json_encode(['status' => 'error', 'message' => 'Phone number should be 11 digits.']);
		exit;
	}
	// Ensure database connection is working
	if (!$conn) {
		die(json_encode(['status' => 'error', 'message' => 'Database connection issue.']));
	}
	// Optional avatar upload
	$avatar_rel_path = null;
	if (!empty($_FILES['avatar']['name']) && isset($_FILES['avatar']['tmp_name'])) {
		$err = $_FILES['avatar']['error'];
		if ($err === UPLOAD_ERR_OK) {
			$tmp = $_FILES['avatar']['tmp_name'];
			$size = (int)$_FILES['avatar']['size'];
			if ($size > 2 * 1024 * 1024) { // 2MB limit
				echo json_encode(['status' => 'error', 'message' => 'Avatar exceeds 2MB limit.']);
				exit;
			}
			$finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
			$mime = $finfo ? finfo_file($finfo, $tmp) : ($_FILES['avatar']['type'] ?? 'application/octet-stream');
			if ($finfo) finfo_close($finfo);
			$allowed = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
			if (!isset($allowed[$mime])) {
				echo json_encode(['status' => 'error', 'message' => 'Invalid avatar file type.']);
				exit;
			}
			$ext = $allowed[$mime];
			$dir = __DIR__ . '/uploads/avatars/';
			if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
			$filename = 'avatar_' . preg_replace('/[^A-Za-z0-9_-]/', '', $user_id) . '_' . time() . '.' . $ext;
			$dest = $dir . $filename;
			if (!move_uploaded_file($tmp, $dest)) {
				echo json_encode(['status' => 'error', 'message' => 'Failed to save avatar.']);
				exit;
			}
			$avatar_rel_path = 'uploads/avatars/' . $filename;
		} else if ($err !== UPLOAD_ERR_NO_FILE) {
			echo json_encode(['status' => 'error', 'message' => 'Avatar upload error.']);
			exit;
		}
	}
	// Determine avatar column if present
	$avatarColumn = null;
	$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar_path'");
	if ($colCheck && $colCheck->num_rows > 0) { $avatarColumn = 'avatar_path'; }
	if (!$avatarColumn) {
		$colCheck2 = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
		if ($colCheck2 && $colCheck2->num_rows > 0) { $avatarColumn = 'profile_picture'; }
	}
	// Build update with optional avatar column
	$baseSql = "UPDATE users SET 
		first_name = ?, middle_name = ?, last_name = ?, birth_date = ?, nationality = ?, religion = ?, 
		biological_sex = ?, email = ?, phone = ?, current_address = ?, permanent_address = ?, 
		mother_name = ?, mother_work = ?, mother_contact = ?, father_name = ?, father_work = ?, 
		father_contact = ?, siblings_count = ?, unit = ?";
	$params = [
		$first_name, $middle_name, $last_name, $birth_date, $nationality, $religion,
		$biological_sex, $email, $phone, $current_address, $permanent_address,
		$mother_name, $mother_work, $mother_contact, $father_name, $father_work,
		$father_contact, $siblings_count, $unit
	];
	// 17 string params before siblings_count, then 1 int (siblings), then 1 string (unit)
	$types = str_repeat('s', 17) . 'i' . 's';

	if ($avatar_rel_path && $avatarColumn) {
		$baseSql .= ", $avatarColumn = ?";
		$params[] = $avatar_rel_path;
		$types .= 's';
	}

	$baseSql .= " WHERE user_id = ?";
	$params[] = $user_id;
	$types .= 's';

	$stmt = $conn->prepare($baseSql);
	if (!$stmt) {
		echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . $conn->error]);
		exit;
	}

	// mysqli requires references for call_user_func_array
	// Build types dynamically to match params
	$types = '';
	foreach ($params as $p) {
		$types .= is_int($p) ? 'i' : 's';
	}
	$bindArgs = [];
	$bindArgs[] = $types;
	foreach ($params as $key => $value) { $bindArgs[] = &$params[$key]; }
	call_user_func_array([$stmt, 'bind_param'], $bindArgs);
	if ($stmt->execute()) {
		echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.' , 'avatar' => $avatar_rel_path]);
	} else {
		echo json_encode(['status' => 'error', 'message' => 'Failed to update profile.', 'error' => $stmt->error]);
	}
	// Close statement and connection
	$stmt->close();
	$conn->close();
}
?>