<?php
session_start();
include("includes/db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle delete action
if (isset($_GET['delete']) && $_GET['delete']) {
    $question_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    if ($stmt->execute()) {
        $message = "Question deleted successfully!";
    } else {
        $message = "Error deleting question.";
    }
    $stmt->close();
}

// Fetch all quiz questions with topic info
$query = "SELECT qq.id, qq.question, qq.option_a, qq.option_b, qq.option_c, qq.option_d, 
                 qq.correct_option, t.title as topic_title, c.name as course_name
          FROM quiz_questions qq 
          JOIN topics t ON qq.topic_id = t.id 
          LEFT JOIN courses c ON t.course_id = c.id 
          ORDER BY c.name, t.title, qq.id";
$questions = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Quiz Questions - Admin</title>
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
    .content-container {
      max-width: 1200px;
      margin: 0 auto;
      background: white;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    .page-header {
      text-align: center;
      margin-bottom: 2rem;
      color: #357ab8;
    }
    .quiz-card {
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      transition: all 0.3s;
    }
    .quiz-card:hover {
      border-color: #357ab8;
      box-shadow: 0 4px 16px rgba(53, 122, 184, 0.1);
    }
    .quiz-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }
    .quiz-info {
      flex: 1;
    }
    .course-topic {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }
    .question-text {
      font-weight: 600;
      color: #333;
      margin-bottom: 1rem;
      line-height: 1.4;
    }
    .options-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.8rem;
      margin-bottom: 1rem;
    }
    .option {
      padding: 0.5rem;
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      font-size: 0.9rem;
    }
    .option.correct {
      background: #d1e7dd;
      border-color: #27ae60;
      font-weight: 600;
    }
    .quiz-actions {
      display: flex;
      gap: 0.5rem;
      flex-shrink: 0;
    }
    .btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s;
    }
    .btn-edit {
      background: #17a2b8;
      color: white;
    }
    .btn-edit:hover {
      background: #138496;
      transform: translateY(-1px);
    }
    .btn-delete {
      background: #dc3545;
      color: white;
    }
    .btn-delete:hover {
      background: #c82333;
      transform: translateY(-1px);
    }
    .btn-primary {
      background: linear-gradient(90deg, #43cea2 0%, #4a90e2 50%, #357ab8 100%);
      color: white;
      padding: 1rem 2rem;
      font-size: 1.1rem;
      margin-bottom: 2rem;
    }
    .btn-primary:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 8px 24px rgba(52, 152, 219, 0.18);
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
    .no-quizzes {
      text-align: center;
      padding: 3rem;
      color: #6c757d;
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
  <div class="content-container">
    <div class="page-header">
      <h1>Manage Quiz Questions</h1>
      <p>View, edit, and delete quiz questions</p>
    </div>

    <div style="text-align: center;">
      <a href="admin_add_quiz.php" class="btn btn-primary">Add New Question</a>
    </div>

    <?php if ($message): ?>
      <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <?php if ($questions && $questions->num_rows > 0): ?>
      <?php while ($question = $questions->fetch_assoc()): ?>
        <div class="quiz-card">
          <div class="quiz-header">
            <div class="quiz-info">
              <div class="course-topic">
                <strong><?php echo htmlspecialchars($question['course_name'] ?? 'General'); ?></strong> 
                → <?php echo htmlspecialchars($question['topic_title']); ?>
              </div>
              <div class="question-text">
                Q: <?php echo htmlspecialchars($question['question']); ?>
              </div>
            </div>
            <div class="quiz-actions">
              <a href="admin_edit_quiz.php?id=<?php echo $question['id']; ?>" class="btn btn-edit">Edit</a>
              <a href="?delete=<?php echo $question['id']; ?>" 
                 class="btn btn-delete" 
                 onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
            </div>
          </div>
          
          <div class="options-grid">
            <div class="option <?php echo ($question['correct_option'] == 'A') ? 'correct' : ''; ?>">
              <strong>A:</strong> <?php echo htmlspecialchars($question['option_a']); ?>
            </div>
            <div class="option <?php echo ($question['correct_option'] == 'B') ? 'correct' : ''; ?>">
              <strong>B:</strong> <?php echo htmlspecialchars($question['option_b']); ?>
            </div>
            <div class="option <?php echo ($question['correct_option'] == 'C') ? 'correct' : ''; ?>">
              <strong>C:</strong> <?php echo htmlspecialchars($question['option_c']); ?>
            </div>
            <div class="option <?php echo ($question['correct_option'] == 'D') ? 'correct' : ''; ?>">
              <strong>D:</strong> <?php echo htmlspecialchars($question['option_d']); ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="no-quizzes">
        <h3>No Quiz Questions Found</h3>
        <p>You haven't created any quiz questions yet.</p>
        <a href="admin_add_quiz.php" class="btn btn-primary">Create Your First Question</a>
      </div>
    <?php endif; ?>
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

<script>
// Add confirmation for delete actions
document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', function(e) {
    if (!confirm('Are you sure you want to delete this quiz question? This action cannot be undone.')) {
      e.preventDefault();
    }
  });
});
</script>

</body>
</html>
