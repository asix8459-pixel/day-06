<?php
include 'config.php';
session_start();

// Check if the user is logged in and is a guidance admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guidance Admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guidance Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .main-content {
            margin-left: 280px; /* Adjust based on the width of the sidebar */
            padding: 20px;
        }

        .main-content h2 {
            margin-bottom: 20px;
            color: #333333;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h3 {
            margin-bottom: 10px;
            color: #007bff;
        }

        .card p {
            color: #333333;
        }

        .card a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'guidance_admin_header.php'; ?>

    <div class="main-content">
        <div class="container">
            <!-- Overview Section -->
            <div class="card">
                <h3>Overview</h3>
                <p>Welcome to the Guidance Admin Dashboard. Here you can manage guidance requests, post announcements, generate reports, and adjust settings.</p>
            </div>

            <!-- Guidance Requests Section -->
            <div class="card">
                <h3>Guidance Requests</h3>
                <p>Manage and view all guidance requests from students.</p>
                <a href="guidance_list_admin.php">View Details</a>
            </div>

            <!-- Announcements Section -->
            <div class="card">
                <h3>Announcements</h3>
                <p>View announcements.</p>
                <a href="announcements_slideshow.php">View Details</a>
            </div>

            <!-- Reports Section -->
            <div class="card">
                <h3>Reports</h3>
                <p>Generate and view various reports related to guidance activities.</p>
                <a href="generate_reports.php">View Details</a>
            </div>

          
        </div>
    </div>
</body>
</html>