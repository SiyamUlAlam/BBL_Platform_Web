<?php
session_start();
include("includes/db.php");

// Check if user is admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

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

// Handle Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE title LIKE ? OR code LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM courses");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Courses</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: linear-gradient(to right, #43cea2, #185a9d);
      min-height: 100vh;
      margin: 0;
      color: #333;
      display: flex;
      flex-direction: column;
    }
    .main-wrapper {
      flex: 1 0 auto;
    }

    /* Unified Navigation Bar Styles */
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

    main {
      padding: 2rem;
      max-width: 1100px;
      margin: auto;
    }

    .admin-form {
      background: white;
      padding: 1.5rem;
      margin-bottom: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      animation: slideUp 0.8s ease-in;
    }

    .admin-form input[type="text"] {
      padding: 0.8rem;
      width: 48%;
      margin-right: 2%;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .admin-form input[type="text"]:focus {
      border-color: #4a90e2;
      outline: none;
    }

    .admin-form input[type="submit"] {
      padding: 0.8rem 1.5rem;
      background: #4a90e2;
      border: none;
      color: white;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
    }

    .admin-form input[type="submit"]:hover {
      background: #357ab8;
      transform: scale(1.03);
    }

    .search-bar {
      margin-bottom: 2rem;
      text-align: center;
    }

    .search-bar input[type="text"] {
      padding: 0.8rem;
      width: 60%;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    .search-bar button {
      padding: 0.8rem 1.2rem;
      background-color: #4a90e2;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      margin-left: 10px;
    }

    .search-bar button:hover {
      background-color: #357ab8;
    }

    .courses-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
    }

    @media (max-width: 992px) {
      .courses-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 600px) {
      .courses-grid {
        grid-template-columns: 1fr;
      }
    }

    .course-card {
      background: white;
      padding: 1.8rem;
      border-radius: 14px;
      min-height: 200px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.06);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .course-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }

    .course-card h3 {
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
    }

    .course-card .actions {
      margin-top: 1rem;
    }

    .cta-button {
      background: #27ae60;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
      margin-right: 10px;
    }

    .cta-button:hover {
      background: #219150;
      transform: scale(1.05);
    }

    .delete-link {
      color: #e74c3c;
      text-decoration: none;
      font-weight: 500;
    }

    .delete-link:hover {
      color: #c0392b;
      transform: scale(1.05);
    }

    footer {
      text-align: center;
      padding: 1.5rem;
      margin-top: 2rem;
      background: #f1f1f1;
      font-size: 0.9rem;
      color: #666;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .name1{
      color:rgb(66, 241, 183);
      font-weight: bold;
    }
    .name2{
      color:rgb(242, 249, 140);
      font-weight: bold;
    }
    .name3{
      color:rgb(156, 230, 171);
      font-weight: bold;


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
        <a href="courses.php" class="nav-item active">Courses</a>
        <?php if ($is_admin): ?>
          <a href="admin.php" class="nav-item">Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-item">Logout</a>
      </nav>
      <div class="menu-toggle" onclick="toggleMenu()">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </div>
    </div>
  </header>


<div class="main-wrapper">
<main>

  <?php if ($is_admin): ?>
    <div class="admin-form">
      <h3>Add a New Course</h3>
      <form method="POST">
        <input type="text" name="title" placeholder="Course Title" required>
        <input type="text" name="code" placeholder="Course Code" required>
        <input type="submit" name="add" value="Add Course">
      </form>
    </div>
  <?php endif; ?>

  <form method="GET" class="search-bar">
    <input 
      type="text" 
      name="search" 
      placeholder="Search courses by title or code..." 
      value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
  </form>

  <div class="courses-grid">
    <?php while($course = $result->fetch_assoc()): ?>
      <div class="course-card">
        <h3><i class="fas fa-book"></i> <?php echo htmlspecialchars($course['title']); ?> (<?php echo htmlspecialchars($course['code']); ?>)</h3>
        <div class="actions">
          <a href="explore.php?course_id=<?php echo $course['id']; ?>" class="cta-button">Explore</a>
          <?php if ($is_admin): ?>
            <a href="?delete=<?php echo $course['id']; ?>" class="delete-link" onclick="return confirm('Delete this course?');">Delete</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

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

  <script>
    function toggleMenu() {
      document.getElementById("navLinks").classList.toggle("active");
    }
  </script>

</body>
</html>
