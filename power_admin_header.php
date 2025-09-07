<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Power Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo">NEUST Gabaldon</div>
            <ul class="nav-links">
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="power_admin_announcement.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
                <li><a href="power_admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="power_admin_manage_grievances.php"><i class="fas fa-exclamation-triangle"></i> Grievances</a></li>
               
                <li><a href="admin_list.php"><i class="fas fa-user-shield"></i> Manage Admin</a></li>
                <li><a href="login.php">Logout</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Content to be included in other pages goes here -->
        </main>
    </div>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
}
.dashboard-container {
    display: flex;
}
.sidebar {
    width: 250px;
    background-color: #002147;
    color: white;
    height: 100vh;
    padding: 20px;
}
.logo {
    font-size: 22px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
}
.nav-links {
    list-style: none;
    padding: 0;
}
.nav-links li {
    margin: 15px 0;
}
.nav-links a {
    text-decoration: none;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    transition: 0.3s;
}
.nav-links a:hover, .nav-links .active {
    background-color: #FFD700;
    color: #002147;
    border-radius: 5px;
}
.main-content {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
}
header {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
}
.stats {
    display: flex;
    gap: 15px;
}
.stat-card {
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    flex: 1;
    text-align: center;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
}
.stat-card i {
    font-size: 30px;
    margin-bottom: 10px;
}
</style>
</body>
</html>