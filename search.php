<?php
session_start();
require 'db.php';
require 'isfollowing.php'; 
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$currentUserId = $_SESSION['user_id']; // Retrieve the current user ID from the session

// Check if the search query is set
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Sanitize the search query to prevent SQL injection
$query = htmlspecialchars($query);
$query = '%' . $query . '%'; // Prepare for SQL LIKE operator

// Prepare the SQL statement to search users
$sql = "SELECT * FROM users WHERE username LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $query);
$stmt->execute();
$searchResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <!-- Search Form -->
    <div class="search-section mb-5">
        <h2>Search Results</h2>
        <form action="search.php" method="GET" class="form-inline">
            <input type="text" name="query" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>" required>
            <button type="submit" class="btn btn-primary ml-2">Search</button>
        </form>
    </div>

    <!-- Search Results Section -->
    <div class="search-results">
        <?php if ($searchResult->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($user = $searchResult->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <img src="../profile_images/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" width="50" height="50">
                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                        </div>
                        <div>
                            <a href="messages.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Chat</a>
                            <?php if (isFollowing($currentUserId, $user['id'])): ?>
                                <a href="unfollow.php?user_id=<?php echo $user['id']; ?>" class="btn btn-secondary btn-sm">Unfollow</a>
                            <?php else: ?>
                                <a href="follow.php?user_id=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">Follow</a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
