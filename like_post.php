<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    header("Location: login.html");
    exit;
}

$userId = $_SESSION['user_id'];
$postId = $_POST['post_id'];

// Check if the user has already liked the post
$sql = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $postId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Unlike the post
    $sql = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
} else {
    // Like the post
    $sql = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $postId);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: profile.php");
?>
