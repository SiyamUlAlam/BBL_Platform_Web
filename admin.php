<?php
session_start();
include("includes/db.php");

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
        <style>
            body {
                background: linear-gradient(to right, #43cea2, #185a9d);
                min-height: 100vh;
                margin: 0;
                display: flex;
                flex-direction: column;
            }
            .navbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 2rem;
                background: linear-gradient(90deg, #4a90e2, #357ab8);
                box-shadow: 0 4px 10px rgba(0,0,0,0.08);
                position: sticky;
                top: 0;
                z-index: 100;
            }
            .logo {
                font-size: 1.5rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;
                color: white;
                text-decoration: none;
            }
            .nav-links {
                display: flex;
                gap: 1.5rem;
            }
            .nav-item {
                color: white;
                text-decoration: none;
                font-family: 'Inter', sans-serif;
                font-size: 1rem;
                font-weight: 700;
                padding: 0.5rem 1.2rem;
                border-radius: 6px;
                position: relative;
                transition: background 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
                box-shadow: 0 2px 8px rgba(52, 152, 219, 0);
                display: inline-block;
            }
            .nav-item:hover {
                background: #fff;
                color: #357ab8;
                box-shadow: 0 2px 8px rgba(52, 152, 219, 0.15);
                transform: translateY(-2px) scale(1.07);
            }
            .nav-item.active {
                background: #fff;
                color: #357ab8;
                font-weight: 900;
                box-shadow: 0 2px 12px rgba(52, 152, 219, 0.18);
                border: 2px solid #357ab8;
                transform: scale(1.12);
                z-index: 2;
            }
            .menu-toggle {
                display: none;
                cursor: pointer;
                margin-left: 1rem;
            }
            @media (max-width: 900px) {
                .navbar {
                    flex-direction: column;
                    align-items: flex-start;
                    padding: 1rem;
                }
                .nav-links {
                    width: 100%;
                    flex-direction: column;
                    gap: 0.5rem;
                    display: none;
                    background: #357ab8;
                    border-radius: 0 0 10px 10px;
                    margin-top: 0.5rem;
                    padding: 1rem 0;
                }
                .nav-links.active {
                    display: flex;
                }
                .menu-toggle {
                    display: block;
                }
            }
            .main-wrapper { flex: 1 0 auto; }
            .dashboard {
                max-width: 600px;
                margin: 3rem auto;
                padding: 2rem;
                border: 1px solid #ddd;
                border-radius: 10px;
                background-color: #f9f9f9;
            }
            .dashboard h2 {
                text-align: center;
                margin-bottom: 2rem;
            }
            .dashboard a {
                display: block;
                padding: 0.75rem;
                margin: 0.5rem 0;
                background: linear-gradient(90deg, #43cea2 0%, #4a90e2 50%, #357ab8 100%);
                color: #fff;
                text-decoration: none;
                text-align: center;
                border-radius: 5px;
                font-weight: 700;
                font-size: 1.1rem;
                letter-spacing: 1px;
                box-shadow: 0 4px 16px rgba(52, 152, 219, 0.10);
                transition: background 0.3s, box-shadow 0.3s, transform 0.2s;
            }
            .dashboard a:hover {
                background: linear-gradient(90deg, #357ab8 0%, #4a90e2 80%, #43cea2 100%);
                color: #fff;
                box-shadow: 0 8px 24px rgba(52, 152, 219, 0.18);
                transform: translateY(-2px) scale(1.04);
            }
            .footer {
                background: #232946;
                color: #fff;
                width: 100%;
                margin-top: 0;
                padding: 0;
            }
            .footer-content {
                max-width: 1100px;
                margin: 0 auto;
                padding: 1.5rem 1rem;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
            }
            .footer-content strong {
                color: #43cea2;
            }
            .footer-content a {
                color: #f4faff;
                text-decoration: none;
                margin-right: 10px;
                transition: color 0.2s;
            }
            .footer-content a:hover {
                color: #43cea2;
                text-decoration: underline;
            }
            .footer-content div {
                flex: 1 1 200px;
                min-width: 180px;
            }
        </style>
</head>
<body>

<header>
    <div class="navbar">
        <div class="logo">
            <a href="admin.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
                <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
                <span class="name1">Brain<span class="name3">-</span>Based</span><span class="name2">Learning</span>
            </a>
        </div>
        <nav class="nav-links" id="navLinks">
            <a href="dashboard.php" class="nav-item">User Dashboard</a>
            <a href="admin.php" class="nav-item active">Admin Dashboard</a>
            <a href="logout.php" class="nav-item">Logout</a>
        </nav>
        <div class="menu-toggle" onclick="toggleMenu()">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </div>
    </div>
</header>

<div class="main-wrapper">
    <main>
        <div class="dashboard">
            <h2>Welcome, Admin</h2>
            <a href="admin_add_topic.php">➕ Add New Topic</a>
            <a href="admin_manage_topic.php">🛠 Manage Topics</a>
            <a href="courses.php">📚 View Courses</a>
            <a href="explore.php?course_id=1">🔍 Explore Learning Styles</a>
        </div>
    </main>
</div>

<footer class="footer">
    <div class="footer-content">
        <div>
            <strong>&copy; 2025 Brain-Based Learning Platform</strong><br>
            Empowering learners with science-backed education.
        </div>
        <div>
            <strong>Quick Links</strong><br>
            <a href="dashboard.php">User Dashboard</a>
            <a href="admin.php">Admin Dashboard</a>
            <a href="courses.php">Courses</a>
            <a href="logout.php">Logout</a>
        </div>
        <div>
            <strong>Contact</strong><br>
            Email: <a href="mailto:2002032@icte.bdu.ac.bd">2002032@icte.bdu.ac.bd</a><br>
            <span>Phone: +8801887240900</span>
        </div>
    </div>
</footer>
</body>
<script>
    function toggleMenu() {
        document.getElementById("navLinks").classList.toggle("active");
    }
</script>

</body>
</html>
