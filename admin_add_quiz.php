<?php
session_start();
include("includes/db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$message = "";
$step = $_POST['step'] ?? $_GET['step'] ?? '1';

// Fetch courses for dropdown
$courses = $conn->query("SELECT id, title FROM courses ORDER BY title");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Step 1: Create topic if new topic is being created
    if (isset($_POST['create_topic'])) {
        $course_id = $_POST['course_id'] ?? '';
        $topic_title = $_POST['topic_title'] ?? '';
        $topic_description = $_POST['topic_description'] ?? '';
        
        if (!$course_id || !$topic_title) {
            $message = "Please fill in course and topic title.";
        } else {
            // Create new topic
            $stmt = $conn->prepare("INSERT INTO topics (course_id, style, title, description, content_type) VALUES (?, 'quizz', ?, ?, 'quiz')");
            $stmt->bind_param("iss", $course_id, $topic_title, $topic_description);
            
            if ($stmt->execute()) {
                $created_topic_id = $conn->insert_id;
                $message = "Quiz topic '{$topic_title}' created successfully! Now add questions below.";
                $step = '2';
                $_POST['topic_id'] = $created_topic_id;
            } else {
                $message = "Error creating topic: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    // Step 2: Add question to existing or newly created topic
    if (isset($_POST['add_question'])) {
        $topic_id = $_POST['topic_id'] ?? '';
        $question = $_POST['question'] ?? '';
        $option_a = $_POST['option_a'] ?? '';
        $option_b = $_POST['option_b'] ?? '';
        $option_c = $_POST['option_c'] ?? '';
        $option_d = $_POST['option_d'] ?? '';
        $correct_option = $_POST['correct_option'] ?? '';

        // Basic validation
        if (!$topic_id || !$question || !$option_a || !$option_b || !$option_c || !$option_d || !$correct_option) {
            $message = "Please fill in all question fields.";
        } else {
            // Insert question into database
            $stmt = $conn->prepare("INSERT INTO quiz_questions (topic_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $topic_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option);
            
            if ($stmt->execute()) {
                $message = "Quiz question added successfully! Add another question or finish.";
                // Clear only question fields, keep topic_id
                unset($_POST['question'], $_POST['option_a'], $_POST['option_b'], $_POST['option_c'], $_POST['option_d'], $_POST['correct_option']);
            } else {
                $message = "Error adding question: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch existing quiz topics for selection
$existing_topics = $conn->query("SELECT t.id, t.title, c.title as course_title FROM topics t LEFT JOIN courses c ON t.course_id = c.id WHERE t.style = 'quizz' ORDER BY c.title, t.title");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Quiz Question - Admin</title>
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
      max-width: 800px;
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
    .step-indicator {
      display: flex;
      justify-content: center;
      margin-bottom: 2rem;
      gap: 2rem;
    }
    .step {
      display: flex;
      align-items: center;
      padding: 0.5rem 1rem;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s;
    }
    .step.active {
      background: #357ab8;
      color: white;
    }
    .step.inactive {
      background: #e9ecef;
      color: #6c757d;
    }
    .step-number {
      background: white;
      color: #357ab8;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 0.5rem;
      font-size: 0.9rem;
      font-weight: bold;
    }
    .step.inactive .step-number {
      background: #6c757d;
      color: white;
    }
    .section {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border-left: 4px solid #357ab8;
    }
    .section h3 {
      margin-top: 0;
      color: #357ab8;
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      margin-bottom: 1rem;
    }
    @media (max-width: 600px) {
      .form-row {
        grid-template-columns: 1fr;
      }
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
      flex-wrap: wrap;
    }
    button, .btn {
      padding: 1rem 1.5rem;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
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
      flex: 1;
    }
    .btn-primary:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 8px 24px rgba(52, 152, 219, 0.18);
    }
    .btn-secondary {
      background: #6c757d;
      color: white;
      flex: 1;
    }
    .btn-secondary:hover {
      background: #5a6268;
      transform: translateY(-2px) scale(1.02);
    }
    .btn-success {
      background: #28a745;
      color: white;
      flex: 1;
    }
    .btn-success:hover {
      background: #218838;
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
    .topic-selector {
      display: flex;
      gap: 1rem;
      align-items: center;
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }
    .radio-group {
      display: flex;
      gap: 1rem;
      align-items: center;
      margin-bottom: 1rem;
    }
    .radio-option {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
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
      <a href="admin_add_quiz.php" class="nav-item active">Add Quiz</a>
      <a href="logout.php" class="nav-item">Logout</a>
    </nav>
  </div>
</header>

<div class="main-wrapper">
  <div class="form-container">
    <div class="form-header">
      <h1>BBL Quiz System</h1>
      <p></p>
    </div>

    <div class="step-indicator">
      <div class="step <?php echo ($step == '1') ? 'active' : 'inactive'; ?>">
        <div class="step-number">1</div>
        <span>Select/Create Topic</span>
      </div>
      <div class="step <?php echo ($step == '2') ? 'active' : 'inactive'; ?>">
        <div class="step-number">2</div>
        <span>Add Questions</span>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <!-- Step 1: Topic Selection/Creation -->
    <?php if ($step == '1'): ?>
      <div class="section">
        <h3>Choose Your Quiz Topic</h3>
        
        <div class="radio-group">
          <div class="radio-option">
            <input type="radio" id="existing_topic" name="topic_choice" value="existing" onchange="toggleTopicFields()" checked>
            <label for="existing_topic">Use Existing Quiz Topic</label>
          </div>
          <div class="radio-option">
            <input type="radio" id="new_topic" name="topic_choice" value="new" onchange="toggleTopicFields()">
            <label for="new_topic">Create New Quiz Topic</label>
          </div>
        </div>

        <!-- Existing Topic Selection -->
        <form method="POST" id="existing_topic_form">
          <input type="hidden" name="step" value="2">
          <div class="form-group" id="existing_topic_section">
            <label for="existing_topic_select">Select Existing Quiz Topic:</label>
            <select name="topic_id" id="existing_topic_select">
              <option value="">-- Select Quiz Topic --</option>
              <?php while ($topic = $existing_topics->fetch_assoc()): ?>
                <option value="<?php echo $topic['id']; ?>">
                  <?php echo htmlspecialchars($topic['course_title'] . ' - ' . $topic['title']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" id="continue_existing">Continue to Add Questions</button>
        </form>

        <!-- New Topic Creation -->
        <form method="POST" id="new_topic_form" style="display: none;">
          <div id="new_topic_section">
            <div class="form-group">
              <label for="course_id">Select Course:</label>
              <select name="course_id" id="course_id" required>
                <option value="">-- Select Course --</option>
                <?php 
                // Reset courses result
                $courses = $conn->query("SELECT id, title FROM courses ORDER BY title");
                while ($course = $courses->fetch_assoc()): ?>
                  <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="topic_title">Quiz Topic Title:</label>
              <input type="text" name="topic_title" id="topic_title" required placeholder="e.g., Mathematical Reasoning Quiz">
            </div>

            <div class="form-group">
              <label for="topic_description">Description (Optional):</label>
              <textarea name="topic_description" id="topic_description" placeholder="Brief description of this quiz topic..."></textarea>
            </div>
          </div>
          <button type="submit" name="create_topic" class="btn btn-success">Create Topic & Add Questions</button>
        </form>
      </div>
    <?php endif; ?>

    <!-- Step 2: Question Addition -->
    <?php if ($step == '2'): ?>
      <div class="section">
        <h3>Add Quiz Question</h3>
        <form method="POST">
          <input type="hidden" name="step" value="2">
          <input type="hidden" name="topic_id" value="<?php echo htmlspecialchars($_POST['topic_id'] ?? ''); ?>">

          <div class="form-group">
            <label for="question">Question:</label>
            <textarea name="question" id="question" required placeholder="Enter your question here..."><?php echo htmlspecialchars($_POST['question'] ?? ''); ?></textarea>
          </div>

          <div class="options-grid">
            <div class="form-group">
              <label for="option_a">Option A:</label>
              <input type="text" name="option_a" id="option_a" required placeholder="First option..." value="<?php echo htmlspecialchars($_POST['option_a'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label for="option_b">Option B:</label>
              <input type="text" name="option_b" id="option_b" required placeholder="Second option..." value="<?php echo htmlspecialchars($_POST['option_b'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label for="option_c">Option C:</label>
              <input type="text" name="option_c" id="option_c" required placeholder="Third option..." value="<?php echo htmlspecialchars($_POST['option_c'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label for="option_d">Option D:</label>
              <input type="text" name="option_d" id="option_d" required placeholder="Fourth option..." value="<?php echo htmlspecialchars($_POST['option_d'] ?? ''); ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="correct_option">Correct Answer:</label>
            <select name="correct_option" id="correct_option" required>
              <option value="">-- Select Correct Option --</option>
              <option value="A" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] == 'A') ? 'selected' : ''; ?>>A</option>
              <option value="B" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] == 'B') ? 'selected' : ''; ?>>B</option>
              <option value="C" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] == 'C') ? 'selected' : ''; ?>>C</option>
              <option value="D" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] == 'D') ? 'selected' : ''; ?>>D</option>
            </select>
          </div>

          <div class="btn-group">
            <button type="submit" name="add_question" class="btn btn-primary">Add Question</button>
            <a href="admin_add_quiz.php" class="btn btn-secondary">Add Another Topic</a>
            <a href="admin_manage_quizzes.php" class="btn btn-success">View All Questions</a>
          </div>
        </form>
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
function toggleTopicFields() {
  const existingRadio = document.getElementById('existing_topic');
  const newRadio = document.getElementById('new_topic');
  const existingForm = document.getElementById('existing_topic_form');
  const newForm = document.getElementById('new_topic_form');
  
  if (existingRadio.checked) {
    existingForm.style.display = 'block';
    newForm.style.display = 'none';
  } else {
    existingForm.style.display = 'none';
    newForm.style.display = 'block';
  }
}
</script>

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
