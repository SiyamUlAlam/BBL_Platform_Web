<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$topic_id = $_GET['id'] ?? null;

if (!$topic_id) {
    echo "Invalid topic ID.";
    exit();
}

// Fetch existing topic
$stmt = $conn->prepare("SELECT * FROM topics WHERE id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$topic = $result->fetch_assoc();
$stmt->close();

if (!$topic) {
    echo "Topic not found.";
    exit();
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $content_type = $_POST['content_type'];

    // File upload handling (optional)
    $file_path = $topic['file_path'];
    if (!empty($_FILES['file']['name'])) {
        $upload_dir = "uploads/";
        $file_name = basename($_FILES['file']['name']);
        $target_file = $upload_dir . time() . "_" . $file_name;
        move_uploaded_file($_FILES['file']['tmp_name'], $target_file);
        $file_path = $target_file;
    }

    $stmt = $conn->prepare("UPDATE topics SET title = ?, description = ?, content_type = ?, file_path = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $description, $content_type, $file_path, $topic_id);

    if ($stmt->execute()) {
        header("Location: admin_manage_topics.php?message=updated");
        exit();
    } else {
        echo "Error updating topic.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Topic</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <h1>Edit Topic</h1>
  <form action="" method="POST" enctype="multipart/form-data">
    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo htmlspecialchars($topic['title']); ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" rows="5" required><?php echo htmlspecialchars($topic['description']); ?></textarea><br><br>

    <label>Content Type:</label><br>
    <select name="content_type" required>
      <option value="article" <?php if ($topic['content_type'] == 'article') echo 'selected'; ?>>Article</option>
      <option value="quiz" <?php if ($topic['content_type'] == 'quiz') echo 'selected'; ?>>Quiz</option>
      <option value="video" <?php if ($topic['content_type'] == 'video') echo 'selected'; ?>>Video</option>
    </select><br><br>

    <label>Replace File (Optional):</label><br>
    <input type="file" name="file"><br><br>

    <?php if (!empty($topic['file_path'])): ?>
      <p>Current File: <a href="<?php echo htmlspecialchars($topic['file_path']); ?>" target="_blank">View</a></p>
    <?php endif; ?>

    <button type="submit">Update Topic</button>
  </form>
  <p><a href="admin_manage_topics.php">Back to Manage Topics</a></p>
</body>
</html>
