<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['post_image'])) {
    $userId = $_SESSION['user_id'];
    $caption = trim($_POST['caption']);
    $image = $_FILES['post_image'];

    // Validate and upload the image
    $imageName = time() . '_' . $image['name'];
    $targetDir = "../uploads/";
    $targetFile = $targetDir . basename($imageName);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if the image is a valid image
    $check = getimagesize($image['tmp_name']);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size (5MB max)
    if ($image['size'] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($image['tmp_name'], $targetFile)) {
            // Insert the post into the database
            $sql = "INSERT INTO posts (user_id, image_url, caption, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $userId, $imageName, $caption);
            if ($stmt->execute()) {
                header("Location: ../server/profile.php");
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    $conn->close();
}
?>
