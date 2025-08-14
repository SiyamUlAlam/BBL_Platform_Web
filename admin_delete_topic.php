<?php
session_start();
include("includes/db.php");

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Get topic ID from query parameter
$topic_id = $_GET['id'] ?? null;

if (!$topic_id) {
    // No topic ID provided, redirect back with error
    header("Location: admin_manage_topic.php?msg=invalid_id");
    exit();
}

// Prepare delete statement
$stmt = $conn->prepare("DELETE FROM topics WHERE id = ?");
$stmt->bind_param("i", $topic_id);

if ($stmt->execute()) {
    // Successfully deleted
    header("Location: admin_manage_topic.php?msg=deleted");
    exit();
} else {
    // Error occurred
    header("Location: admin_manage_topic.php?msg=error");
    exit();
}
?>
