<?php
// Function to check if the current user is following another user
function isFollowing($followerId, $followedId) {
    global $conn;
    $sql = "SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $followerId, $followedId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
?>
