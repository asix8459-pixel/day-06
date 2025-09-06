<?php 
$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db"; 
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch the latest 12 announcements from the Power Admin
$sql = "SELECT id, title, image FROM announcements ORDER BY date_posted DESC LIMIT 12";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Student Dashboard</title>
    <!-- Slick Carousel CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/student_theme.css">

    <style>
        .slideshow-container {
            max-width: 85%;
            margin: 20px auto;
            position: relative;
        }
        .slide {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }
        .slide img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }
        .caption {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 12px;
            width: 80%;
            text-align: center;
            font-size: 18px;
            border-radius: 5px;
        }
        .slide:hover { transform: scale(1.02); }
        .slick-prev, .slick-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.7);
            color: black;
            border: none;
            font-size: 22px;
            padding: 10px;
            cursor: pointer;
            z-index: 10;
            border-radius: 50%;
        }
        .slick-prev { left: -50px; }
        .slick-next { right: -50px; }
        .slick-prev:hover, .slick-next:hover { background: white; color: black; }
        .slick-dots { bottom: 15px; }
        .slick-dots li button:before { color: white; font-size: 15px; }
        .slick-dots li.slick-active button:before { color: yellow; }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #003366;
            color: white;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            box-shadow: 0px -4px 6px rgba(0, 0, 0, 0.1);
        }
        .footer p { margin: 0; font-size: 14px; }
        .footer a { color: white; text-decoration: none; font-weight: bold; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <?php include('student_header.php'); ?>
    <div class="slideshow-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="slide">
                <a href="announcement_details.php?id=<?= htmlspecialchars($row['id']) ?>">
                    <img src="uploads/announcements/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                    <div class="caption"><?= htmlspecialchars($row['title']) ?></div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="footer">
        <p>&copy; 2025 NEUST Gabaldon. All Rights Reserved.</p>
    </div>
    <!-- jQuery & Slick Carousel Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.slideshow-container').slick({
                dots: true,
                infinite: true,
                speed: 600,
                fade: false,
                autoplay: true,
                autoplaySpeed: 3000,
                arrows: true,
                prevArrow: '<button class="slick-prev">&#10094;</button>',
                nextArrow: '<button class="slick-next">&#10095;</button>'
            });
        });
    </script>
</body>
</html>