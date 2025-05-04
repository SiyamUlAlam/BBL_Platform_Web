<?php
session_start();
include("includes/db.php");

// Only allow access if the user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['email'] !== '2002032@icte.bdu.ac.bd') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if (isset($_POST['add'])) {
    $course_id = $_POST['course_id'];
    $style = $_POST['style'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO topics (course_id, style, title, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $course_id, $style, $title, $description);
    $stmt->execute();
    $stmt->close();

    $success = "Resource added successfully.";
}

// Fetch courses
$courses = $conn->query("SELECT id, title FROM courses");

$styles = [
    'linguistic', 'logical-mathematical', 'spatial',
    'bodily-kinesthetic', 'musical', 'interpersonal',
    'intrapersonal', 'naturalist'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Resource</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    form {
      max-width: 600px;
      margin: 2rem auto;
      background: #f9f9f9;
      padding: 1.5rem;
      border-radius: 10px;
      border: 1px solid #ccc;
    }
    input, select, textarea {
      width: 100%;
      padding: 0.5rem;
      margin-top: 0.5rem;
      margin-bottom: 1rem;
    }
    button {
      background-color: #007BFF;
      color: white;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 5px;
    }
    .success {
      text-align: center;
      color: green;
    }
  </style>
</head>
<body>

<header>
  <h1>Add New Resource</h1>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main>
  <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

  <form method="POST">
    <label for="course_id">Select Course:</label>
    <select name="course_id" required>
      <?php while ($course = $courses->fetch_assoc()): ?>
        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
      <?php endwhile; ?>
    </select>

    <label for="style">Select Learning Style:</label>
    <select name="style" required>
      <?php foreach ($styles as $style): ?>
        <option value="<?php echo $style; ?>"><?php echo ucwords(str_replace('-', ' ', $style)); ?></option>
      <?php endforeach; ?>
    </select>

    <label for="title">Topic Title:</label>
    <input type="text" name="title" required>

    <label for="description">Description:</label>
    <textarea name="description" rows="5" required></textarea>

    <button type="submit" name="add">Add Resource</button>
  </form>
</main>

<footer>
  <p>&copy; 2025 My Learning Platform</p>
</footer>

</body>
</html>
