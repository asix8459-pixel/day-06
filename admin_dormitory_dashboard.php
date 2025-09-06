<?php
session_start();
include 'config.php';

// Fetch counts for dashboard stats
$pendingCount = $conn->query("SELECT COUNT(*) AS total FROM student_room_applications WHERE `status` = 'Pending'")->fetch_assoc()['total'];
$approvedCount = $conn->query("SELECT COUNT(*) AS total FROM student_room_applications WHERE `status` = 'Approved'")->fetch_assoc()['total'];
$rejectedCount = $conn->query("SELECT COUNT(*) AS total FROM student_room_applications WHERE `status` = 'Rejected'")->fetch_assoc()['total'];

// Calculate occupied and available beds
$occupiedBeds = $conn->query("SELECT SUM(occupied_beds) AS total FROM rooms")->fetch_assoc()['total'];
$totalBeds = $conn->query("SELECT SUM(total_beds) AS total FROM rooms")->fetch_assoc()['total'];
$availableBeds = $totalBeds - $occupiedBeds;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dormitory Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            display: flex;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: #003366;
            color: white;
            position: fixed;
            padding-top: 20px;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: #002855;
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #FFD700;
        }

        .sidebar-header i {
            margin-right: 10px;
            color: #FFD700;
        }

        .sidebar-menu a {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px 20px;
            font-size: 1.1rem;
            margin: 8px 15px;
            background-color: #004080;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .sidebar-menu a:hover {
            background-color: #FFD700;
            color: #003366;
            transform: scale(1.05);
            font-weight: bold;
        }

        .logout-btn {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px;
            font-size: 1.1rem;
            background-color: #d9534f;
            margin: 30px 15px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c9302c;
            transform: scale(1.05);
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
            min-height: 100vh;
            background: white;  
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 10px;
            color: white;
            padding: 20px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .bg-pending { background: #f1c40f; }
        .bg-approved { background: #2ecc71; }
        .bg-rejected { background: #e74c3c; }
        .bg-occupied { background: #9b59b6; }
        .bg-available { background: #1abc9c; }
    </style>
    <script>
        function navigateTo(page) {
            window.location.href = page;
        }

        function logout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = "login.php";
            }
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa fa-building"></i>
            <span>Dormitory Management</span>
        </div>
        <div class="sidebar-menu">
            <a href="admin_dormitory_dashboard.php" class="active" style="background-color: #FFD700; color: #003366; font-weight: bold;"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="room_assignments.php"><i class="fas fa-bed"></i> Room Allocation</a>
            <a href="dormitory_manage_applications.php"><i class="fas fa-file-alt"></i> Room Applications</a>
            <a href="dormitory_room_management.php"><i class="fas fa-users"></i> View Boarders</a>
            <a href="admin_manage_dorm_agreements.php"><i class="fas fa-file-signature"></i> Agreements</a>
            <a href="#"><i class="fas fa-bullhorn"></i> Announcements</a>
        </div>
        <a href="#" class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h2 class="text-center text-primary">Dormitory Admin Dashboard</h2>
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-pending" onclick="navigateTo('dormitory_manage_applications.php')">
                        <h4>Pending Applications</h4>
                        <p class="fs-3"><i class="fa-solid fa-hourglass-half"></i> <?= $pendingCount ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-approved" onclick="navigateTo('approved_applications.php')">
                        <h4>Approved Applications</h4>
                        <p class="fs-3"><i class="fa-solid fa-check"></i> <?= $approvedCount ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-rejected" onclick="navigateTo('rejected_applications.php')">
                        <h4>Rejected Applications</h4>
                        <p class="fs-3"><i class="fa-solid fa-times"></i> <?= $rejectedCount ?></p>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-occupied" onclick="navigateTo('occupied_rooms.php')">
                        <h4>Occupied Beds</h4>
                        <p class="fs-3"><i class="fa-solid fa-bed"></i> <?= $occupiedBeds ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-available" onclick="navigateTo('available_rooms.php')">
                        <h4>Available Beds</h4>
                        <p class="fs-3"><i class="fa-solid fa-door-open"></i> <?= $availableBeds ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
