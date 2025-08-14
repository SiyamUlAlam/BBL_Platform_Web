<?php
session_start();
include("includes/db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$message = "";
$question_id = $_GET['id'] ?? null;

if (!$question_id) {
    header("Location: admin_manage_quizzes.php");
    exit();
}

// Fetch existing question data
$stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE id = ?");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$question_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$question_data) {
    header("Location: admin_manage_quizzes.php");
    exit();
}

// Fetch topics for dropdown (only quiz topics)
$topics = $conn->query("SELECT id, title, course_id FROM topics WHERE style = 'quizz' ORDER BY title");

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
        // Update question in database
        $stmt = $conn->prepare("UPDATE quiz_questions SET topic_id = ?, question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ? WHERE id = ?");
        $stmt->bind_param("issssssi", $topic_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option, $question_id);
        
        if ($stmt->execute()) {
            $message = "Quiz question updated successfully!";
            // Refresh question data
            $stmt2 = $conn->prepare("SELECT * FROM quiz_questions WHERE id = ?");
            $stmt2->bind_param("i", $question_id);
            $stmt2->execute();
            $question_data = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
        } else {
            $message = "Error updating question: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Quiz Question - Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #43cea2, #185a9d);
      min-height: 100vh;
      margin: 0;
      font-family: 'Inter', sans-serif;
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
      font-weight: 700;
      padding: 0.5rem 1.2rem;
      border-radius: 6px;
      transition: background 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
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
    }
    .main-wrapper { 
      flex: 1 0 auto; 
      padding: 2rem;
    }
    .form-container {
      max-width: 700px;
      margin: 0 auto;
      background: white;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    .form-header {
      text-align: center;
      margin-bottom: 2rem;
      color: #357ab8;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #333;
    }
    input[type="text"], textarea, select {
      width: 100%;
      padding: 0.8rem;
      border: 2px solid #e0e7ef;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s;
      box-sizing: border-box;
    }
    input:focus, textarea:focus, select:focus {
      border-color: #357ab8;
      outline: none;
    }
    textarea {
      resize: vertical;
      min-height: 100px;
    }
    .options-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    @media (max-width: 600px) {
      .options-grid {
        grid-template-columns: 1fr;
      }
    }
    .btn-group {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
    }
    button, .btn {
      flex: 1;
      padding: 1rem;
      border: none;
      border-radius: 8px;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
      text-align: center;
      display: inline-block;
    }
    .btn-primary {
      background: linear-gradient(90deg, #43cea2 0%, #4a90e2 50%, #357ab8 100%);
      color: white;
      box-shadow: 0 4px 16px rgba(52, 152, 219, 0.10);
    }
    .btn-primary:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 8px 24px rgba(52, 152, 219, 0.18);
    }
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    .btn-secondary:hover {
      background: #5a6268;
      transform: translateY(-2px) scale(1.02);
    }
    .message {
      text-align: center;
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 8px;
      font-weight: 600;
    }
    .message.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .message.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .footer {
      background: #232946;
      color: #fff;
      text-align: center;
      padding: 1rem 0;
      margin-top: auto;
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
      transition: all 0.18s;
      text-decoration: none;
      outline: none;
    }
    .side-nav-btn:hover {
      background: #357ab8;
      color: #fff;
      box-shadow: 0 4px 16px rgba(52,152,219,0.13);
      transform: translateY(-50%) scale(1.08);
    }
    .side-nav-btn.left { left: 24px; }
    .side-nav-btn.right { right: 24px; }
    .side-nav-btn svg {
      width: 26px;
      height: 26px;
      fill: none;
      stroke: currentColor;
      stroke-width: 2.2;
    }
  </style>
</head>
<body>

<header>
  <div class="navbar">
    <div class="logo">
      <a href="admin.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
        <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
        <span>Brain-Based Learning</span>
      </a>
    </div>
    <nav class="nav-links">
      <a href="admin.php" class="nav-item">Admin Dashboard</a>
      <a href="admin_add_topic.php" class="nav-item">Add Topic</a>
      <a href="admin_add_quiz.php" class="nav-item">Add Quiz</a>
      <a href="admin_manage_quizzes.php" class="nav-item active">Manage Quizzes</a>
      <a href="logout.php" class="nav-item">Logout</a>
    </nav>
  </div>
</header>

<div class="main-wrapper">
  <div class="form-container">
    <div class="form-header">
      <h1>Edit Quiz Question</h1>
      <p>Modify the quiz question details</p>
    </div>

    <?php if ($message): ?>
      <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="topic_id">Select Quiz Topic:</label>
        <select name="topic_id" id="topic_id" required>
          <option value="">-- Select Quiz Topic --</option>
          <?php 
          // Reset the result for reuse
          $topics = $conn->query("SELECT id, title, course_id FROM topics WHERE style = 'quizz' ORDER BY title");
          while ($topic = $topics->fetch_assoc()): ?>
            <option value="<?php echo $topic['id']; ?>" <?php echo ($question_data['topic_id'] == $topic['id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($topic['title']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="question">Question:</label>
        <textarea name="question" id="question" required placeholder="Enter your question here..."><?php echo htmlspecialchars($question_data['question']); ?></textarea>
      </div>

      <div class="options-grid">
        <div class="form-group">
          <label for="option_a">Option A:</label>
          <input type="text" name="option_a" id="option_a" required placeholder="First option..." value="<?php echo htmlspecialchars($question_data['option_a']); ?>">
        </div>

        <div class="form-group">
          <label for="option_b">Option B:</label>
          <input type="text" name="option_b" id="option_b" required placeholder="Second option..." value="<?php echo htmlspecialchars($question_data['option_b']); ?>">
        </div>

        <div class="form-group">
          <label for="option_c">Option C:</label>
          <input type="text" name="option_c" id="option_c" required placeholder="Third option..." value="<?php echo htmlspecialchars($question_data['option_c']); ?>">
        </div>

        <div class="form-group">
          <label for="option_d">Option D:</label>
          <input type="text" name="option_d" id="option_d" required placeholder="Fourth option..." value="<?php echo htmlspecialchars($question_data['option_d']); ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="correct_option">Correct Answer:</label>
        <select name="correct_option" id="correct_option" required>
          <option value="">-- Select Correct Option --</option>
          <option value="A" <?php echo ($question_data['correct_option'] == 'A') ? 'selected' : ''; ?>>A</option>
          <option value="B" <?php echo ($question_data['correct_option'] == 'B') ? 'selected' : ''; ?>>B</option>
          <option value="C" <?php echo ($question_data['correct_option'] == 'C') ? 'selected' : ''; ?>>C</option>
          <option value="D" <?php echo ($question_data['correct_option'] == 'D') ? 'selected' : ''; ?>>D</option>
        </select>
      </div>

      <div class="btn-group">
        <button type="submit" class="btn-primary">Update Question</button>
        <a href="admin_manage_quizzes.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<a class="side-nav-btn left" href="#" onclick="history.back(); return false;" title="Go back">
  <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
</a>
<a class="side-nav-btn right" href="#" onclick="history.forward(); return false;" title="Go forward">
  <svg viewBox="0 0 24 24" style="transform: scaleX(-1)"><path d="M15 18l-6-6 6-6"/></svg>
</a>

<footer class="footer">
  <p>&copy; 2025 Brain-Based Learning Platform. Empowering learners with science-backed education.</p>
</footer>

</body>
</html>
