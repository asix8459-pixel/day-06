<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db"; 

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM announcements ORDER BY date_posted DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEUST Gabaldon Student Services</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Roboto', sans-serif; 
            margin: 0; 
            padding: 0; 
            background:rgb(247, 247, 244); 
           
        color: #333; 
            
        }
        .navbar { 
            background:rgb(2, 31, 61); 
         
            color: white; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
        }
        .navbar .logo { 
            font-size: 24px; 
            font-weight: bold; 
            color: gold; 
        }
        .navbar .nav-links { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
            display: flex; 
        }
        .navbar .nav-links li { 
            margin: 0 15px; 
        }
        .navbar .nav-links a { 
            color: white; 
            text-decoration: none; 
            font-size: 18px; 
            transition: color 0.3s; 
        }
        .navbar .nav-links a:hover { 
            color: #ffd700; 
        }
        
        .slideshow-container { 
            width: 80%; 
            margin: 50px auto; 
            position: relative; 
            border-radius: 10px; 
            overflow: hidden; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
        }
        .slide { 
            position: relative; 
        }
        .slide img { 
            width: 100%; 
            height: 400px; 
            object-fit: cover; 
        }
        .caption { 
            position: absolute; 
            bottom: 20px; 
            left: 50%; 
            transform: translateX(-50%); 
            background: rgba(0, 0, 0, 0.6); 
            color: white; 
            padding: 10px 20px; 
            border-radius: 5px; 
        }
        
        .slick-prev, .slick-next { 
            position: absolute; 
            top: 50%; 
            transform: translateY(-50%); 
            background: rgba(255, 255, 255, 0.7); 
            color: black; 
            border: none; 
            font-size: 25px; 
            padding: 10px 15px; 
            cursor: pointer; 
            z-index: 10; 
            border-radius: 50%; 
            transition: background 0.3s; 
        }
        .slick-prev { 
            left: -80px; 
        }
        .slick-next { 
            right: -40px; 
        }
        .slick-prev:hover, .slick-next:hover { 
            background: white; 
            color: black; 
        }
        .slick-dots { 
            bottom: 10px; 
        }
        .slick-dots li button:before { 
            color: #fff; 
            font-size: 20px; 
        }
        .slick-dots li.slick-active button:before { 
            color: #ffd700; 
        }
        
        .welcome { 
            text-align: center; 
            padding: 30px 10px; 
            background: #007bff; 
            color: white; 
        }
        .welcome h2 { 
            font-size: 35px; 
            margin-bottom: 20px; 
            font-weight: bold; 
        }
        
        .about, .services { 
            text-align: center; 
            padding: 100px 20px; 
        }
        .about { 
            background: #fff; 
        }
        .services { 
            background: #f4f4f4; 
        }
        .about h2, .services h2 { 
            margin-bottom: 20px; 
            font-size: 36px; 
            font-weight: bold; 
        }
        .about p, .services p { 
            max-width: 800px; 
            margin: 0 auto; 
            font-size: 18px; 
            line-height: 1.6; 
            text-align: justify; 
        }
        .services .service { 
            display: inline-block; 
            width: 45%; 
            margin: 20px 2.5%; 
            padding: 40px; 
            background: #fff; 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            transition: transform 0.3s, box-shadow 0.3s; 
        }
        .services .service:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); 
        }
        .services .service h3 { 
            margin-bottom: 15px; 
            font-size: 28px; 
            font-weight: bold; 
        }
        
        .footer { 
            background: rgb(2, 31, 61); 
            color: white; 
            text-align: center; 
            padding: 20px; 
            margin-top: 50px; 
            position: sticky; 
            bottom: 0; 
            width: 100%; 
        }
        .footer p { 
            margin: 0; 
            font-size: 18px; 
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <img src="assets/logo.png" alt="NEUST Gabaldon Student Services" style="height: 70px; margin-left: 10px;">
            <span style="color: white; font-size: 20px; margin-left: 10px;">NEUST Gabaldon Student Services</span>
        </div>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#" id="openLogin">Login</a></li>
            <li><a href="#" id="openRegister">Register</a></li>
        </ul>
    </div>
  

    <div class="slideshow-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="slide">
                <img src="uploads/announcements/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                <div class="caption"> <?= htmlspecialchars($row['title']) ?> </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="about" id="about">
        <h2>About Us</h2>
        <p>
            NEUST Gabaldon Student Services Management System is designed to optimize and enhance the management of various student services. 
            Our goal is to provide an efficient, user-friendly platform for students and faculty to access essential services such as announcements, 
            scholarships, grievances, and dormitory management.
        </p>
    </div>

    <div class="services" id="services">
        <h2>Our Services</h2>
        <div class="service">
            <h3>Announcements</h3>
            <p>Stay updated with the latest news and announcements from NEUST Gabaldon. Our platform ensures you never miss important updates.</p>
        </div>
        <div class="service">
            <h3>Scholarships</h3>
            <p>Apply for various scholarships offered by NEUST Gabaldon. Our platform provides an optimized application process to help you secure financial support.</p>
        </div>
        <div class="service">
            <h3>Grievances</h3>
            <p>Have any concerns or issues? Use our grievance service to report and resolve your problems efficiently and effectively.</p>
        </div>
        <div class="service">
            <h3>Dormitory Services</h3>
            <p>Manage your dormitory applications and stay updated with dormitory services offered by NEUST Gabaldon.</p>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 NEUST Gabaldon. All Rights Reserved.</p>
    </div>
    
    <!-- Overlay Modals -->
    <style>
        .overlay-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: none; align-items: center; justify-content: center; z-index: 1050; }
        .overlay-modal { width: 90%; max-width: 980px; height: 85vh; background: #fff; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,.35); overflow: hidden; position: relative; }
        .overlay-header { position: absolute; top: 0; left: 0; right: 0; height: 50px; background: rgb(2, 31, 61); color: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 14px; }
        .overlay-title { font-weight: 700; }
        .overlay-close { background: transparent; border: none; color: #fff; font-size: 22px; cursor: pointer; }
        .overlay-body { position: absolute; top: 50px; left: 0; right: 0; bottom: 0; }
        .overlay-body iframe { width: 100%; height: 100%; border: 0; }
        @media (max-width: 768px){ .overlay-modal{ width: 96%; height: 90vh; } }
    </style>
    <div class="overlay-backdrop" id="loginOverlay" aria-hidden="true">
        <div class="overlay-modal" role="dialog" aria-modal="true" aria-labelledby="loginOverlayTitle">
            <div class="overlay-header"><span id="loginOverlayTitle" class="overlay-title">Login</span><button class="overlay-close" data-close="loginOverlay" aria-label="Close">×</button></div>
            <div class="overlay-body"><iframe id="loginFrame" src="about:blank"></iframe></div>
        </div>
    </div>
    <div class="overlay-backdrop" id="registerOverlay" aria-hidden="true">
        <div class="overlay-modal" role="dialog" aria-modal="true" aria-labelledby="registerOverlayTitle">
            <div class="overlay-header"><span id="registerOverlayTitle" class="overlay-title">Register</span><button class="overlay-close" data-close="registerOverlay" aria-label="Close">×</button></div>
            <div class="overlay-body"><iframe id="registerFrame" src="about:blank"></iframe></div>
        </div>
    </div>
    
    <script>
        $(document).ready(function(){
            $('.slideshow-container').slick({
                dots: true, 
                infinite: true,
                speed: 500,
                autoplay: true,
                autoplaySpeed: 3000,
                prevArrow: '<button class="slick-prev">&#10094;</button>',
                nextArrow: '<button class="slick-next">&#10095;</button>'
            });
            function openOverlay(id, src){
                const $overlay = $('#'+id);
                $overlay.css('display','flex');
                if (src) {
                    const $frame = $overlay.find('iframe');
                    if ($frame.attr('src') !== src) $frame.attr('src', src);
                }
                $('body').css('overflow','hidden');
            }
            function closeOverlay(id){
                const $overlay = $('#'+id);
                $overlay.hide();
                $('body').css('overflow','auto');
            }
            $('#openLogin').on('click', function(e){ e.preventDefault(); openOverlay('loginOverlay', 'login.php'); });
            $('#openRegister').on('click', function(e){ e.preventDefault(); openOverlay('registerOverlay', 'register.php'); });
            $('[data-close]').on('click', function(){ closeOverlay($(this).data('close')); });
            $(document).on('keydown', function(e){ if (e.key === 'Escape'){ closeOverlay('loginOverlay'); closeOverlay('registerOverlay'); }});
            // bubble iframe redirects to top-level when logged in
            const dashboards = [
                'admin_dashboard.php','student_dashboard.php','faculty_dashboard.php','scholarship_admin_dashboard.php','guidance_admin_dashboard.php','admin_dormitory_dashboard.php','registrar_dashboard.php'
            ];
            $('#loginFrame').on('load', function(){
                try {
                    const href = this.contentWindow.location.href;
                    for (const page of dashboards){
                        if (href.indexOf(page) !== -1){ window.location.href = href; return; }
                    }
                } catch(err) {}
            });
        });
    </script>
</body>
</html>