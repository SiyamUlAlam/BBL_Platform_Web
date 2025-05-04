<?php
session_start();
include("includes/db.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Get topic ID from query
$topic_id = $_GET['id'] ?? null;

if (!$topic_id) {
    echo "Invalid topic ID.";
    exit();
}

// Delete file from server if it exists
$stmt = $conn->prepare("SELECT file_path FROM topics WHERE id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$stmt->bind_result($file_path);
$stmt->fetch();
$stmt->close();

if ($file_path && file_exists($file_path)) {
    unlink($file_path); // Remove file
}

// Delete topic from database
$stmt = $conn->prepare("DELETE FROM topics WHERE id = ?");
$stmt->bind_param("i", $topic_id);
if ($stmt->execute()) {
    header("Location: admin_manage_topics.php?message=Topic+deleted+successfully");
} else {
    echo "Error deleting topic.";
}
$stmt->close();
?>
