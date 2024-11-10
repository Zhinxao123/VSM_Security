<?php
session_start(); // Start the session

// Reset notification when acknowledged
if (isset($_GET['reset_notification'])) {
    unset($_SESSION['has_notification']);
    unset($_SESSION['next_status']);
    echo "<script>window.location.href = 'user_dashboard.php';</script>";  // Use JavaScript to redirect after reset
    exit();
}

// Check if the user is logged in and if they are a user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    // Redirect to login page if not logged in or not a user
    header("Location: login.html");
    exit();
}

// Database logic to check if there are any new notifications based on application status...
include 'db.php';
$user_id = $_SESSION['user_id'];
$query = "SELECT status FROM applications WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$has_notification = false;
$next_status = "";
while ($application = $result->fetch_assoc()) {
    if (in_array($application['status'], ['Final Interview', 'Final Exam', 'Interview', 'Exam', 'Resume Review'])) {
        $has_notification = true;
        $next_status = $application['status'];
        break;
    }
}

if ($has_notification) {
    $_SESSION['has_notification'] = true;
    $_SESSION['next_status'] = $next_status;
} else {
    unset($_SESSION['has_notification']);
    unset($_SESSION['next_status']);
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page - VSM Security</title>
    <link rel="stylesheet" href="index.css">
    <style>
        /* Reduced gap between nav and slider */
        #home {
            position: relative;
            height: 100vh;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .slider {
            width: 100%;
            height: 90%;
            display: flex;
            position: relative;
            justify-content: center;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1s ease;
        }

        .slide.active {
            opacity: 1;
        }

        /* Gradient Overlay */
        .slider::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.7));
            z-index: 1;
        }

        /* Arrows */
        .arrow {
            position: absolute;
            top: 50%;
            z-index: 2;
            width: 50px;
            height: 50px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            font-size: 20px;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .arrow:hover {
            background-color: rgba(255, 255, 255, 0.8);
        }

        .arrow-left {
            left: 20px;
            transform: translateY(-50%);
        }

        .arrow-right {
            right: 20px;
            transform: translateY(-50%);
        }

        /* Dots */
        .dots-container {
            position: absolute;
            bottom: 20px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            z-index: 2;
        }

        .dot {
            width: 12px;
            height: 12px;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .dot.active {
            background-color: #ffffff;
        }

        /* Profile Icon Styling */
        .profile-container {
            display: flex;
            align-items: center;
            position: relative;
        }

        .profile-icon {
            margin-left: 10px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-image: url('defaultpic.jpg');
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }

        /* Notification Badge Styling */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 12px;
            height: 12px;
            background-color: red;
            color: white;
            font-size: 10px;
            display: none;
            border-radius: 50%;
            text-align: center;
            line-height: 12px;
        }

        /* Logo/Icon next to Notification Badge */
        .logo-notification-container {
            display: flex;
            align-items: center;
            position: relative;
        }

        .notification-logo {
            width: 25px;
            height: 25px;
            background-image: url('notification-icon.png'); /* Replace with the notification logo icon */
            background-size: cover;
            background-position: center;
            margin-right: 10px;
            cursor: pointer;
        }

        /* Dropdown Menu Styling */
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            overflow: hidden;
            width: 150px;
            z-index: 1000;
        }

        .dropdown-menu.active {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            transition: background 0.3s;
        }

        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }

    </style>
</head>
<body>
<header>
    <nav>
        <div class="logo-container">
            <div class="icon"><img src="logovsm.png" alt="VSM Security Logo" width="40" height="50"></div>
            <div class="logo">VSM Security</div>
        </div>
        <div class="menu">
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#jobs">Jobs</a>
            <a href="#contact">Contact</a>
        </div>
        <div class="profile-container">
            <div class="logo-notification-container">
                <div class="notification-logo" onclick="toggleNotificationDetails()"></div>
                <div class="notification-badge" id="notification-badge">!</div>
            </div>
            <div class="profile-icon" onclick="toggleDropdown()">
                <img src="defaultpic.png" alt="Profile Icon" width="30" height="30">
            </div>
            <div class="dropdown-menu">
                <a href="#profile">My Profile</a>
                <a href="progress.php">Track My Process</a>
                <a href="index.html">Sign Out</a>
            </div>
        </div>
        <div class="menu-icon" onclick="toggleMenu()">☰</div>
    </nav>
