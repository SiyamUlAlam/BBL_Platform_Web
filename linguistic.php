<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    echo "Invalid course selection.";
    exit();
}

// Fetch course title
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$stmt->bind_result($course_title);
$stmt->fetch();
$stmt->close();

// Fetch linguistic topics
$stmt = $conn->prepare("SELECT title, content FROM topics WHERE course_id = ? AND style = 'linguistic'");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($course_title); ?> - Linguistic Learning</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1><?php echo htmlspecialchars($course_title); ?> - Linguistic Style</h1>
    <nav>
        <a href="explore.php?course_id=<?php echo $course_id; ?>">Back</a>
        <a href="dashboard.php">Dashboard</a>
    </nav>
</header>

<main style="padding: 2rem;">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div style="border:1px solid #ccc; padding:1rem; margin-bottom:1rem; border-radius:8px;">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No topics available yet for this style.</p>
    <?php endif; ?>
</main>

</body>
</html>
