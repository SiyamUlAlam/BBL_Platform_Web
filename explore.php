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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Explore <?php echo htmlspecialchars($course_title); ?></title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; padding: 2rem; }
    .card {
      padding: 1rem;
      background: #f0f8ff;
      border: 1px solid #ccc;
      border-radius: 12px;
      text-align: center;
      transition: 0.3s;
    }
    .card:hover { background: #e6f2ff; transform: scale(1.02); }
    .card a {
      display: inline-block;
      margin-top: 0.5rem;
      padding: 0.5rem 1rem;
      background: #007BFF;
      color: white;
      border-radius: 5px;
      text-decoration: none;
    }
  </style>
</head>
<body>

<header>
  <h1><?php echo htmlspecialchars($course_title); ?> - Learning Styles</h1>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="courses.php">Courses</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main>
  <div class="grid">
    <?php
    $styles = [
      'linguistic', 'logical-mathematical', 'spatial',
      'bodily-kinesthetic', 'musical', 'interpersonal',
      'intrapersonal', 'naturalist'
    ];

    foreach ($styles as $style):
    ?>
    <div class="card">
      <h3><?php echo ucwords(str_replace('-', ' ', $style)); ?></h3>
      <a href="learning_style.php?course_id=<?php echo $course_id; ?>&style=<?php echo $style; ?>">Explore</a>

    </div>
    <?php endforeach; ?>
  </div>
</main>

<footer>
  <p>&copy; 2025 My Learning Platform. All rights reserved.</p>
</footer>

</body>
</html>
