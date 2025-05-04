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
  <meta charset="UTF-8">
  <title>Manage Topics</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 0.75rem; border: 1px solid #ccc; text-align: left; }
    th { background-color: #f9f9f9; }
    form.filter-form { margin-top: 1rem; margin-bottom: 1rem; }
    .button { padding: 6px 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; text-decoration: none; }
    .danger { background-color: #dc3545; }
  </style>
</head>
<body>

<header>
  <h1>Manage Topics</h1>
  <nav>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_add_topic.php">Add Topic</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

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

<footer>
  <p>&copy; 2025 My Learning Platform</p>
</footer>

</body>
</html>
