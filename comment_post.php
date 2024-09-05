<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$userId = $_SESSION['user_id'];
$postId = $_POST['post_id'];
$commentText = $_POST['comment_text'];

// Validate and sanitize inputs
if (empty($commentText) || empty($postId)) {
    header("Location: profile.php?id=$userId");
    exit;
}

$sql = "INSERT INTO comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $postId, $userId, $commentText);

if ($stmt->execute()) {
    // Redirect back to the post page or profile page
    header("Location: profile.php?id=" . $userId);
} else {
    // Handle error
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
