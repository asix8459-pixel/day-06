<?php 
session_start(); // Start the session
require_once __DIR__ . '/csrf.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_services_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMessage = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $errorMessage = "Invalid request. Please refresh and try again.";
    } else {
    $userId = trim($_POST['user_id']);
    $password = $_POST['password'];
    $rememberMe = isset($_POST['remember_me']);

    // SQL query to fetch user information
    $stmt = $conn->prepare("SELECT password_hash, role FROM users WHERE user_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($passwordHash, $role);
        $stmt->fetch();

        if (password_verify($password, $passwordHash)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $role;

            if ($rememberMe) {
                setcookie("user_id", $userId, time() + (86400 * 30), "/"); // Save user for 30 days
            }

            // Redirect based on user role
            switch ($role) {
                case 'Power Admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'Student':
                    header("Location: student_dashboard.php");
                    break;
                case 'Faculty':
                    header("Location: faculty_dashboard.php");
                    break;
                case 'Scholarship Admin':
                    header("Location: scholarship_admin_dashboard.php");
                    break;
                case 'Guidance Admin':
                    header("Location: guidance_admin_dashboard.php");
                    break;
                case 'Dormitory Admin':
                    header("Location: admin_dormitory_dashboard.php");
                    break;
                case 'Registrar Admin':
                    header("Location: registrar_dashboard.php");
                    break;
                default:
                    $errorMessage = "Unauthorized role.";
                    session_destroy();
                    exit();
            }
            exit();
        } else {
            $errorMessage = "Invalid password.";
        }
    } else {
        $errorMessage = "User ID does not exist.";
    }
    $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #0077b6;
            --secondary-color: #00b4d8;
            --accent-color: #90e0ef;
            --background-color: #f8f9fa;
            --text-color: #1a1a1a;
            --light-text-color: #ffffff;
            --error-color: #e63946;
            --glass-bg: rgba(255,255,255,.78);
            --glass-border: rgba(255,255,255,.45);
            --shadow: 0 20px 60px rgba(0,0,0,.25);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #001934 0%, #012a52 40%, #023b70 100%);
            overflow: hidden;
            color: var(--text-color);
        }

        /* Animated background */
        .bg-anim { position: absolute; inset: -20% -10% -10% -10%; background: radial-gradient(600px 300px at 10% 10%, rgba(14,165,233,.25), transparent 60%), radial-gradient(600px 300px at 90% 80%, rgba(34,197,94,.22), transparent 60%), radial-gradient(500px 250px at 40% 20%, rgba(245,158,11,.18), transparent 60%); filter: blur(12px); animation: hue 16s linear infinite; }
        @keyframes hue { 0%{ filter: blur(12px) hue-rotate(0deg);} 100%{ filter: blur(12px) hue-rotate(360deg);} }

        .container {
            width: 400px;
            background: var(--glass-bg);
            padding: 40px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.3s;
            transform: translateY(10px);
            opacity: 0;
            animation: fadeSlide .45s ease forwards;
        }

        @keyframes fadeSlide { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .container:hover {
            transform: scale(1.02);
        }

        .icon { font-size: 60px; color: var(--primary-color); margin-bottom: 12px; }

        /* Floating labels */
        .fl-group { position: relative; margin: 12px 0; }
        .fl-group input { width: 100%; padding: 14px 12px; border: 1px solid #cfd8dc; border-radius: 10px; font-size: 16px; background: rgba(255,255,255,.9); transition: border .2s, box-shadow .2s; }
        .fl-group input:focus { border-color: var(--primary-color); outline: 0; box-shadow: 0 0 0 4px rgba(0,119,182,.12); }
        .fl-group label { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; background: transparent; padding: 0 6px; transition: .18s ease; pointer-events: none; }
        .fl-group input::placeholder { color: transparent; }
        .fl-group input:focus + label,
        .fl-group input:not(:placeholder-shown) + label { top: -8px; transform: none; font-size: 12px; background: var(--glass-bg); border-radius: 6px; }

        .hint { text-align: left; font-size: 12px; color: #6b7280; margin-top: -6px; }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .toast-error { background: #fee2e2; color: #7f1d1d; border: 1px solid #fecaca; padding: 10px 12px; border-radius: 10px; margin: 8px 0 4px; text-align: left; animation: pop .2s ease; }
        @keyframes pop { from { transform: scale(.98); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .remember-me {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }

        .remember-me input {
            width: auto;
            margin-right: 10px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: var(--light-text-color);
            font-size: 16px;
            cursor: pointer;
            border-radius: 8px;
            transition: transform .15s ease, filter .2s ease;
        }

        button:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        .spinner {
            width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.6); border-top-color: #fff; border-radius: 50%; display: inline-block; margin-right: 8px; animation: spin .8s linear infinite; vertical-align: -2px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="bg-anim" aria-hidden="true"></div>
    <div class="container">
        <i class="fas fa-user-circle icon"></i>
        <h2>Login</h2>
        <?php if (!empty($errorMessage)) { ?>
            <div class="toast-error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php } ?>
        <form method="POST" action="" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
            <div class="fl-group">
                <input type="text" name="user_id" id="user_id" placeholder=" " value="<?php echo isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : ''; ?>" required>
                <label for="user_id">User ID</label>
            </div>
            <div class="fl-group" style="position:relative;">
                <input type="password" id="password" name="password" placeholder=" " required>
                <label for="password">Password</label>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <div class="remember-me">
                <input type="checkbox" name="remember_me" id="remember_me">
                <label for="remember_me">Remember Me</label>
            </div>
            <button type="submit" id="loginBtn">Login</button>
        </form>
        <div style="margin-top:10px; display:flex; justify-content: space-between; align-items:center;">
            <a href="register.php">Create account</a>
            <a href="reset_password.php">Forgot password?</a>
        </div>
    </div>
    <script>
        // Loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function(){
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span>Signing in...';
        });
        document.getElementById("togglePassword").addEventListener("click", function () {
            let passwordInput = document.getElementById("password");
            this.classList.toggle("fa-eye-slash");
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        });
    </script>
</body>
</html>