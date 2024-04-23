<?php
session_start();

include 'database.php';

// Fetch uploaded photos from the database
$query = "SELECT * FROM photos";
$result = $conn->query($query);

// Check if any photos are found
if ($result->num_rows > 0) {
    $photos = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $photos = [];
}

// Check for success or error messages from the URL query parameters
$successMessage = isset($_GET['success']) ? "Photo uploaded successfully." : "";
$errorMessage = isset($_GET['error']) ? $_GET['error'] : "";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index Page</title>
</head>
<body>
    <h2>Uploaded Photos</h2>

    <!-- Display success or error messages -->
    <?php if ($successMessage): ?>
        <p style="color: green;"><?php echo $successMessage; ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <!-- Display uploaded photos -->
    <div class="photo-gallery">
        <?php foreach ($photos as $photo): ?>
            <div class="photo">
                <img src="<?php echo $photo['path']; ?>" alt="Uploaded Photo">
            </div>
        <?php endforeach; ?>
    </div>

    <hr>

    <!-- Form to upload more photos -->
    <h2>Upload Photo</h2>
    <form action="photo_uploads.php" method="post" enctype="multipart/form-data">
        <input type="file" name="photo" accept="image/*">
        <button type="submit" name="upload">Upload</button>
    </form>
</body>
</html>
