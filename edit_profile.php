<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch the user's current data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = trim($_POST['bio']);
    $profilePicture = $_FILES['profile_picture'];

    // Update the bio
    $sql = "UPDATE users SET bio = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $bio, $userId);
    $stmt->execute();

    // Update the profile picture if a new one was uploaded
    if (isset($profilePicture) && $profilePicture['error'] == 0) {
        $profilePicName = time() . '_' . $profilePicture['name'];
        $targetDir = "../profile_images/";
        $targetFile = $targetDir . basename($profilePicName);

        if (move_uploaded_file($profilePicture['tmp_name'], $targetFile)) {
            // Update the database with the new profile picture
            $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $profilePicName, $userId);
            $stmt->execute();
        }
    }

    $stmt->close();
    $conn->close();

    header("Location: ../server/profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Profile</h2>
        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea name="bio" id="bio" class="form-control"><?php echo $user['bio']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html>
