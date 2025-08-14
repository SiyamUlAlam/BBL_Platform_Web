<!-- ...existing code... -->
<?php
session_start();
include("includes/db.php");

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Handle filters
$filter_course = $_GET['course'] ?? '';
$filter_style = $_GET['style'] ?? '';
$filter_type = $_GET['type'] ?? '';

// Fetch courses for dropdown
$course_options = $conn->query("SELECT id, title FROM courses");

// Build query with optional filters
$sql = "SELECT topics.id, topics.title, topics.style, topics.content_type, topics.file_path, courses.title AS course_title 
        FROM topics 
        JOIN courses ON topics.course_id = courses.id 
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($filter_course)) {
    $sql .= " AND course_id = ?";
    $params[] = $filter_course;
    $types .= 'i';
}
if (!empty($filter_style)) {
    $sql .= " AND style = ?";
    $params[] = $filter_style;
    $types .= 's';
}
if (!empty($filter_type)) {
    $sql .= " AND content_type = ?";
    $params[] = $filter_type;
    $types .= 's';
}

$sql .= " ORDER BY topics.id DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
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
    </style>
  <meta charset="UTF-8">
  <title>Manage Topics</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      background: linear-gradient(to right, #43cea2, #185a9d);
      min-height: 100vh;
      margin: 0;
      display: flex;
      flex-direction: column;
      font-family: 'Inter', Arial, sans-serif;
    }
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      background: #357ab8;
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
    .main-wrapper {
      flex: 1 0 auto;
      background: #fff;
      border-radius: 12px;
      max-width: 1100px;
      margin: 2rem auto 1rem auto;
      box-shadow: 0 2px 16px rgba(52, 152, 219, 0.07);
      padding: 2rem 2.5vw;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
      background: #f8fafc;
      border-radius: 8px;
      overflow: hidden;
      font-size: 1rem;
    }
    th, td {
      padding: 0.75rem 0.7rem;
      border-bottom: 1px solid #e0e6ed;
      text-align: left;
    }
    th {
      background: #eaf1fb;
      color: #185a9d;
      font-weight: 700;
      border-bottom: 2px solid #d0d8e4;
    }
    tr:nth-child(even) td {
      background: #f4f8fb;
    }
    tr:hover td {
      background: #e3ecf7;
    }
    form.filter-form {
      margin-top: 1rem;
      margin-bottom: 1rem;
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      align-items: center;
      background: #f8fafc;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      box-shadow: 0 1px 4px rgba(52, 152, 219, 0.04);
    }
    form.filter-form label {
      font-weight: 600;
      color: #357ab8;
    }
    form.filter-form select {
      margin-left: 0.5rem;
      padding: 0.3rem 0.7rem;
      border-radius: 4px;
      border: 1px solid #b5c6d6;
      background: #fff;
      font-size: 1rem;
    }
    .button {
      padding: 7px 18px;
      background: #357ab8;
      color: #fff;
      border: none;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 700;
      font-size: 1rem;
      transition: background 0.2s, box-shadow 0.2s;
      box-shadow: 0 2px 8px rgba(52, 152, 219, 0.07);
    }
    .button:hover {
      background: #185a9d;
      color: #fff;
      box-shadow: 0 4px 16px rgba(52, 152, 219, 0.13);
    }
    .danger {
      background: #dc3545;
      color: #fff;
    }
    .danger:hover {
      background: #b52a37;
    }
  </style>
</head>
<body>

<header>
</header>
  <div class="navbar">
    <div class="logo">
      <a href="admin.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
        <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
        <span class="name1">Brain<span class="name3">-</span>Based</span><span class="name2">Learning</span>
      </a>
    </div>
    <nav class="nav-links" id="navLinks">
      <a href="admin.php" class="nav-item">Admin Dashboard</a>
      <a href="admin_add_topic.php" class="nav-item">Add Topic</a>
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
  <form class="filter-form" method="get">
    <label>Course:
      <select name="course">
        <option value="">All</option>
        <?php while ($course = $course_options->fetch_assoc()): ?>
          <option value="<?= $course['id'] ?>" <?= ($filter_course == $course['id']) ? 'selected' : '' ?>><?= htmlspecialchars($course['title']) ?></option>
        <?php endwhile; ?>
      </select>
    </label>

    <label>Style:
      <select name="style">
        <option value="">All</option>
        <?php
        $styles = ['linguistic','logical-mathematical','spatial','bodily-kinesthetic','musical','interpersonal','intrapersonal','naturalist'];
        foreach ($styles as $style): ?>
          <option value="<?= $style ?>" <?= ($filter_style == $style) ? 'selected' : '' ?>><?= ucwords(str_replace('-', ' ', $style)) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Type:
      <select name="type">
        <option value="">All</option>
        <?php foreach (['article', 'quiz', 'video'] as $type): ?>
          <option value="<?= $type ?>" <?= ($filter_type == $type) ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <button type="submit" class="button">Filter</button>
  </form>

  <?php if ($result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Course</th>
          <th>Title</th>
          <th>Style</th>
          <th>Type</th>
          <th>File</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['course_title']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= ucwords(str_replace('-', ' ', $row['style'])) ?></td>
            <td><?= htmlspecialchars($row['content_type']) ?></td>
            <td>
              <?php if (!empty($row['file_path'])): ?>
                <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">View</a>
              <?php else: ?>
                No file
              <?php endif; ?>
            </td>
            <td>
              <a class="button" href="admin_edit_topic.php?id=<?= $row['id'] ?>">Edit</a>
              <a class="button danger" href="admin_delete_topic.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this topic?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No topics found.</p>
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
      <a href="dashboard.php">User Dashboard</a>
      <a href="admin.php">Admin Dashboard</a>
      <a href="courses.php">Courses</a>
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
</html>
