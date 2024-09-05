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

// Delete the record from the followers table
$sql = "DELETE FROM followers WHERE follower_id = ? AND followed_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $currentUserId, $followedUserId);
$stmt->execute();

header("Location: explore.php"); // Redirect back to the explore page or wherever appropriate
