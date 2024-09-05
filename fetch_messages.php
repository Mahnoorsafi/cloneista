<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    header("Location: login.html");
    exit;
}

$senderId = $_SESSION['user_id'];
$receiverId = $_GET['user_id'];

// Fetch messages between the logged-in user and the selected user
$sql = "SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $senderId, $receiverId, $receiverId, $senderId);
$stmt->execute();
$messages = $stmt->get_result();

$stmt->close();
$conn->close();
?>
