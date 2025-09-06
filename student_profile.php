<?php
session_start();
require_once 'config.php'; // Database connection
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}
// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $current_address = $_POST['current_address'];
    $permanent_address = $_POST['permanent_address'];
    $birth_date = $_POST['birth_date'];
    $nationality = $_POST['nationality'];
    $religion = $_POST['religion'];
    $biological_sex = $_POST['biological_sex'];
    $mother_name = $_POST['mother_name'];
    $mother_work = $_POST['mother_work'];
    $mother_contact = $_POST['mother_contact'];
    $father_name = $_POST['father_name'];
    $father_work = $_POST['father_work'];
    $father_contact = $_POST['father_contact'];
    $siblings_count = $_POST['siblings_count'];

    // Update query
    $update_query = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, current_address = ?, permanent_address = ?, birth_date = ?, nationality = ?, religion = ?, biological_sex = ?, mother_name = ?, mother_work = ?, mother_contact = ?, father_name = ?, father_work = ?, father_contact = ?, siblings_count = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssssssssssssssssi", $first_name, $middle_name, $last_name, $email, $phone, $current_address, $permanent_address, $birth_date, $nationality, $religion, $biological_sex, $mother_name, $mother_work, $mother_contact, $father_name, $father_work, $father_contact, $siblings_count, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
    }

    $stmt->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <style>
        .profile-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select {
            width: 100%; padding: 10px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px;
        }
        button {
            width: 100%; padding: 10px; background-color: #4CAF50; color: white; font-size: 18px;
            border: none; border-radius: 5px; cursor: pointer;
        }
        button:hover { background-color: #45a049; }
    </style>
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/student_theme.css">
</head>
<body>
<?php include('student_header.php'); ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-user-circle"></i> My Profile</h1>
        <p>Update your personal information and profile picture</p>
    </div>

<div class="profile-container">
    <h2>Update Profile</h2>
    <form method="POST" action="update_profile.php">
        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required />
        </div>
        <div class="form-group">
            <label for="middle_name">Middle Name:</label>
            <input type="text" id="middle_name" name="middle_name" value="<?= htmlspecialchars($user['middle_name']) ?>" required />
        </div>
        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required />
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required />
        </div>
        <div class="form-group">
            <label for="biological_sex">Biological Sex:</label>
            <select id="biological_sex" name="biological_sex" required>
                <option value="Male" <?= ($user['biological_sex'] == 'Male') ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= ($user['biological_sex'] == 'Female') ? 'selected' : '' ?>>Female</option>
            </select>
        </div>
        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required />
        </div>
        <div class="form-group">
            <label for="current_address">Current Address:</label>
            <input type="text" id="current_address" name="current_address" value="<?= htmlspecialchars($user['current_address']) ?>" required />
        </div>
        <div class="form-group">
            <label for="permanent_address">Permanent Address:</label>
            <input type="text" id="permanent_address" name="permanent_address" value="<?= htmlspecialchars($user['permanent_address']) ?>" required />
        </div>
        <div class="form-group">
            <label for="birth_date">Birthdate:</label>
            <input type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($user['birth_date']) ?>" required />
        </div>
        <div class="form-group">
            <label for="nationality">Nationality:</label>
            <input type="text" id="nationality" name="nationality" value="<?= htmlspecialchars($user['nationality']) ?>" required />
        </div>
        <div class="form-group">
            <label for="religion">Religion:</label>
            <input type="text" id="religion" name="religion" value="<?= htmlspecialchars($user['religion']) ?>" required />
        </div>
        <div class="form-group">
            <label for="siblings_count">Number of Siblings:</label>
            <input type="number" id="siblings_count" name="siblings_count" value="<?= htmlspecialchars($user['siblings_count']) ?>" required />
        </div>
        <button type="submit">Update Profile</button>
    </form>
    <div class="card" style="padding:20px;">
        <form method="POST" action="update_profile.php" enctype="multipart/form-data">
            <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">
                <div style="flex:0 0 140px; text-align:center;">
                    <?php
                    $avatarPath = $user['avatar_path'] ?? ($user['profile_picture'] ?? null);
                    $avatarUrl = ($avatarPath && is_string($avatarPath)) ? htmlspecialchars($avatarPath) : null;
                    ?>
                    <div style="width:120px; height:120px; border-radius:50%; overflow:hidden; box-shadow:var(--shadow-1); background:#eaeaea; display:inline-flex; align-items:center; justify-content:center;">
                        <?php if ($avatarUrl): ?>
                            <img src="<?= $avatarUrl ?>" alt="Profile Picture" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-user" style="font-size:48px; color:#94a3b8;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3">
                        <label class="btn-accent" style="display:inline-block; cursor:pointer; padding:10px 14px;">
                            <i class="fas fa-camera"></i> Upload Photo
                            <input type="file" name="avatar" accept="image/*" style="display:none;">
                        </label>
                        <div style="font-size:12px; color:var(--text-muted); margin-top:6px;">PNG/JPG up to 2MB</div>
                    </div>
                </div>
                <div style="flex:1 1 420px;">
                    <div style="display:grid; grid-template-columns: repeat(2, minmax(200px, 1fr)); gap:14px;">
                        <div>
                            <label>First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Biological Sex</label>
                            <select name="biological_sex" required>
                                <option value="Male" <?= (($user['biological_sex'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= (($user['biological_sex'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div>
                            <label>Phone</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Current Address</label>
                            <input type="text" name="current_address" value="<?= htmlspecialchars($user['current_address'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Permanent Address</label>
                            <input type="text" name="permanent_address" value="<?= htmlspecialchars($user['permanent_address'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Birthdate</label>
                            <input type="date" name="birth_date" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Nationality</label>
                            <input type="text" name="nationality" value="<?= htmlspecialchars($user['nationality'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Religion</label>
                            <input type="text" name="religion" value="<?= htmlspecialchars($user['religion'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Mother's Name</label>
                            <input type="text" name="mother_name" value="<?= htmlspecialchars($user['mother_name'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Mother's Work</label>
                            <input type="text" name="mother_work" value="<?= htmlspecialchars($user['mother_work'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Mother's Contact</label>
                            <input type="text" name="mother_contact" value="<?= htmlspecialchars($user['mother_contact'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Father's Name</label>
                            <input type="text" name="father_name" value="<?= htmlspecialchars($user['father_name'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Father's Work</label>
                            <input type="text" name="father_work" value="<?= htmlspecialchars($user['father_work'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Father's Contact</label>
                            <input type="text" name="father_contact" value="<?= htmlspecialchars($user['father_contact'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Number of Siblings</label>
                            <input type="number" name="siblings_count" value="<?= htmlspecialchars($user['siblings_count'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mt-4" style="display:flex; gap:10px;">
                        <button type="submit" class="btn-primary-modern"><i class="fas fa-save"></i> Save Changes</button>
                        <a href="student_dashboard.php" class="btn-primary-modern" style="background:var(--neust-blue); text-decoration:none; display:inline-flex; align-items:center; gap:8px;"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>