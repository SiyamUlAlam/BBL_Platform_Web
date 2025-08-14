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
    .topic-card {
      background: #f9f9f9;
      padding: 1rem;
      border: 1px solid #ccc;
      border-radius: 10px;
      margin-bottom: 1rem;
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
      <a href="dashboard.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
        <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
        <span class="name1">Brain<span class="name3">-</span>Based</span><span class="name2">Learning</span>
      </a>
    </div>
    <nav class="nav-links" id="navLinks">
      <a href="dashboard.php" class="nav-item">Dashboard</a>
      <a href="courses.php" class="nav-item">Courses</a>
      <a href="explore.php?course_id=<?php echo $course_id; ?>" class="nav-item">Back</a>
      <a href="logout.php" class="nav-item">Logout</a>
    </nav>
    <div class="menu-toggle" onclick="toggleMenu()">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </div>
  </div>
</header>

<div class="main-wrapper">
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
</div>

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
</body>
<script>
  function toggleMenu() {
    document.getElementById("navLinks").classList.toggle("active");
  }
</script>

</body>
</html>
