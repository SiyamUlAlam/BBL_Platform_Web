<?php
session_start();
include("includes/db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$message = "";
$action = $_GET['action'] ?? 'overview';

// Fetch available courses
$courses = $conn->query("SELECT id, title FROM courses");

// Handle topic creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_topic'])) {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO topics (course_id, style, title, description, content_type) VALUES (?, 'quizz', ?, ?, 'quiz')");
    $stmt->bind_param("iss", $course_id, $title, $description);
    
    if ($stmt->execute()) {
        $message = "Quiz topic created successfully!";
    } else {
        $message = "Error creating topic: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing quiz topics
$quiz_topics = $conn->query("SELECT t.id, t.title, t.description, c.title as course_title, 
                            (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.topic_id = t.id) as question_count
                            FROM topics t 
                            LEFT JOIN courses c ON t.course_id = c.id 
                            WHERE t.style = 'quizz' 
                            ORDER BY c.title, t.title");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quiz Management - Admin</title>
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
    .tabs {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      border-bottom: 2px solid #e9ecef;
    }
    .tab {
      padding: 1rem 2rem;
      background: none;
      border: none;
      border-bottom: 3px solid transparent;
      cursor: pointer;
      font-weight: 600;
      color: #6c757d;
      text-decoration: none;
      transition: all 0.3s;
    }
    .tab.active {
      color: #357ab8;
      border-bottom-color: #357ab8;
    }
    .tab:hover {
      color: #357ab8;
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
    .workflow-steps {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
      border-left: 4px solid #357ab8;
    }
    .step {
      display: flex;
      align-items: center;
      margin-bottom: 1rem;
      font-weight: 600;
    }
    .step-number {
      background: #357ab8;
      color: white;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
      font-size: 0.9rem;
    }
    .form-container {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
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
    .btn {
      padding: 0.8rem 1.5rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s;
      margin-right: 1rem;
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
      transform: translateY(-2px);
    }
    .topic-card {
      background: white;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      transition: all 0.3s;
    }
    .topic-card:hover {
      border-color: #357ab8;
      box-shadow: 0 4px 16px rgba(53, 122, 184, 0.1);
    }
    .topic-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }
    .topic-info h3 {
      color: #357ab8;
      margin: 0 0 0.5rem 0;
    }
    .topic-meta {
      color: #6c757d;
      font-size: 0.9rem;
    }
    .topic-actions {
      display: flex;
      gap: 0.5rem;
      align-items: flex-start;
    }
    .question-count {
      background: #e7f3ff;
      color: #357ab8;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
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
    .empty-state {
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
      <a href="admin_quiz_management.php" class="nav-item active">Quiz</a>
      <a href="logout.php" class="nav-item">Logout</a>
    </nav>
  </div>
</header>

<div class="main-wrapper">
  <div class="content-container">
    <div class="page-header">
      <h1>Quiz Management</h1>
      <p>Create quiz topics and manage quiz questions</p>
    </div>

    <div class="workflow-steps">
      <h3>Quick Actions</h3>
      <div style="display: flex; gap: 1rem; justify-content: center; margin: 1rem 0;">
        <a href="admin_add_quiz.php" class="btn btn-primary">📝 Add Questions</a>
        <!-- <a href="admin_manage_quizzes.php" class="btn btn-secondary">👁️ View All Questions</a> -->
      </div>
    </div>

    <div class="tabs">
      <a href="#" class="tab active" onclick="showTab('topics')">Quiz Topics</a>
      <a href="#" class="tab" onclick="showTab('create')">Create New Topic</a>
    </div>

    <?php if ($message): ?>
      <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <!-- Quiz Topics Tab -->
    <div id="topics-tab" class="tab-content active">
      <h3>Existing Quiz Topics</h3>
      
      <?php if ($quiz_topics && $quiz_topics->num_rows > 0): ?>
        <?php while ($topic = $quiz_topics->fetch_assoc()): ?>
          <div class="topic-card">
            <div class="topic-header">
              <div class="topic-info">
                <h3><?php echo htmlspecialchars($topic['title']); ?></h3>
                <div class="topic-meta">
                  Course: <?php echo htmlspecialchars($topic['course_title'] ?? 'General'); ?> | 
                  Questions: <span class="question-count"><?php echo $topic['question_count']; ?> questions</span>
                </div>
                <?php if ($topic['description']): ?>
                  <p style="margin-top: 0.5rem; color: #666;"><?php echo htmlspecialchars($topic['description']); ?></p>
                <?php endif; ?>
              </div>
              <div class="topic-actions">
                <?php if ($topic['question_count'] == 0): ?>
                  <a href="admin_add_quiz.php?topic_id=<?php echo $topic['id']; ?>" class="btn btn-primary">Add Questions</a>
                <?php else: ?>
                  <a href="admin_add_quiz.php?topic_id=<?php echo $topic['id']; ?>" class="btn btn-secondary">Add More</a>
                  <a href="admin_manage_quizzes.php" class="btn btn-primary">Manage Questions</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <h3>No Quiz Topics Found</h3>
          <p>Create your first quiz topic to get started!</p>
          <button class="btn btn-primary" onclick="showTab('create')">Create Quiz Topic</button>
        </div>
      <?php endif; ?>
    </div>

    <!-- Create Topic Tab -->
    <div id="create-tab" class="tab-content">
      <div class="form-container">
        <h3>Create New Quiz Topic</h3>
        <form method="POST">
          <div class="form-group">
            <label for="course_id">Select Course:</label>
            <select name="course_id" id="course_id" required>
              <option value="">-- Select Course --</option>
              <?php 
              // Reset courses result
              $courses = $conn->query("SELECT id, title FROM courses");
              while ($course = $courses->fetch_assoc()): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="title">Quiz Topic Title:</label>
            <input type="text" name="title" id="title" required placeholder="e.g., Mathematical Reasoning Quiz">
          </div>

          <div class="form-group">
            <label for="description">Description (Optional):</label>
            <textarea name="description" id="description" placeholder="Brief description of this quiz topic..."></textarea>
          </div>

          <button type="submit" name="create_topic" class="btn btn-primary">Create Quiz Topic</button>
        </form>
      </div>
    </div>

  </div>
</div>

<a class="side-nav-btn left" href="#" onclick="history.back(); return false;" title="Go back">
  <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
</a>
<a class="side-nav-btn right" href="#" onclick="history.forward(); return false;" title="Go forward">
  <svg viewBox="0 0 24 24" style="transform: scaleX(-1)"><path d="M15 18l-6-6 6-6"/></svg>
</a>

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

<script>
function showTab(tabName) {
  // Hide all tab contents
  document.querySelectorAll('.tab-content').forEach(content => {
    content.classList.remove('active');
  });
  
  // Remove active class from all tabs
  document.querySelectorAll('.tab').forEach(tab => {
    tab.classList.remove('active');
  });
  
  // Show selected tab content
  document.getElementById(tabName + '-tab').classList.add('active');
  
  // Add active class to clicked tab
  event.target.classList.add('active');
}

// Add click handlers to tabs
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', function(e) {
    e.preventDefault();
  });
});
</script>

</body>
</html>
