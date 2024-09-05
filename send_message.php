<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['receiver_id']) || !isset($_POST['message_text'])) {
    header("Location: login.html");
    exit;
}

$senderId = $_SESSION['user_id'];
$receiverId = $_POST['receiver_id'];
$messageText = trim($_POST['message_text']);

if ($messageText !== "") {
    // Insert the message into the database
    $sql = "INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $senderId, $receiverId, $messageText);
    $stmt->execute();

    $stmt->close();
}

$conn->close();
header("Location: messages.php?user_id=$receiverId");
?>
