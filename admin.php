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
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            transition: 0.3s;
        }
        .dashboard a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="dashboard.php">User Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <div class="dashboard">
        <h2>Welcome, Admin</h2>
        <a href="admin_add_topic.php">➕ Add New Topic</a>
        <a href="admin_manage_topics.php">🛠 Manage Topics</a>
        <a href="courses.php">📚 View Courses</a>
        <a href="explore.php?course_id=1">🔍 Explore Learning Styles</a>
    </div>
</main>

<footer>
    <p>&copy; 2025 My Learning Platform. All rights reserved.</p>
</footer>

</body>
</html>
