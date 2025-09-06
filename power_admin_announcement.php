<?php

$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $title = !empty($_POST['title']) ? $_POST['title'] : "No Title";
        $targetDir = "uploads/announcements/";
        $fileName = basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowedTypes = array("jpg", "jpeg", "png", "gif");
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $sql = "INSERT INTO announcements (title, image) VALUES ('$title', '$fileName')";
                if ($conn->query($sql)) {
                    $message = "Announcement added successfully!";
                } else {
                    $message = "Error saving to database!";
                }
            } else {
                $message = "Error uploading file!";
            }
        } else {
            $message = "Invalid file type! Please upload JPG, JPEG, PNG, or GIF.";
        }
    }

    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $query = "SELECT image FROM announcements WHERE id = $id";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        
        if ($row) {
            unlink("uploads/announcements/" . $row['image']);
            $sql = "DELETE FROM announcements WHERE id = $id";
            if ($conn->query($sql)) {
                $message = "Announcement deleted successfully!";
            } else {
                $message = "Error deleting announcement!";
            }
        }
    }

    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $title = !empty($_POST['title']) ? $_POST['title'] : "No Title";
        $targetDir = "uploads/announcements/";
        $fileName = basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        if (!empty($fileName)) {
            $allowedTypes = array("jpg", "jpeg", "png", "gif");
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    $query = "SELECT image FROM announcements WHERE id = $id";
                    $result = $conn->query($query);
                    $row = $result->fetch_assoc();
                    if ($row) {
                        unlink("uploads/announcements/" . $row['image']);
                    }
                    $sql = "UPDATE announcements SET title='$title', image='$fileName' WHERE id=$id";
                    if ($conn->query($sql)) {
                        $message = "Announcement updated successfully!";
                    } else {
                        $message = "Error updating announcement!";
                    }
                } else {
                    $message = "Error uploading file!";
                }
            } else {
                $message = "Invalid file type! Please upload JPG, JPEG, PNG, or GIF.";
            }
        } else {
            $sql = "UPDATE announcements SET title='$title' WHERE id=$id";
            if ($conn->query($sql)) {
                $message = "Announcement updated successfully!";
            } else {
                $message = "Error updating announcement!";
            }
        }
    }
}

$sql = "SELECT * FROM announcements ORDER BY date_posted DESC";
$result = $conn->query($sql);

function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Power Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        .btn {
            padding: 10px;
            border: none;
            cursor: pointer;
            color: white;
            border-radius: 5px;
            margin: 5px;
        }
        .btn-add { background: blue; }
        .btn-delete { background: red; }
        .btn-edit { background: gold; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo">NEUST Gabaldon</div>
            <ul class="nav-links">
                <li><a href="admin_dashboard.php" class="<?= isActive('admin_dashboard.php') ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="power_admin_announcement.php" class="<?= isActive('power_admin_announcement.php') ?>"><i class="fas fa-bullhorn"></i> Announcements</a></li>
                <li><a href="power_admin_users.php" class="<?= isActive('user_management.php') ?>"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="#" class="<?= isActive('grievance_reports.php') ?>"><i class="fas fa-exclamation-triangle"></i> Grievances</a></li>
                <li>
                <a href="#" class="active"><i class="fas fa-user-shield"></i> Manage Admin <i class="fas fa-caret-down"></i></a>
          <ul style="list-style: none; padding-left: 20px; display: none;">
            <li><a href="admin_list.php"><i class="fas fa-list"></i> Admin List</a></li>
            <li><a href="add_admin.php"><i class="fas fa-user-plus"></i> Add Admin</a></li>
          </ul>
        </li>
        <script>
          // JavaScript to toggle dropdown visibility
          document.querySelectorAll('.sidebar .nav-links li > a').forEach(link => {
            link.addEventListener('click', function(e) {
              const dropdown = this.nextElementSibling;
              if (dropdown && dropdown.tagName === 'UL') {
          e.preventDefault();
          dropdown.style.display = dropdown.style.display === 'none' || dropdown.style.display === '' ? 'block' : 'none';
              }
            });
          });
        </script>
                <li><a href="scholarships.php" class="<?= isActive('scholarships.php') ?>"><i class="fas fa-graduation-cap"></i> Scholarships</a></li>
                <li><a href="login.php" class="<?= isActive('logout.php') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <h2>Manage Announcements</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <label>Title:</label>
                <input type="text" name="title">
                <label>Image:</label>
                <input type="file" name="image" required>
                <button type="submit" name="add" class="btn btn-add">Add Announcement</button>
            </form>

            <h3>Existing Announcements</h3>
            <table>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><img src="uploads/announcements/<?= $row['image'] ?>" width="100"></td>
                    <td><?= $row['title'] ?></td>
                    <td>
                        <button class="btn btn-edit" onclick="openEditModal('<?= $row['id'] ?>', '<?= $row['title'] ?>', '<?= $row['image'] ?>')">Edit</button>
                        <button class="btn btn-delete" onclick="openDeleteModal('<?= $row['id'] ?>')">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </main>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Edit Announcement</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="editId" name="id">
                <label>Title:</label>
                <input type="text" id="editTitle" name="title">
                <label>Image:</label>
                <input type="file" name="image">
                <img id="previewImage" src="" width="100">
                <br>
                <button type="submit" name="update" class="btn btn-edit">Update</button>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this announcement?</p>
            <form action="" method="POST">
                <input type="hidden" id="deleteId" name="id">
                <button type="submit" name="delete" class="btn btn-delete">Delete</button>
                <button type="button" class="btn" style="background: blue;" onclick="closeDeleteModal()">Cancel</button>
            </form>
        </div>
    </div>

    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMessageModal()">&times;</span>
            <p id="messageText"></p>
        </div>
    </div>

    <script>
        function openEditModal(id, title, image) {
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('previewImage').src = 'uploads/announcements/' + image;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openDeleteModal(id) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
            if (event.target == document.getElementById('deleteModal')) {
                closeDeleteModal();
            }
            if (event.target == document.getElementById('messageModal')) {
                closeMessageModal();
            }
        }

        <?php if (!empty($message)): ?>
        document.getElementById('messageText').innerText = "<?= $message ?>";
        document.getElementById('messageModal').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>
