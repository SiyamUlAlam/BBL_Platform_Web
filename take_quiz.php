<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$topic_id = $_GET['topic_id'] ?? null;
if (!$topic_id) {
    echo "Invalid quiz selection.";
    exit();
}

// Fetch topic and course info
$stmt = $conn->prepare("SELECT t.title as topic_title, c.title as course_title, c.id as course_id 
                        FROM topics t 
                        JOIN courses c ON t.course_id = c.id 
                        WHERE t.id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Quiz not found.";
    exit();
}
$quiz_info = $result->fetch_assoc();
$stmt->close();

// Fetch quiz questions
$stmt = $conn->prepare("SELECT id, question, option_a, option_b, option_c, option_d FROM quiz_questions WHERE topic_id = ? ORDER BY id");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = $questions_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($questions)) {
    echo "<p>No questions available for this quiz yet.</p>";
    echo "<a href='quizzes.php?course_id=" . $quiz_info['course_id'] . "'>Back to Quizzes</a>";
    exit();
}

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_answers = $_POST['answers'] ?? [];
    $score = 0;
    $total_questions = count($questions);
    
    // Calculate score
    foreach ($questions as $index => $question) {
        $question_id = $question['id'];
        $user_answer = $user_answers[$question_id] ?? '';
        
        // Get correct answer
        $stmt = $conn->prepare("SELECT correct_option FROM quiz_questions WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->bind_result($correct_option);
        $stmt->fetch();
        $stmt->close();
        
        if ($user_answer === $correct_option) {
            $score++;
        }
    }
    
    $percentage = ($score / $total_questions) * 100;
    
    // Save quiz attempt
    $stmt = $conn->prepare("INSERT INTO quiz_attempts (user_id, topic_id, score, total_questions, percentage) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiii", $_SESSION['user_id'], $topic_id, $score, $total_questions, $percentage);
    $stmt->execute();
    $attempt_id = $conn->insert_id;
    $stmt->close();
    
    // Save individual answers
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $user_answer = $user_answers[$question_id] ?? '';
        
        if ($user_answer) {
            // Get correct answer
            $stmt = $conn->prepare("SELECT correct_option FROM quiz_questions WHERE id = ?");
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $stmt->bind_result($correct_option);
            $stmt->fetch();
            $stmt->close();
            
            $is_correct = ($user_answer === $correct_option) ? 1 : 0;
            
            $stmt = $conn->prepare("INSERT INTO quiz_answers (attempt_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $attempt_id, $question_id, $user_answer, $is_correct);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Redirect to results page
    header("Location: quiz_results.php?attempt_id=" . $attempt_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz: <?php echo htmlspecialchars($quiz_info['topic_title']); ?></title>
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
        .main-wrapper {
            flex: 1 0 auto;
            padding: 2rem;
        }
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .quiz-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e7ef;
        }
        .quiz-header h1 {
            color: #357ab8;
            margin-bottom: 0.5rem;
        }
        .quiz-header p {
            color: #666;
            font-size: 1.1rem;
        }
        .question-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }
        .question-card:hover {
            border-color: #e0e7ef;
        }
        .question-number {
            background: #357ab8;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .question-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .options {
            display: grid;
            gap: 0.8rem;
        }
        .option {
            display: flex;
            align-items: center;
            padding: 0.8rem;
            border: 2px solid #e0e7ef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        .option:hover {
            border-color: #357ab8;
            background: #f0f7ff;
        }
        .option input[type="radio"] {
            margin-right: 0.8rem;
            transform: scale(1.2);
        }
        .option label {
            cursor: pointer;
            flex: 1;
            font-weight: 500;
        }
        .submit-section {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e0e7ef;
        }
        .submit-btn {
            background: linear-gradient(90deg, #43cea2 0%, #4a90e2 50%, #357ab8 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 16px rgba(52, 152, 219, 0.10);
        }
        .submit-btn:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 24px rgba(52, 152, 219, 0.18);
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
            transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
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
            <a href="dashboard.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
                <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
                <span>Brain-Based Learning</span>
            </a>
        </div>
        <nav class="nav-links">
            <a href="dashboard.php" class="nav-item">Dashboard</a>
            <a href="courses.php" class="nav-item">Courses</a>
            <a href="quizzes.php?course_id=<?php echo $quiz_info['course_id']; ?>" class="nav-item">Back to Quizzes</a>
            <a href="logout.php" class="nav-item">Logout</a>
        </nav>
    </div>
</header>

<div class="main-wrapper">
    <div class="quiz-container">
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($quiz_info['topic_title']); ?></h1>
            <p>Course: <?php echo htmlspecialchars($quiz_info['course_title']); ?></p>
            <p><strong><?php echo count($questions); ?> Questions</strong> | Select the best answer for each question</p>
        </div>

        <form method="POST" id="quizForm">
            <?php foreach ($questions as $index => $question): ?>
            <div class="question-card">
                <div class="question-number"><?php echo $index + 1; ?></div>
                <div class="question-text"><?php echo htmlspecialchars($question['question']); ?></div>
                <div class="options">
                    <div class="option">
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="A" id="q<?php echo $question['id']; ?>_a" required>
                        <label for="q<?php echo $question['id']; ?>_a">A. <?php echo htmlspecialchars($question['option_a']); ?></label>
                    </div>
                    <div class="option">
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="B" id="q<?php echo $question['id']; ?>_b">
                        <label for="q<?php echo $question['id']; ?>_b">B. <?php echo htmlspecialchars($question['option_b']); ?></label>
                    </div>
                    <div class="option">
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="C" id="q<?php echo $question['id']; ?>_c">
                        <label for="q<?php echo $question['id']; ?>_c">C. <?php echo htmlspecialchars($question['option_c']); ?></label>
                    </div>
                    <div class="option">
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="D" id="q<?php echo $question['id']; ?>_d">
                        <label for="q<?php echo $question['id']; ?>_d">D. <?php echo htmlspecialchars($question['option_d']); ?></label>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="submit-section">
                <button type="submit" class="submit-btn" onclick="return confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.');">
                    Submit Quiz
                </button>
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
