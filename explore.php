<?php
session_start();
require 'db.php';
require 'header.php';
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$currentUserId = $_SESSION['user_id']; // Retrieve the current user ID from the session

// Prepare SQL query to get popular posts or posts from followed users
$sql = "SELECT posts.*, users.username, users.profile_picture, 
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->execute();
$postsResult = $stmt->get_result();

// Check if the current user is following a specific user
function isFollowing($followerId, $followedId) {
    global $conn;
    $sql = "SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $followerId, $followedId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Check if the current user has liked a specific post
function hasLiked($userId, $postId) {
    global $conn;
    $sql = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Explore</h2>

    <!-- Search Bar -->
    <div class="search-section mb-4">
        <form action="search.php" method="GET" class="form-inline">
            <input type="text" name="query" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>" required>
            <button type="submit" class="btn btn-primary ml-2">Search</button>
        </form>
    </div>

    <!-- Posts Section -->
    <?php if ($postsResult->num_rows > 0): ?>
        <div class="row">
            <?php while ($post = $postsResult->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <a href="profile.php?user_id=<?php echo $post['user_id']; ?>">
                            <img src="../uploads/<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="Post Image">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['username']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($post['caption']); ?></p>

                            <!-- Like Button and Like Count -->
                            <form action="like_post.php" method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="btn btn-link p-0">
                                    <?php if (hasLiked($currentUserId, $post['id'])): ?>
                                        <span>&#128077;</span> Unlike
                                    <?php else: ?>
                                        <span>&#128077;</span> Like
                                    <?php endif; ?>
                                </button>
                            </form>
                            <small><?php echo $post['like_count']; ?> likes</small>

                            <!-- Comment Section -->
                            <form action="comment-post.php" method="POST" class="mt-2">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <div class="form-group">
                                    <textarea name="comment_text" class="form-control" rows="2" placeholder="Add a comment..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                            </form>

                            <a href="messages.php?user_id=<?php echo $post['user_id']; ?>" class="btn btn-primary btn-sm mt-2">Chat</a>
                            <?php if (isFollowing($currentUserId, $post['user_id'])): ?>
                                <a href="unfollow.php?user_id=<?php echo $post['user_id']; ?>" class="btn btn-secondary btn-sm mt-2">Unfollow</a>
                            <?php else: ?>
                                <a href="follow.php?user_id=<?php echo $post['user_id']; ?>" class="btn btn-success btn-sm mt-2">Follow</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No posts found.</p>
    <?php endif; ?>
</div>
</body>
</html>
