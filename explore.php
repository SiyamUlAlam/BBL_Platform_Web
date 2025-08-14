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
    body {
      background: linear-gradient(to right, #43cea2, #185a9d);
      min-height: 100vh;
      margin: 0;
      display: flex;
      flex-direction: column;
    }
    .main-wrapper {
      flex: 1 0 auto;
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
    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
      padding: 2.5rem 2rem;
      max-width: 950px;
      margin: 0 auto;
    }
    .card {
  background: #e6f9f0;
  border: 1.5px solid #e0e7ef;
  padding: 1.7rem 1.2rem 1.3rem 1.2rem;
  border-radius: 16px;
  min-height: 150px;
  box-shadow: 0 4px 18px rgba(52, 152, 219, 0.10);
  text-align: center;
  transition: box-shadow 0.32s cubic-bezier(0.4,0,0.2,1), transform 0.32s cubic-bezier(0.4,0,0.2,1), background 0.32s cubic-bezier(0.4,0,0.2,1);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
    }
    .card h3 {
      font-size: 1.18rem;
      margin-bottom: 1rem;
      color: #357ab8;
      font-weight: 800;
      letter-spacing: 0.5px;
    }
    .card:hover {
  box-shadow: 0 10px 28px rgba(52, 152, 219, 0.13), 0 1.5px 6px rgba(52, 152, 219, 0.08);
  transform: translateY(-6px) scale(1.025);
    }
    .card a {
      display: inline-block;
      margin-top: 0.7rem;
      padding: 0.6rem 1.3rem;
  background: #27ae60;
      color: white;
      border-radius: 7px;
      text-decoration: none;
      font-size: 1.08rem;
      font-weight: 700;
      letter-spacing: 0.2px;
      box-shadow: 0 2px 8px rgba(52, 152, 219, 0.10);
      transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
    }
    .card a:hover {
      background: #357ab8;
      color: #fff;
      box-shadow: 0 4px 16px rgba(52, 152, 219, 0.13);
      transform: scale(1.06);
    }
    @media (max-width: 992px) {
      .grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    @media (max-width: 700px) {
      .grid {
        grid-template-columns: 1fr;
      }
      .card {
        min-height: 120px;
        padding: 1.1rem 0.7rem 1rem 0.7rem;
      }
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
  
</head>
<body>

<header>
  <div class="navbar">
    <div class="logo">
      <a href="dashboard.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
        <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
        <span class="name1">Brain<span class="name3">-</span>Based</span><span class="name2">Learning</span>
      </a>
    </div>
    <nav class="nav-links" id="navLinks">
      <a href="dashboard.php" class="nav-item">Dashboard</a>
      <a href="courses.php" class="nav-item">Courses</a>
      <a href="logout.php" class="nav-item">Logout</a>
    </nav>
    <div class="menu-toggle" onclick="toggleMenu()">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </div>
  </div>
</header>
<script>
  function toggleMenu() {
    document.getElementById("navLinks").classList.toggle("active");
  }
</script>

<div class="main-wrapper">
<main>
  <div class="grid">
    <?php
    $styles = [
      'linguistic', 'logical-mathematical', 'spatial',
      'bodily-kinesthetic', 'musical', 'interpersonal',
      'intrapersonal', 'naturalist', 'quizz'
    ];

    foreach ($styles as $style):
      $label = $style === 'quizz' ? 'Quiz' : ucwords(str_replace('-', ' ', $style));
    ?>
    <div class="card">
      <h3><?php echo $label; ?></h3>
      <?php if ($style === 'quizz'): ?>
        <a href="quizzes.php?course_id=<?php echo $course_id; ?>">Explore</a>
      <?php else: ?>
        <a href="learning_style.php?course_id=<?php echo $course_id; ?>&style=<?php echo $style; ?>">Explore</a>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
 </main>
</div>
<a class="side-nav-btn left" href="#" onclick="history.back(); return false;" title="Go back">
    <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
</a>
<a class="side-nav-btn right" href="#" onclick="history.forward(); return false;" title="Go forward">
    <svg viewBox="0 0 24 24" style="transform: scaleX(-1)"><path d="M15 18l-6-6 6-6"/></svg>
</a>

  <!-- <p>&copy; 2025 My Learning Platform. All rights reserved.</p> -->
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
      <a href="explore.php?course_id=<?php echo $course_id; ?>">Explore</a>
      <a href="logout.php">Logout</a>
    </div>
    <div>
      <strong>Contact</strong><br>
      Email: <a href="mailto:2002032@icte.bdu.ac.bd">2002032@icte.bdu.ac.bd</a><br>
      <span>Phone: +8801887240900</span>
    </div>
  </div>
</footer>
</html>
