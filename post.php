<?php
function displayPostWithComments($conn, $postId, $userId) {
    // Fetch the post
    $sql = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $postResult = $stmt->get_result();
    $post = $postResult->fetch_assoc();
    $stmt->close();

    if ($post) {
        // Display the post
        echo '<div class="col-md-4 mb-4">';
        echo '<img src="../uploads/' . htmlspecialchars($post['image_url']) . '" class="img-fluid post-img" alt="Post Image">';
        echo '<p>' . htmlspecialchars($post['caption']) . '</p>';

        // Like Button
        echo '<form action="like_post.php" method="POST" class="d-inline">';
        echo '<input type="hidden" name="post_id" value="' . htmlspecialchars($post['id']) . '">';
        echo '<button type="submit" class="btn btn-outline-primary">';
        $sql = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $post['id']);
        $stmt->execute();
        $likeResult = $stmt->get_result();
        echo ($likeResult->num_rows > 0) ? "Unlike" : "Like";
        $stmt->close();
        echo '</button>';
        echo '</form>';

        // Comment Form
        echo '<form action="comment_post.php" method="POST" class="mt-2">';
        echo '<input type="hidden" name="post_id" value="' . htmlspecialchars($post['id']) . '">';
        echo '<input type="hidden" name="redirect_to" value="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">';
        echo '<div class="mb-3">';
        echo '<textarea name="comment_text" class="form-control" placeholder="Write a comment..." rows="2"></textarea>';
        echo '</div>';
        echo '<button type="submit" class="btn btn-primary">Comment</button>';
        echo '</form>';

        // Display Comments
        echo '<div class="mt-2">';
        $sql = "SELECT comments.comment_text, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $post['id']);
        $stmt->execute();
        $commentResult = $stmt->get_result();
        while ($comment = $commentResult->fetch_assoc()) {
            echo '<p><strong>' . htmlspecialchars($comment['username']) . ':</strong> ' . htmlspecialchars($comment['comment_text']) . '</p>';
        }
        $stmt->close();
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p>Post not found.</p>';
    }
}
?>
