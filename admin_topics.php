<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // If not an admin, redirect to login or dashboard
    header("Location: login.php");
    exit();
}

include("includes/db.php");

// Handle Add Topic
if (isset($_POST['add_topic'])) {
    $course_id = $_POST['course_id'];
    $topic_name = $_POST['topic_name'];
    $mwtl_type = $_POST['mwtl_type'];

    $stmt = $conn->prepare("INSERT INTO topics (course_id, topic_name, mwtl_type) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $course_id, $topic_name, $mwtl_type);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_topics.php");
    exit();
}

// Fetch courses
$courses = $conn->query("SELECT * FROM courses");

// Fetch topics
$topics = $conn->query("SELECT * FROM topics");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
      .nav-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f4f8fb;
        color: #357ab8;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        margin: 0 10px;
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.10);
        cursor: pointer;
        transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
        text-decoration: none;
        outline: none;
        font-size: 1rem;
      }
      .nav-btn:hover, .nav-btn:focus {
        background: #357ab8;
        color: #fff;
        box-shadow: 0 4px 16px rgba(52, 152, 219, 0.13);
        transform: translateY(-2px) scale(1.08);
      }
      .nav-btn svg {
        width: 22px;
        height: 22px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2.2;
        display: block;
      }
      @media (max-width: 600px) {
        .nav-btn {
          width: auto;
          height: auto;
          border-radius: 6px;
          padding: 7px 18px 7px 14px;
          gap: 7px;
        }
        .nav-btn span {
          display: inline;
        }
      }
      .nav-btn span {
        display: none;
        margin-left: 6px;
        font-weight: 600;
        font-size: 1rem;
      }
      .nav-btn-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 1.2rem 0 1.2rem 0;
      }
    </style>
    <style>
      .back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f4f8fb;
        color: #357ab8;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        margin-bottom: 1.2rem;
        margin-left: 0.2rem;
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.10);
        cursor: pointer;
        transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
        text-decoration: none;
        outline: none;
      }
      .back-btn:hover, .back-btn:focus {
        background: #357ab8;
        color: #fff;
        box-shadow: 0 4px 16px rgba(52, 152, 219, 0.13);
        transform: translateY(-2px) scale(1.08);
      }
      .back-btn svg {
        width: 22px;
        height: 22px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2.2;
        display: block;
      }
      @media (max-width: 600px) {
        .back-btn {
          width: auto;
          height: auto;
          border-radius: 6px;
          padding: 7px 18px 7px 14px;
          gap: 7px;
        }
        .back-btn span {
          display: inline;
        }
      }
      .back-btn span {
        display: none;
        margin-left: 6px;
        font-weight: 600;
        font-size: 1rem;
      }
    </style>
  <meta charset="UTF-8">
  <title>Admin - Manage Topics</title>
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
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 0.75rem; border: 1px solid #ccc; text-align: left; }
    th { background-color: #f9f9f9; }
    form.filter-form { margin-top: 1rem; margin-bottom: 1rem; }
    .button { padding: 6px 12px; background: linear-gradient(90deg, #43cea2 0%, #4a90e2 50%, #357ab8 100%); color: white; border: none; border-radius: 4px; text-decoration: none; font-weight: 700; }
    .button:hover { background: linear-gradient(90deg, #357ab8 0%, #4a90e2 80%, #43cea2 100%); color: #fff; }
    .danger { background-color: #dc3545; }
  </style>
</head>
<body>

<header>
  <div class="navbar">
    <div class="logo">
      <a href="admin.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
        <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
        <span class="name1">Brain<span class="name3">-</span>Based</span><span class="name2">Learning</span>
      </a>
    </div>
    <nav class="nav-links" id="navLinks">
      <a href="admin.php" class="nav-item">Admin Dashboard</a>
      <a href="courses.php" class="nav-item">Manage Courses</a>
      <a href="logout.php" class="nav-item">Logout</a>
    </nav>
    <div class="menu-toggle" onclick="toggleMenu()">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </div>
  </div>
</header>

<div class="main-wrapper">
  <a class="back-btn" href="#" onclick="history.back(); return false;" title="Go back">
    <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    <span>Back</span>
  </a>
<main>
  <h2>Add a New Topic</h2>
  <form method="POST" action="admin_topics.php">
    <select name="course_id" required>
      <option value="">Select Course</option>
      <?php while ($course = $courses->fetch_assoc()): ?>
        <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
      <?php endwhile; ?>
    </select>
    <input type="text" name="topic_name" placeholder="Topic Name" required>
    <select name="mwtl_type" required>
      <option value="">Select MWL Type</option>
      <option value="Linguistic">Linguistic</option>
      <option value="Logical-Mathematical">Logical-Mathematical</option>
      <option value="Spatial">Spatial</option>
      <option value="Bodily-Kinesthetic">Bodily-Kinesthetic</option>
      <option value="Musical">Musical</option>
      <option value="Interpersonal">Interpersonal</option>
      <option value="Intrapersonal">Intrapersonal</option>
      <option value="Naturalist">Naturalist</option>
    </select>
    <input type="submit" name="add_topic" value="Add Topic">
  </form>

  <h2>Existing Topics</h2>
  <table>
    <thead>
      <tr>
        <th>Course</th>
        <th>Topic Name</th>
        <th>MWTL Type</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($topic = $topics->fetch_assoc()): ?>
        <tr>
          <td><?php echo $topic['course_id']; ?></td>
          <td><?php echo $topic['topic_name']; ?></td>
          <td><?php echo $topic['mwtl_type']; ?></td>
          <td><a href="delete_topic.php?id=<?php echo $topic['id']; ?>">Delete</a></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</main>
</div>

<footer class="footer">
<style>
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
<a class="side-nav-btn left" href="#" onclick="history.back(); return false;" title="Go back">
  <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
</a>
<a class="side-nav-btn right" href="#" onclick="history.forward(); return false;" title="Go forward">
  <svg viewBox="0 0 24 24" style="transform: scaleX(-1)"><path d="M15 18l-6-6 6-6"/></svg>
</a>

  <div class="footer-content">
    <div>
      <strong>&copy; 2025 Brain-Based Learning Platform</strong><br>
      Empowering learners with science-backed education.
    </div>
    <div>
      <strong>Quick Links</strong><br>
      <a href="admin.php">Admin Dashboard</a>
      <a href="courses.php">Manage Courses</a>
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

<main>
  <h2>Add a New Topic</h2>
  <form method="POST" action="admin_topics.php">
    <select name="course_id" required>
      <option value="">Select Course</option>
      <?php while ($course = $courses->fetch_assoc()): ?>
        <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
      <?php endwhile; ?>
    </select>
    <input type="text" name="topic_name" placeholder="Topic Name" required>
    <select name="mwtl_type" required>
      <option value="">Select MWL Type</option>
      <option value="Linguistic">Linguistic</option>
      <option value="Logical-Mathematical">Logical-Mathematical</option>
      <option value="Spatial">Spatial</option>
      <option value="Bodily-Kinesthetic">Bodily-Kinesthetic</option>
      <option value="Musical">Musical</option>
      <option value="Interpersonal">Interpersonal</option>
      <option value="Intrapersonal">Intrapersonal</option>
      <option value="Naturalist">Naturalist</option>
    </select>
    <input type="submit" name="add_topic" value="Add Topic">
  </form>

  <h2>Existing Topics</h2>
  <table>
    <thead>
      <tr>
        <th>Course</th>
        <th>Topic Name</th>
        <th>MWTL Type</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($topic = $topics->fetch_assoc()): ?>
        <tr>
          <td><?php echo $topic['course_id']; ?></td>
          <td><?php echo $topic['topic_name']; ?></td>
          <td><?php echo $topic['mwtl_type']; ?></td>
          <td><a href="delete_topic.php?id=<?php echo $topic['id']; ?>">Delete</a></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</main>

</body>
</html>
