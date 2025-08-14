<?php
session_start();
include("includes/db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$message = "";

// Fetch topics for dropdown
$topics = $conn->query("SELECT id, title FROM topics ORDER BY title");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $topic_id = $_POST['topic_id'] ?? '';
    $question = $_POST['question'] ?? '';
    $option_a = $_POST['option_a'] ?? '';
    $option_b = $_POST['option_b'] ?? '';
    $option_c = $_POST['option_c'] ?? '';
    $option_d = $_POST['option_d'] ?? '';
    $correct_option = $_POST['correct_option'] ?? '';

    // Basic validation
    if (!$topic_id || !$question || !$option_a || !$option_b || !$option_c || !$option_d || !$correct_option) {
        $message = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO quizzes (topic_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $topic_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option);

        if ($stmt->execute()) {
            $message = "Quiz added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Add Quiz - Admin</title>
<style>
  form { max-width: 600px; margin: 2rem auto; padding: 1rem; border: 1px solid #ccc; border-radius: 10px; background: #f9f9f9; }
  label { display: block; margin-top: 1rem; }
  input[type=text], textarea, select { width: 100%; padding: 0.5rem; margin-top: 0.5rem; }
  button { margin-top: 1rem; padding: 0.5rem 1rem; background: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; }
  button:hover { background: #0056b3; }
  .message { text-align: center; padding: 1rem; color: green; }
</style>
</head>
<body>

<h1 style="text-align:center;">Add New Quiz</h1>
<nav style="text-align:center; margin-bottom: 1rem;">
  <a href="admin_manage_topics.php">Manage Topics</a> |
  <a href="admin_manage_quizzes.php">Manage Quizzes</a> |
  <a href="logout.php">Logout</a>
</nav>

<?php if ($message): ?>
  <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">
  <label for="topic_id">Select Topic:</label>
  <select name="topic_id" required>
    <option value="">-- Select Topic --</option>
    <?php while ($topic = $topics->fetch_assoc()): ?>
      <option value="<?= $topic['id'] ?>"><?= htmlspecialchars($topic['title']) ?></option>
    <?php endwhile; ?>
  </select>

  <label for="question">Question:</label>
  <textarea name="question" rows="3" required></textarea>

  <label for="option_a">Option A:</label>
  <input type="text" name="option_a" required>

  <label for="option_b">Option B:</label>
  <input type="text" name="option_b" required>

  <label for="option_c">Option C:</label>
  <input type="text" name="option_c" required>

  <label for="option_d">Option D:</label>
  <input type="text" name="option_d" required>

  <label for="correct_option">Correct Option:</label>
  <select name="correct_option" required>
    <option value="">-- Select Correct Option --</option>
    <option value="A">Option A</option>
    <option value="B">Option B</option>
    <option value="C">Option C</option>
    <option value="D">Option D</option>
  </select>

  <button type="submit">Add Quiz</button>
</form>

</body>
</html>
