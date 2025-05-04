<?php
session_start();
include("includes/db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Fetch available courses
$courses = $conn->query("SELECT id, title FROM courses");

// Learning styles
$styles = [
    'linguistic', 'logical-mathematical', 'spatial',
    'bodily-kinesthetic', 'musical', 'interpersonal',
    'intrapersonal', 'naturalist'
];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $style = $_POST['style'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $content_type = $_POST['content_type'];

    // Handle file upload
    $file_path = null;
    if (!empty($_FILES['topic_file']['name'])) {
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES["topic_file"]["name"]);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES["topic_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            $message = "File upload failed.";
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO topics (course_id, style, title, description, content_type, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $course_id, $style, $title, $description, $content_type, $file_path);

    if ($stmt->execute()) {
        $message = "Topic added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Topic - Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        form { max-width: 600px; margin: 2rem auto; padding: 1rem; border: 1px solid #ccc; border-radius: 10px; background: #f9f9f9; }
        label { display: block; margin-top: 1rem; }
        input, textarea, select { width: 100%; padding: 0.5rem; margin-top: 0.5rem; }
        button { margin-top: 1rem; padding: 0.5rem 1rem; background: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .message { text-align: center; padding: 1rem; color: green; }
    </style>
</head>
<body>

<header>
    <h1 style="text-align:center;">Add New Topic</h1>
    <nav style="text-align:center; margin-bottom: 1rem;">
        <a href="admin.php">Admin Dashboard</a> |
        <a href="logout.php">Logout</a>
    </nav>
</header>

<?php if ($message): ?>
    <p class="message"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label for="course_id">Select Course:</label>
    <select name="course_id" required>
        <option value="">-- Select Course --</option>
        <?php while ($row = $courses->fetch_assoc()): ?>
            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></option>
        <?php endwhile; ?>
    </select>

    <label for="style">Select Learning Style:</label>
    <select name="style" required>
        <option value="">-- Select Style --</option>
        <?php foreach ($styles as $style): ?>
            <option value="<?php echo $style; ?>"><?php echo ucwords(str_replace('-', ' ', $style)); ?></option>
        <?php endforeach; ?>
    </select>

    <label for="title">Topic Title:</label>
    <input type="text" name="title" required>

    <label for="description">Description:</label>
    <textarea name="description" rows="4" required></textarea>

    <label for="content_type">Content Type:</label>
    <select name="content_type" required>
        <option value="Article">Article</option>
        <option value="Quiz">Quiz</option>
        <option value="Video">Video</option>
        <option value="PDF">PDF</option>
    </select>

    <label for="topic_file">Upload File (optional):</label>
    <input type="file" name="topic_file" accept=".pdf,.mp4,.mov,.docx,.pptx,.zip">

    <button type="submit">Add Topic</button>
</form>

</body>
</html>
