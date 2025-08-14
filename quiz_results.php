<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$attempt_id = $_GET['attempt_id'] ?? null;
if (!$attempt_id) {
    echo "Invalid quiz attempt.";
    exit();
}

// Fetch quiz attempt details
$stmt = $conn->prepare("SELECT qa.*, t.title as topic_title, c.title as course_title, c.id as course_id 
                        FROM quiz_attempts qa 
                        JOIN topics t ON qa.topic_id = t.id 
                        JOIN courses c ON t.course_id = c.id 
                        WHERE qa.id = ? AND qa.user_id = ?");
$stmt->bind_param("ii", $attempt_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Quiz attempt not found.";
    exit();
}
$attempt = $result->fetch_assoc();
$stmt->close();

// Fetch detailed answers
$stmt = $conn->prepare("SELECT qans.*, qq.question, qq.option_a, qq.option_b, qq.option_c, qq.option_d, qq.correct_option 
                        FROM quiz_answers qans 
                        JOIN quiz_questions qq ON qans.question_id = qq.id 
                        WHERE qans.attempt_id = ? 
                        ORDER BY qq.id");
$stmt->bind_param("i", $attempt_id);
$stmt->execute();
$answers_result = $stmt->get_result();
$answers = $answers_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Results - <?php echo htmlspecialchars($attempt['topic_title']); ?></title>
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
            transition: all 0.3s;
        }
        .nav-item:hover {
            background: #fff;
            color: #357ab8;
        }
        .main-wrapper {
            flex: 1 0 auto;
            padding: 2rem;
        }
        .results-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .results-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e7ef;
        }
        .score-display {
            background: linear-gradient(120deg, #e6f9f0 60%, #eaf6fb 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .score-main {
            font-size: 3rem;
            font-weight: 900;
            color: #357ab8;
            margin-bottom: 0.5rem;
        }
        .score-details {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 1rem;
        }
        .score-percentage {
            font-size: 1.5rem;
            font-weight: 700;
            color: #27ae60;
        }
        .question-review {
            margin-bottom: 1.5rem;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #e0e7ef;
        }
        .question-header {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .question-body {
            padding: 1.5rem;
        }
        .answer-option {
            padding: 0.5rem 0;
            margin: 0.3rem 0;
        }
        .correct-answer {
            color: #27ae60;
            font-weight: 600;
        }
        .wrong-answer {
            color: #e74c3c;
            font-weight: 600;
        }
        .user-answer {
            background: #fff3cd;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
        }
        .correct-indicator {
            background: #d4edda;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            border-left: 4px solid #28a745;
        }
        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        .correct-icon {
            background: #28a745;
        }
        .wrong-icon {
            background: #dc3545;
        }
        .action-buttons {
            text-align: center;
            margin-top: 2rem;
            gap: 1rem;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #357ab8;
            color: white;
        }
        .btn-primary:hover {
            background: #2c6ba0;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
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
            <a href="dashboard.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
                <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
                <span>Brain-Based Learning</span>
            </a>
        </div>
        <nav class="nav-links">
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="courses.php" class="nav-item">Courses</a>
            <a href="quizzes.php?course_id=<?php echo $attempt['course_id']; ?>" class="nav-item">Back to Quizzes</a>
            <a href="logout.php" class="nav-item">Logout</a>
        </nav>
    </div>
</header>

<div class="main-wrapper">
    <div class="results-container">
        <div class="results-header">
            <h1>Quiz Results</h1>
            <h2><?php echo htmlspecialchars($attempt['topic_title']); ?></h2>
            <p>Course: <?php echo htmlspecialchars($attempt['course_title']); ?></p>
        </div>

        <div class="score-display">
            <div class="score-main"><?php echo $attempt['score']; ?>/<?php echo $attempt['total_questions']; ?></div>
            <div class="score-details">You got <?php echo $attempt['score']; ?> out of <?php echo $attempt['total_questions']; ?> questions correct</div>
            <div class="score-percentage"><?php echo number_format($attempt['percentage'], 1); ?>%</div>
        </div>

        <h3>Question Review</h3>
        <?php foreach ($answers as $index => $answer): ?>
        <div class="question-review">
            <div class="question-header">
                <span class="status-icon <?php echo $answer['is_correct'] ? 'correct-icon' : 'wrong-icon'; ?>">
                    <?php echo $answer['is_correct'] ? '✓' : '✗'; ?>
                </span>
                <span>Question <?php echo $index + 1; ?></span>
            </div>
            <div class="question-body">
                <p><strong><?php echo htmlspecialchars($answer['question']); ?></strong></p>
                
                <div class="answer-option">
                    <strong>Your Answer:</strong> 
                    <span class="user-answer">
                        <?php echo $answer['selected_option']; ?>. 
                        <?php echo htmlspecialchars($answer['option_' . strtolower($answer['selected_option'])]); ?>
                    </span>
                </div>
                
                <?php if (!$answer['is_correct']): ?>
                <div class="answer-option">
                    <strong>Correct Answer:</strong> 
                    <span class="correct-indicator">
                        <?php echo $answer['correct_option']; ?>. 
                        <?php echo htmlspecialchars($answer['option_' . strtolower($answer['correct_option'])]); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="action-buttons">
            <a href="quizzes.php?course_id=<?php echo $attempt['course_id']; ?>" class="btn btn-primary">Back to Quizzes</a>
            <a href="take_quiz.php?topic_id=<?php echo $attempt['topic_id']; ?>" class="btn btn-secondary">Retake Quiz</a>
            <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</div>

<footer class="footer">
  <div class="footer-content">
    <div>
      <strong>&copy; 2025 Brain-Based Learning Platform</strong><br>
      Empowering learners with science-backed education.
    </div>
    <div>
      <strong>Quick Links</strong><br>
      <a href="dashboard.php">Dashboard</a>
      <a href="courses.php">Courses</a>
      <a href="explore.php?course_id=1">Explore</a>
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
</html>
