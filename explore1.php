<?php
// Fetching course id from URL
$course_id = $_GET['course_id'];

// Fetch the related content for each learning style from the database
$query = "SELECT * FROM activities WHERE course_id = ? ORDER BY learning_style";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

// Display content
while ($row = $result->fetch_assoc()) {
    echo "<div class='learning-style'>";
    echo "<h3>" . htmlspecialchars($row['learning_style']) . "</h3>";
    echo "<h4>" . htmlspecialchars($row['title']) . "</h4>";
    echo "<p>" . htmlspecialchars($row['description']) . "</p>";
    if ($row['link']) {
        echo "<a href='" . htmlspecialchars($row['link']) . "'>View Resource</a>";
    }
    echo "</div>";
}
?>
