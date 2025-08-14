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

// Fetch quiz topics for this course
$stmt = $conn->prepare("SELECT id, title, description FROM topics WHERE course_id = ? AND style = 'quizz' ORDER BY title");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quiz Topics - <?= htmlspecialchars($course_title) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #43cea2, #185a9d);
      min-height: 100vh;
      margin: 0;
      font-family: 'Inter', sans-serif;
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
      font-weight: 700;
      padding: 0.5rem 1.2rem;
      border-radius: 6px;
      transition: background 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
    }
    .nav-item:hover {
      background: #fff;
      color: #357ab8;
      box-shadow: 0 2px 8px rgba(52, 152, 219, 0.15);
      transform: translateY(-2px) scale(1.07);
    }
    .main-wrapper {
      flex: 1 0 auto;
      padding: 2rem;
    }
    .page-header {
      text-align: center;
      margin-bottom: 2rem;
      color: white;
    }
    .page-header h1 {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
    }
    .quiz-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 2rem;
      max-width: 1100px;
      margin: 0 auto;
    }
    .quiz-card {
      background: #e6f9f0;
      border: 1.5px solid #e0e7ef;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 4px 18px rgba(52, 152, 219, 0.10);
      transition: box-shadow 0.32s cubic-bezier(0.4,0,0.2,1), transform 0.32s cubic-bezier(0.4,0,0.2,1);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 200px;
    }
    .quiz-card:hover {
      box-shadow: 0 10px 28px rgba(52, 152, 219, 0.13), 0 1.5px 6px rgba(52, 152, 219, 0.08);
      transform: translateY(-6px) scale(1.025);
    }
    .quiz-card h3 {
      font-size: 1.3rem;
      margin-bottom: 1rem;
      color: #357ab8;
      font-weight: 800;
    }
    .quiz-card p {
      color: #333;
      margin-bottom: 1.5rem;
      line-height: 1.5;
      flex-grow: 1;
    }
    .quiz-card a {
      display: inline-block;
      background: #27ae60;
      color: white;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 700;
      text-align: center;
      transition: background 0.3s, transform 0.3s;
    }
    .quiz-card a:hover {
      background: #357ab8;
      transform: scale(1.05);
    }
    .no-quizzes {
      text-align: center;
      background: white;
      padding: 3rem 2rem;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      max-width: 500px;
      margin: 2rem auto;
    }
    .no-quizzes h3 {
      color: #357ab8;
      margin-bottom: 1rem;
    }
    .no-quizzes p {
      color: #666;
      margin-bottom: 1.5rem;
    }
    .no-quizzes a {
      background: #357ab8;
      color: white;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 700;
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
      transition: all 0.18s;
      text-decoration: none;
    }
    .side-nav-btn:hover {
      background: #357ab8;
      color: #fff;
      box-shadow: 0 4px 16px rgba(52,152,219,0.13);
      transform: translateY(-50%) scale(1.08);
    }
    .side-nav-btn.left { left: 24px; }
    .side-nav-btn.right { right: 24px; }
    .side-nav-btn svg {
      width: 26px;
      height: 26px;
      fill: none;
      stroke: currentColor;
      stroke-width: 2.2;
    }
  </style>
</head>
<body>

<header>
  <div class="navbar">
    <div class="logo">
      <a href="dashboard.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
        <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
        <span>Brain-Based Learning</span>
      </a>
    </div>
    <nav class="nav-links">
      <a href="dashboard.php" class="nav-item">Dashboard</a>
      <a href="courses.php" class="nav-item">Courses</a>
      <a href="explore.php?course_id=<?= $course_id ?>" class="nav-item">Back to Explore</a>
      <a href="logout.php" class="nav-item">Logout</a>
    </nav>
  </div>
</header>

<div class="main-wrapper">
  <div class="page-header">
    <h1>Quiz Topics</h1>
    <p>Course: <?= htmlspecialchars($course_title) ?></p>
  </div>

  <?php if ($result->num_rows > 0): ?>
    <div class="quiz-grid">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="quiz-card">
          <h3><?= htmlspecialchars($row['title']) ?></h3>
          <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
          <a href="take_quiz.php?topic_id=<?= $row['id'] ?>">Take Quiz</a>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="no-quizzes">
      <h3>No Quizzes Available</h3>
      <p>There are no quiz topics available for this course yet. Check back later or explore other learning materials.</p>
      <a href="explore.php?course_id=<?= $course_id ?>">Back to Course</a>
    </div>
  <?php endif; ?>
</div>

<a class="side-nav-btn left" href="#" onclick="history.back(); return false;" title="Go back">
  <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
</a>
<a class="side-nav-btn right" href="#" onclick="history.forward(); return false;" title="Go forward">
  <svg viewBox="0 0 24 24" style="transform: scaleX(-1)"><path d="M15 18l-6-6 6-6"/></svg>
</a>

<footer class="footer">
  <div class="footer-content">
    <div>
      <strong>&copy; 2025 Brain-Based Learning Platform</strong><br>
      Empowering learners with science-backed education.
    </div>
    <div>
      <strong>Quick Links</strong><br>
      <a href="dashboard.php">Dashboard</a>
      <a href="courses.php">Courses</a>
      <a href="explore.php?course_id=1">Explore</a>
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
</html>
