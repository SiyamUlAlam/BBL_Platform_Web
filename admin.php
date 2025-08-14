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
        <style>
            .nav-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #f4f8fb;
                color: #357ab8;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                margin: 0 10px;
                box-shadow: 0 2px 8px rgba(52, 152, 219, 0.10);
                cursor: pointer;
                transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
                text-decoration: none;
                outline: none;
                font-size: 1rem;
            }
            .nav-btn:hover, .nav-btn:focus {
                background: #357ab8;
                color: #fff;
                box-shadow: 0 4px 16px rgba(52, 152, 219, 0.13);
                transform: translateY(-2px) scale(1.08);
            }
            .nav-btn svg {
                width: 22px;
                height: 22px;
                fill: none;
                stroke: currentColor;
                stroke-width: 2.2;
                display: block;
            }
            @media (max-width: 600px) {
                .nav-btn {
                    width: auto;
                    height: auto;
                    border-radius: 6px;
                    padding: 7px 18px 7px 14px;
                    gap: 7px;
                }
                .nav-btn span {
                    display: inline;
                }
            }
            .nav-btn span {
                display: none;
                margin-left: 6px;
                font-weight: 600;
                font-size: 1rem;
            }
                    .nav-btn-wrapper {
                        width: 100%;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        margin: 1.2rem 0 1.2rem 0;
                    }
        </style>
        <style>
            .back-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #f4f8fb;
                color: #357ab8;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                margin-bottom: 1.2rem;
                margin-left: 0.2rem;
                box-shadow: 0 2px 8px rgba(52, 152, 219, 0.10);
                cursor: pointer;
                transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
                text-decoration: none;
                outline: none;
            }
            .back-btn:hover, .back-btn:focus {
                background: #357ab8;
                color: #fff;
                box-shadow: 0 4px 16px rgba(52, 152, 219, 0.13);
                transform: translateY(-2px) scale(1.08);
            }
            .back-btn svg {
                width: 22px;
                height: 22px;
                fill: none;
                stroke: currentColor;
                stroke-width: 2.2;
                display: block;
            }
            @media (max-width: 600px) {
                .back-btn {
                    width: auto;
                    height: auto;
                    border-radius: 6px;
                    padding: 7px 18px 7px 14px;
                    gap: 7px;
                }
                .back-btn span {
                    display: inline;
                }
            }
            .back-btn span {
                display: none;
                margin-left: 6px;
                font-weight: 600;
                font-size: 1rem;
            }
        </style>
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
            <a href="admin_quiz_management.php">🎯 Quiz</a>
            <a href="courses.php">📚 View Courses</a>
            <a href="explore.php?course_id=1">🔍 Explore Learning Styles</a>
        </div>
    </main>
</div>

<footer class="footer">
<style>
    .side-nav-btn {
        position: fixed;
        top: 50%;
        z-index: 9999;
        transform: translateY(-50%);
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f4f8fb;
        color: #357ab8;
        border: none;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        box-shadow: 0 2px 8px rgba(52,152,219,0.10);
        cursor: pointer;
        transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
        text-decoration: none;
        outline: none;
        font-size: 1.1rem;
    }
    .side-nav-btn:hover, .side-nav-btn:focus {
        background: #357ab8;
        color: #fff;
        box-shadow: 0 4px 16px rgba(52,152,219,0.13);
        transform: translateY(-50%) scale(1.08);
    }
    .side-nav-btn svg {
        width: 26px;
        height: 26px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2.2;
        display: block;
    }
    .side-nav-btn.left { left: 24px; }
    .side-nav-btn.right { right: 24px; }
    @media (max-width: 700px) {
        .side-nav-btn.left { left: 6px; }
        .side-nav-btn.right { right: 6px; }
        .side-nav-btn { width: 38px; height: 38px; }
        .side-nav-btn svg { width: 20px; height: 20px; }
    }
</style>
<a class="side-nav-btn left" href="#" onclick="history.back(); return false;" title="Go back">
    <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
</a>
<a class="side-nav-btn right" href="#" onclick="history.forward(); return false;" title="Go forward">
    <svg viewBox="0 0 24 24" style="transform: scaleX(-1)"><path d="M15 18l-6-6 6-6"/></svg>
</a>



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
