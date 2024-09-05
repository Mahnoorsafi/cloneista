<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
    header("Location: login.html");
    exit;
}

$receiverId = $_GET['user_id'];
$senderId = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $receiverId);
$stmt->execute();
$userResult = $stmt->get_result();
$receiver = $userResult->fetch_assoc();
$stmt->close();

// Fetch messages
include 'fetch_messages.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages with <?php echo $receiver['username']; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h3>Chat with <?php echo $receiver['username']; ?></h3>
        <div class="messages-box">
            <?php while ($message = $messages->fetch_assoc()): ?>
                <div class="message <?php echo ($message['sender_id'] == $senderId) ? 'sent' : 'received'; ?>">
                    <p><?php echo $message['message_text']; ?></p>
                    <small><?php echo date('H:i', strtotime($message['created_at'])); ?></small>
                </div>
            <?php endwhile; ?>
        </div>
        
        <form action="send_message.php" method="POST">
            <input type="hidden" name="receiver_id" value="<?php echo $receiverId; ?>">
            <div class="form-group">
                <textarea name="message_text" class="form-control" placeholder="Type your message..." rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    </div>

    <style>
        .messages-box {
            border: 1px solid #ddd;
            padding: 15px;
            height: 300px;
            overflow-y: scroll;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 15px;
        }
        .message.sent {
            text-align: right;
        }
        .message.received {
            text-align: left;
        }
        .message p {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            max-width: 75%;
        }
    </style>
</body>
</html>
