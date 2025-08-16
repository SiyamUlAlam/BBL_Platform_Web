<?php
session_start();
include("includes/db.php");

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Get topic ID from URL
$topic_id = $_GET['id'] ?? null;
if (!$topic_id) {
    header("Location: admin_manage_topic.php?msg=invalid_id");
    exit();
}

// Fetch topic data
$stmt = $conn->prepare("SELECT * FROM topics WHERE id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin_manage_topic.php?msg=not_found");
    exit();
}
$topic = $result->fetch_assoc();

// Fetch courses for dropdown
$course_options = $conn->query("SELECT id, title FROM courses");

// Styles and content types
$styles = ['linguistic','logical-mathematical','spatial','bodily-kinesthetic','musical','interpersonal','intrapersonal','naturalist'];
$content_types = ['article', 'quiz', 'video'];

$message = "";
$message_type = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $course_id = $_POST['course'] ?? '';
    $style = $_POST['style'] ?? '';
    $content_type = $_POST['content_type'] ?? '';
    $description = $_POST['description'] ?? '';
    $file_path = $topic['file_path']; // keep old file path by default

    // Validate required fields
    if (!$title || !$course_id || !$style || !$content_type) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } else {
        // Handle file upload if new file is selected
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = basename($_FILES['file']['name']);
            $target_file = $upload_dir . time() . '_' . $filename;

            // Move uploaded file
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                // Optionally delete old file if exists and different
                if (!empty($file_path) && file_exists($file_path) && $file_path !== $target_file) {
                    unlink($file_path);
                }
                $file_path = $target_file;
            } else {
                $message = "Failed to upload file.";
                $message_type = "error";
            }
        }

        if (!$message) {
            // Update database
            $stmt = $conn->prepare("UPDATE topics SET title = ?, course_id = ?, style = ?, content_type = ?, file_path = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sissssi", $title, $course_id, $style, $content_type, $file_path, $description, $topic_id);
            if ($stmt->execute()) {
                $message = "Topic updated successfully!";
                $message_type = "success";
                
                // Refresh topic data
                $stmt = $conn->prepare("SELECT * FROM topics WHERE id = ?");
                $stmt->bind_param("i", $topic_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $topic = $result->fetch_assoc();
            } else {
                $message = "Database update failed.";
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Topic - Admin</title>
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
      max-width: 800px;
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
    .page-header h1 {
      margin: 0 0 0.5rem 0;
      font-size: 2rem;
      font-weight: 700;
    }
    .page-header p {
      margin: 0;
      color: #6c757d;
      font-size: 1.1rem;
    }
    .breadcrumb {
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: #6c757d;
    }
    .breadcrumb a {
      color: #357ab8;
      text-decoration: none;
      font-weight: 600;
    }
    .breadcrumb a:hover {
      text-decoration: underline;
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
    input[type="text"], textarea, select, input[type="file"] {
      width: 100%;
      padding: 0.8rem;
      border: 2px solid #e0e7ef;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }
    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: #357ab8;
      box-shadow: 0 0 0 3px rgba(53, 122, 184, 0.1);
    }
    textarea {
      min-height: 100px;
      resize: vertical;
    }
    .btn {
      display: inline-block;
      padding: 0.8rem 2rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s;
      font-size: 1rem;
      font-family: 'Inter', sans-serif;
    }
    .btn-primary {
      background: linear-gradient(135deg, #357ab8, #4a90e2);
      color: white;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #2c5f8a, #357ab8);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(53, 122, 184, 0.3);
    }
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    .btn-secondary:hover {
      background: #545b62;
      transform: translateY(-2px);
    }
    .message {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
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
    .file-info {
      background: #e9ecef;
      padding: 1rem;
      border-radius: 8px;
      margin-top: 0.5rem;
    }
    .file-info a {
      color: #357ab8;
      text-decoration: none;
      font-weight: 600;
    }
    .file-info a:hover {
      text-decoration: underline;
    }
    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      margin-top: 2rem;
      padding-top: 2rem;
      border-top: 2px solid #e9ecef;
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
    @media (max-width: 768px) {
      .navbar {
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem;
      }
      .nav-links {
        width: 100%;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1rem;
      }
      .main-wrapper {
        padding: 1rem;
      }
      .form-actions {
        flex-direction: column;
      }
      .side-nav-btn.left { left: 6px; }
      .side-nav-btn.right { right: 6px; }
      .side-nav-btn { width: 38px; height: 38px; }
      .side-nav-btn svg { width: 20px; height: 20px; }
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
      <a href="dashboard.php" class="nav-item">User Dashboard</a>
      <a href="admin.php" class="nav-item">Admin Dashboard</a>
      <a href="admin_manage_topic.php" class="nav-item active">Manage Topics</a>
      <a href="logout.php" class="nav-item">Logout</a>
    </nav>
  </div>
</header>

<div class="main-wrapper">
  <div class="content-container">
    
    <div class="page-header">
      <h1>Edit Topic</h1>
      <p>Update topic information and content</p>
    </div>

    <div class="breadcrumb">
      <a href="admin.php">Admin Dashboard</a>
      <span>›</span>
      <a href="admin_manage_topic.php">Manage Topics</a>
      <span>›</span>
      <span>Edit Topic</span>
    </div>

    <?php if ($message): ?>
      <div class="message <?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <div class="form-container">
      <form method="post" enctype="multipart/form-data">
        
        <div class="form-group">
          <label for="title">Topic Title</label>
          <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($topic['title']); ?>" required placeholder="Enter topic title">
        </div>

        <div class="form-group">
          <label for="course">Course</label>
          <select id="course" name="course" required>
            <option value="">-- Select Course --</option>
            <?php 
            // Reset courses result for re-iteration
            $course_options = $conn->query("SELECT id, title FROM courses");
            while ($course = $course_options->fetch_assoc()): ?>
              <option value="<?php echo $course['id']; ?>" <?php echo ($course['id'] == $topic['course_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($course['title']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="style">Learning Style</label>
          <select id="style" name="style" required>
            <option value="">-- Select Learning Style --</option>
            <?php foreach ($styles as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo ($s == $topic['style']) ? 'selected' : ''; ?>>
                <?php echo ucwords(str_replace('-', ' ', $s)); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="content_type">Content Type</label>
          <select id="content_type" name="content_type" required>
            <option value="">-- Select Content Type --</option>
            <?php foreach ($content_types as $ct): ?>
              <option value="<?php echo $ct; ?>" <?php echo ($ct == $topic['content_type']) ? 'selected' : ''; ?>>
                <?php echo ucfirst($ct); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="description">Description (Optional)</label>
          <textarea id="description" name="description" placeholder="Enter topic description..."><?php echo htmlspecialchars($topic['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
          <label>Current File</label>
          <?php if (!empty($topic['file_path'])): ?>
            <div class="file-info">
              <strong>File:</strong> <a href="<?php echo htmlspecialchars($topic['file_path']); ?>" target="_blank">View Current File</a>
            </div>
          <?php else: ?>
            <div class="file-info">
              <em>No file currently uploaded</em>
            </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="file">Upload New File (Optional)</label>
          <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.mp4,.avi,.mov,.txt">
          <small style="color: #6c757d; font-size: 0.9rem; margin-top: 0.5rem; display: block;">
            Supported formats: PDF, DOC, DOCX, MP4, AVI, MOV, TXT
          </small>
        </div>

        <div class="form-actions">
          <a href="admin_manage_topic.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Update Topic</button>
        </div>

      </form>
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

</body>
</html>
