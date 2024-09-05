<?php
session_start();
require 'db.php';
require 'post.php';
require 'header.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch the profile user
if (isset($_GET['id'])) {
    $profileId = $_GET['id'];
} else {
    $profileId = $userId; // View your own profile by default
}

// Fetch the user profile data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profileId);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

if (!$user) {
    echo "User not found!";
    exit;
}

// Fetch posts from the profile user
$sql = "SELECT p.*, u.username FROM posts p 
        JOIN users u ON p.user_id = u.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profileId);
$stmt->execute();
$posts = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img {
            width: 100%;
            max-width: 200px;
        }
        .post-img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Profile Header -->
        <div class="row">
            <div class="col-md-4">
                <img src="../profile_images/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="img-thumbnail profile-img">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p><?php echo htmlspecialchars($user['bio']); ?></p>
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
            
                <!-- Follow/Unfollow Button -->
                <?php if ($userId != $user['id']): ?>
                    <form action="follow_user.php" method="POST" class="d-inline">
                        <input type="hidden" name="followed_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <?php 
                            // Check if the current user is following the profile user
                            $sql = "SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ii", $userId, $user['id']);
                            $stmt->execute();
                            $followResult = $stmt->get_result();
                            echo ($followResult->num_rows > 0) ? "Unfollow" : "Follow"; 
                            ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="col-md-8">
                <!-- User Posts -->
                <h3><?php echo htmlspecialchars($user['username']); ?>'s Posts</h3>
                <div class="row">
                    <?php while ($post = $posts->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <img src="../uploads/<?php echo htmlspecialchars($post['image_url']); ?>" class="img-fluid post-img" alt="Post Image">
                        <p><?php echo htmlspecialchars($post['caption']); ?></p>
                        
                        <!-- Like Button -->
                        <form action="like_post.php" method="POST" class="d-inline">
                            <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <?php 
                                $sql = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
                                $stmt = $conn->prepare($sql);
                                if (!$stmt) {
                                    die('Prepare failed: ' . $conn->error);
                                }
                                $stmt->bind_param("ii", $userId, $post['id']);
                                $stmt->execute();
                                $likeResult = $stmt->get_result();
                                echo ($likeResult->num_rows > 0) ? "Unlike" : "Like"; 
                                $stmt->close();
                                ?>
                            </button>
                        </form>

                        <!-- Comment Form -->
                        <form action="comment_post.php" method="POST" class="mt-2">
                            <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                            <div class="mb-3">
                                <textarea name="comment_text" class="form-control" placeholder="Write a comment..." rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Comment</button>
                        </form>
                        
                        <!-- Display Comments -->
                        <div class="mt-2">
                            <?php
                            $sql = "SELECT comments.comment_text, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at DESC";
                            $stmt = $conn->prepare($sql);
                            if (!$stmt) {
                                die('Prepare failed: ' . $conn->error);
                            }
                            $stmt->bind_param("i", $post['id']);
                            $stmt->execute();
                            $commentResult = $stmt->get_result();
                            while ($comment = $commentResult->fetch_assoc()): ?>
                                <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['comment_text']); ?></p>
                            <?php endwhile; ?>
                            <?php $stmt->close(); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Upload New Post -->
        <div class="mt-5">
            <h3>Upload New Post</h3>
            <form action="upload_post.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <input type="file" name="post_image" class="form-control" required>
                </div>
                <div class="mb-3">
                    <textarea name="caption" class="form-control" placeholder="Write a caption..." rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
