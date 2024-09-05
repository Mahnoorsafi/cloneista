<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$currentUserId = $_SESSION['user_id'];
$followedUserId = $_GET['user_id'];

// Insert a new record into the followers table
$sql = "INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $currentUserId, $followedUserId);
$stmt->execute();

header("Location: explore.php"); // Redirect back to the explore page or wherever appropriate
