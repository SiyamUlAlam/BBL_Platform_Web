<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$course_id = $_GET['course_id'] ?? null;
$style = $_GET['style'] ?? null;

if (!$course_id || !$style) {
    echo "Invalid course or style.";
    exit();
}

// Fetch course title
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$stmt->bind_result($course_title);
$stmt->fetch();
$stmt->close();

// Fetch style-related topics
$stmt = $conn->prepare("SELECT title, description, content_type, file_path FROM topics WHERE course_id = ? AND style = ?");
$stmt->bind_param("is", $course_id, $style);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo ucwords($style); ?> - <?php echo htmlspecialchars($course_title); ?></title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .topic-card {
      background: #f9f9f9;
      padding: 1rem;
      border: 1px solid #ccc;
      border-radius: 10px;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>

<header>
  <h1><?php echo htmlspecialchars($course_title); ?> - <?php echo ucwords(str_replace('-', ' ', $style)); ?> Learning</h1>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="courses.php">Courses</a>
    <a href="explore.php?course_id=<?php echo $course_id; ?>">Back</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main style="padding: 2rem;">
  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="topic-card">
        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
        <?php if (!empty($row['file_path'])): ?>
          <p><a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">📥 Download Resource</a></p>
        <?php endif; ?>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($row['content_type']); ?></p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No topics found for this learning style yet.</p>
  <?php endif; ?>
</main>

<footer>
  <p>&copy; 2025 My Learning Platform. All rights reserved.</p>
</footer>

</body>
</html>
