<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    echo "Invalid course selection.";
    exit();
}

// Fetch course title
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$stmt->bind_result($course_title);
$stmt->fetch();
$stmt->close();

// Fetch quiz topics
$stmt = $conn->prepare("SELECT id, title, description FROM topics WHERE course_id = ? AND content_type = 'quiz'");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quiz Topics - <?= htmlspecialchars($course_title) ?></title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .quiz-card {
      border: 1px solid #ccc;
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 10px;
      background: #f9f9f9;
    }
    .quiz-card h3 {
      margin-bottom: 0.5rem;
    }
    .quiz-card a {
      display: inline-block;
      margin-top: 0.5rem;
      background: #28a745;
      color: white;
      padding: 0.4rem 0.8rem;
      border-radius: 5px;
      text-decoration: none;
    }
  </style>
</head>
<body>
<header>
  <h1>Quiz Topics - <?= htmlspecialchars($course_title) ?></h1>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="courses.php">Courses</a>
    <a href="explore.php?course_id=<?= $course_id ?>">Back</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main style="padding: 2rem;">
  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="quiz-card">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
        <a href="quiz.php?topic_id=<?= $row['id'] ?>">Take Quiz</a>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No quizzes available for this course yet.</p>
  <?php endif; ?>
</main>

<footer>
  <p>&copy; 2025 My Learning Platform</p>
</footer>

</body>
</html>
