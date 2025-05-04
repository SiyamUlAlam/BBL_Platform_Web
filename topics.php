<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$course_id = $_GET['course_id'] ?? null;
$mwlt_type = $_GET['mwlt'] ?? null;

if (!$course_id || !$mwlt_type) {
    echo "Invalid access.";
    exit();
}

// Get course title
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$stmt->bind_result($course_title);
$stmt->fetch();
$stmt->close();

// Get topics
$stmt = $conn->prepare("SELECT title, content FROM topics WHERE course_id = ? AND mwlt_type = ?");
$stmt->bind_param("is", $course_id, $mwlt_type);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars(ucwords($mwlt_type)); ?> - <?php echo htmlspecialchars($course_title); ?></title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .topic-card {
      background: #f9f9f9;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 1rem;
      margin: 1rem auto;
      max-width: 700px;
    }
  </style>
</head>
<body>

<header>
  <h1><?php echo htmlspecialchars($course_title); ?> - <?php echo ucwords(str_replace('-', ' ', $mwlt_type)); ?> Learning</h1>
  <nav>
    <a href="explore.php?course_id=<?php echo $course_id; ?>">Back to Explore</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main>
  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="topic-card">
        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center;">No topics found for this learning style yet.</p>
  <?php endif; ?>
</main>

<footer>
  <p>&copy; 2025 Learning Platform</p>
</footer>

</body>
</html>