</header>

<!-- Home Section (Full Page Image Slider with Arrows) -->
<section id="home" class="section">
    <div class="slider">
        <!-- Slide 1 -->
        <div class="slide" style="background-image: url('vsm1.jpg');"></div>
        <!-- Slide 2 -->
        <div class="slide" style="background-image: url('vsm2.jpg');"></div>
        <!-- Slide 3 -->
        <div class="slide" style="background-image: url('vsm3.jpg');"></div>
    </div>
    <button class="arrow arrow-left" onclick="prevSlide()">&#10094;</button>
    <button class="arrow arrow-right" onclick="nextSlide()">&#10095;</button>
</section>

<!-- About Section -->
<section id="about" class="section">
    <div class="container">
        <h2>About Us</h2>
        <p>VSM Security is a leading provider of security solutions, dedicated to keeping communities and organizations safe with innovative security technology and professional services.</p>
    </div>
</section>

<!-- Jobs Section -->
<section id="jobs" class="section">
    <div class="container">
        <h2>Career Opportunities</h2>
        <p>Security guard, Accountant, Human Resources</p>
        <a href="job_listing.php" class="apply-button">Apply Now!</a>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="section">
    <div class="container">
        <h2>Contact Us</h2>
        <p>For inquiries and questions</p>
        <div class="contact-content">
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=..." allowfullscreen="" loading="lazy"></iframe>
            </div>
            <div class="form-container">
                <form action="send_email.php" method="post">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Scripts -->
<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.style.opacity = (i === index) ? '1' : '0';
        });
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    setInterval(nextSlide, 5000); // Automatically change slide every 5 seconds

    function toggleDropdown() {
        const dropdownMenu = document.querySelector('.dropdown-menu');
        dropdownMenu.classList.toggle('active');
    }

    document.addEventListener('click', function(event) {
        const profileIcon = document.querySelector('.profile-icon');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        if (!profileIcon.contains(event.target)) {
            dropdownMenu.classList.remove('active');
        }
    });

    // Smooth scrolling for menu links
    document.querySelectorAll('.menu a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    let lastScrollTop = 0;
    let timer;
    const nav = document.querySelector('nav');

    window.addEventListener('scroll', () => {
        clearTimeout(timer);
        const scrollTop = window.scrollY || document.documentElement.scrollTop; // Get current scroll position

        if (scrollTop > 0) { // Check if not at the top
            nav.classList.remove('hidden'); // Show nav when scrolling down
        } else {
            nav.classList.remove('hidden'); // Keep nav visible at the top
        }

        // Hide nav after 2 seconds of inactivity while scrolling down
        timer = setTimeout(() => {
            if (scrollTop > 0) { // Only hide if scrolled down
                nav.classList.add('hidden');
            }
        }, 1000);
    });

    // Show nav when cursor is over nav
    nav.addEventListener('mouseenter', () => {
        nav.classList.remove('hidden'); // Show nav when mouse enters
    });

    // Optionally hide nav when mouse leaves if needed
    nav.addEventListener('mouseleave', () => {
        timer = setTimeout(() => {
            if (window.scrollY > 0) { // Only hide if scrolled down
                nav.classList.add('hidden');
            }
        }, 1000);
    });

    document.addEventListener('DOMContentLoaded', function() {
    // Check if the notification session variable is set and show or hide the badge accordingly
    if (<?php echo isset($_SESSION['has_notification']) && $_SESSION['has_notification'] == true ? 'true' : 'false'; ?>) {
        document.getElementById('notification-badge').style.display = 'block';  // Show notification
    } else {
        document.getElementById('notification-badge').style.display = 'none';   // Hide notification
    }

    // Handle click on notification logo
    document.querySelector('.notification-logo').addEventListener('click', function() {
        // If there's a valid next status, show the alert with the status
        if (<?php echo isset($_SESSION['next_status']) ? 'true' : 'false'; ?>) {
            alert('Your application has moved to the next phase: <?php echo $_SESSION['next_status']; ?>');

            // Immediately hide the notification badge after the click
            document.getElementById('notification-badge').style.display = 'none';

            // Redirect to reset the notification status on the server side
             // This will reset the session and reload the page
        }
    });
});





</script>
</body>
</html>  