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
  <meta charset="UTF-8">
  <title>Admin - Manage Topics</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
  <h1>Manage Topics</h1>
  <nav>
    <a href="admin.php">Admin Dashboard</a>
    <a href="courses.php">Manage Courses</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

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
