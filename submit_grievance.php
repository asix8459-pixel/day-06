<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validate
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "❌ Invalid request. Please refresh and try again.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || strlen($title) < 5 || $description === '' || strlen($description) < 20) {
            $message = "⚠️ Please provide a valid title (min 5 chars) and description (min 20 chars).";
        } else {
            $stmt = $conn->prepare("INSERT INTO grievances (user_id, title, description, submission_date, status) VALUES (?, ?, ?, NOW(), 'pending')");
            $stmt->bind_param("sss", $user_id, $title, $description);

            if ($stmt->execute()) {
                $message = "✅ Grievance submitted successfully.";
            } else {
                $message = "❌ Error submitting grievance: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Grievance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container {
            max-width: 900px; margin: 50px auto; padding: 20px;
            background-color: white; border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; color: #003366; font-weight: 700;}
        .form-label { font-weight: bold; }
        .form-control {
            width: 100%; padding: 10px; margin: 10px 0 20px;
            border: 1px solid #ccc; border-radius: 5px; font-size: 16px;
        }
        .btn-primary {
            background-color: #003366; color: white; padding: 10px 20px;
            border: none; border-radius: 5px; cursor: pointer;
            transition: background-color 0.3s;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: gold; color: #003366;
        }
        .alert {
            padding: 15px;
            background-color: #33cc99;
            color: #003366;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align:center;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }
        .alert.error { background-color: #d9534f; color: white; }
    </style>
</head>
<body>

<?php include 'student_header.php'; ?>

<div class="container">
    <h2 class="text-center mt-4"><i class="fa-solid fa-file-circle-exclamation"></i> Submit Grievance</h2>
    <?php if (isset($message)): ?>
        <div class="alert<?= (strpos($message,'Error')!==false || strpos($message,'Invalid')!==false)?' error':'' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="submit_grievance.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required minlength="5" maxlength="120" placeholder="Brief title">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5" required minlength="20" placeholder="Describe your grievance..."></textarea>
        </div>
        <button type="submit" class="btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit</button>
    </form>
</div>

</body>
</html>