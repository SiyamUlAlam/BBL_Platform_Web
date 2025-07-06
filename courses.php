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
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: #f2f4f8;
      color: #333;
      animation: fadeIn 1s ease-in-out;
    }

    /* Navigation Styles */
    header {
      position: sticky;
      top: 0;
      z-index: 100;
      background: linear-gradient(90deg, #4a90e2, #357ab8);
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      max-width: 1200px;
      margin: auto;
      color: white;
    }

    .logo {
      font-size: 1.5rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav-links {
      display: flex;
      gap: 1.5rem;
    }

    .nav-links a {
      color: white;
      text-decoration: none;
      font-weight: 800;
      padding: 0.5rem 0.8rem;
      border-radius: 6px;
      transition: background 0.3s, transform 0.3s;
    }

    .nav-links a:hover,
    .nav-links a.active {
      background: rgba(255, 255, 255, 0.15);
      transform: scale(1.05);
    }

    .menu-toggle {
      display: none;
      font-size: 1.5rem;
      cursor: pointer;
    }

    @media (max-width: 768px) {
      .nav-links {
        flex-direction: column;
        background: #357ab8;
        position: absolute;
        top: 70px;
        right: 0;
        width: 200px;
        display: none;
        padding: 1rem;
        border-radius: 0 0 0 10px;
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
       <Span class="name1">Brain<span class="name3">-</span>Based</Span><Span class="name2">Learning</Span>
      </a>
      </div>
    <nav class="nav-links" id="navLinks">
      <a href="dashboard.php">Dashboard</a>
      <a href="courses.php" class="active">Courses</a>
      <a href="logout.php">Logout</a>
    </nav>
    <div class="menu-toggle" onclick="toggleMenu()">
      <i class="fas fa-bars"></i>
    </div>
  </div>
</header>


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

<footer>
  <p>&copy; 2025 My Learning Platform</p>
</footer>

<script>
  function toggleMenu() {
    document.getElementById("navLinks").classList.toggle("active");
  }
</script>

</body>
</html>
