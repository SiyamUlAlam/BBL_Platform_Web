<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user info from database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $is_admin);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
  <h1>Dashboard</h1>
  <nav>
    <a href="courses.php">Courses</a>
    <?php if ($is_admin): ?>
      <a href="admin.php">Admin Panel</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main style="text-align:center; padding: 2rem;">
  <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
  <p>Ready to start learning?</p>
  <a href="courses.php" class="cta-button">Go to Courses</a>
</main>

<footer>
  <p>&copy; 2025 My Learning Platform. All rights reserved.</p>
</footer>

</body>
</html>
