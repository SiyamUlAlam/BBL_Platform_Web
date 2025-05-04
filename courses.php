<?php
session_start();
include("includes/db.php");

// Check if user is logged in and is admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1; // Admin check

// Handle Add Course
if ($is_admin && isset($_POST['add'])) {
    $title = $_POST['title'];
    $code = $_POST['code'];
    $stmt = $conn->prepare("INSERT INTO courses (title, code) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $code);
    $stmt->execute();
    $stmt->close();
    header("Location: courses.php");
    exit();
}

// Handle Delete Course
if ($is_admin && isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: courses.php");
    exit();
}

// Get Courses
$result = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Courses</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
  <h1>Available Courses</h1>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main style="padding: 2rem; max-width: 800px; margin: auto;">

  <?php if ($is_admin): ?>
  <h3>Add a New Course</h3>
  <form method="POST" style="margin-bottom: 2rem;">
    <input type="text" name="title" placeholder="Course Title" required>
    <input type="text" name="code" placeholder="Course Code" required>
    <input type="submit" name="add" value="Add Course">
  </form>
  <?php endif; ?>

  <!-- Display courses -->
  <?php while($course = $result->fetch_assoc()): ?>
    <div style="background:#f8f9fa; border:1px solid #ccc; margin-bottom:1rem; padding:1rem;">
      <h3><?php echo htmlspecialchars($course['title']); ?> (<?php echo htmlspecialchars($course['code']); ?>)</h3>
      <a href="explore.php?course_id=<?php echo $course['id']; ?>" class="cta-button">Explore</a>
      <?php if ($is_admin): ?>
        <a href="?delete=<?php echo $course['id']; ?>" onclick="return confirm('Delete this course?');" style="color:red; margin-left:10px;">Delete</a>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>

</main>

<footer>
  <p>&copy; 2025 My Learning Platform</p>
</footer>

</body>
</html>
