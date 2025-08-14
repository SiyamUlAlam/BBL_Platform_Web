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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $course_id = $_POST['course'] ?? '';
    $style = $_POST['style'] ?? '';
    $content_type = $_POST['content_type'] ?? '';
    $file_path = $topic['file_path']; // keep old file path by default

    // Validate required fields
    if (!$title || !$course_id || !$style || !$content_type) {
        $error = "Please fill in all required fields.";
    } else {
        // Handle file upload if new file is selected
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/topics/';
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
                $error = "Failed to upload file.";
            }
        }

        if (!isset($error)) {
            // Update database
            $stmt = $conn->prepare("UPDATE topics SET title = ?, course_id = ?, style = ?, content_type = ?, file_path = ? WHERE id = ?");
            $stmt->bind_param("sisssi", $title, $course_id, $style, $content_type, $file_path, $topic_id);
            if ($stmt->execute()) {
                header("Location: admin_manage_topics.php?msg=updated");
                exit();
            } else {
                $error = "Database update failed.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Topic</title>
<style>
  label { display: block; margin-top: 1rem; }
  input[type=text], select { width: 300px; padding: 0.5rem; }
  .button { margin-top: 1rem; padding: 0.5rem 1rem; background-color: #007bff; color: white; border: none; cursor: pointer; }
  .error { color: red; margin-top: 1rem; }
</style>
</head>
<body>

<h1>Edit Topic</h1>
<nav>
  <a href="admin_manage_topic.php">Back to Manage Topics</a>
</nav>

<?php if (isset($error)): ?>
  <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <label>
    Title:<br>
    <input type="text" name="title" value="<?= htmlspecialchars($topic['title']) ?>" required>
  </label>

  <label>
    Course:<br>
    <select name="course" required>
      <option value="">-- Select Course --</option>
      <?php while ($course = $course_options->fetch_assoc()): ?>
        <option value="<?= $course['id'] ?>" <?= ($course['id'] == $topic['course_id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($course['title']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </label>

  <label>
    Style:<br>
    <select name="style" required>
      <option value="">-- Select Style --</option>
      <?php foreach ($styles as $s): ?>
        <option value="<?= $s ?>" <?= ($s == $topic['style']) ? 'selected' : '' ?>>
          <?= ucwords(str_replace('-', ' ', $s)) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>
    Content Type:<br>
    <select name="content_type" required>
      <option value="">-- Select Type --</option>
      <?php foreach ($content_types as $ct): ?>
        <option value="<?= $ct ?>" <?= ($ct == $topic['content_type']) ? 'selected' : '' ?>>
          <?= ucfirst($ct) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>
    Current File:<br>
    <?php if (!empty($topic['file_path'])): ?>
      <a href="<?= htmlspecialchars($topic['file_path']) ?>" target="_blank">View File</a>
    <?php else: ?>
      No file uploaded
    <?php endif; ?>
  </label>

  <label>
    Upload New File (optional):<br>
    <input type="file" name="file" accept=".pdf,.doc,.docx,.mp4,.avi,.mov">
  </label>

  <button type="submit" class="button">Update Topic</button>
</form>

</body>
</html>
