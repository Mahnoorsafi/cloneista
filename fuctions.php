<?php
function hasLiked($userId, $postId, $conn) {
    // Prepare the SQL query to check if the user has liked the post
    $sql = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Return true if the user has liked the post, false otherwise
    return $result->num_rows > 0;
}
?>
