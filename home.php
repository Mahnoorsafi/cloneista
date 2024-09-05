<?php
session_start();
require 'db.php';
include 'fuctions.php';
require 'post.php';
require 'header.php';
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$currentUserId = $_SESSION['user_id']; // Get the current user ID from the session

// Get posts from accounts the user is following
$sql = "SELECT posts.*, users.username, users.profile_picture, 
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        JOIN followers ON followers.followed_id = posts.user_id 
        WHERE followers.follower_id = ? 
        ORDER BY posts.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$feedResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
       
        .post-image {
            width: 100%;
            height: auto;
        }
        
    </style>
</head>
<body>


<div class="container mt-5">
    <h2>Your Feed</h2>

    <!-- Feed Section -->
    <?php if ($feedResult->num_rows > 0): ?>
        <div class="row">
            <?php while ($post = $feedResult->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <a href="profile.php?user_id=<?php echo $post['user_id']; ?>">
                            <img src="../uploads/<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top post-image" alt="Post Image">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['username']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($post['caption']); ?></p>

                            <!-- Like Button and Like Count -->
                            <form action="like.php" method="POST">
    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
    <button type="submit" class="btn btn-link p-0">
        <?php if (hasLiked($currentUserId, $post['id'], $conn)): ?>
            <span>&#128077;</span> Unlike
        <?php else: ?>
            <span>&#128077;</span> Like
        <?php endif; ?>
    </button>
</form>

                            <small><?php echo $post['like_count']; ?> likes</small>

                            <!-- Comment Section -->
                            <form action="comment.php" method="POST" class="mt-2">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <div class="form-group">
                                    <textarea name="comment_text" class="form-control" rows="2" placeholder="Add a comment..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No posts found from the accounts you are following.</p>
    <?php endif; ?>
</div>
</body>
</html>
