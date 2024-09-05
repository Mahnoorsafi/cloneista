<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['followed_id'])) {
    header("Location: login.html");
    exit;
}

$userId = $_SESSION['user_id'];
$followedId = $_POST['followed_id'];

// Check if the user is already following the target user
$sql = "SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $followedId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Unfollow the user
    $sql = "DELETE FROM followers WHERE follower_id = ? AND followed_id = ?";
} else {
    // Follow the user
    $sql = "INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $followedId);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: ../server/profile.php");
?>
