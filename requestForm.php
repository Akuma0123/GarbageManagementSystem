<?php 
$page = 'request';
require_once './assets/components/head.php';
require_once './assets/components/nav.php';

// Database connection
include 'database.php';

// Define constants for minimum and maximum lengths
define('MIN_LOCATION_LENGTH', 5);
define('MAX_LOCATION_LENGTH', 50);
define('MIN_DESCRIPTION_LENGTH', 10);
define('MAX_DESCRIPTION_LENGTH', 500);

// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validate and sanitize form inputs
$collection_location = trim($_POST["collection_location"]);

// Validate date
$date = trim($_POST["date"]);
if (!empty($date) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
    // Date format is invalid
    // Handle the error, show a message, redirect, or take appropriate action
}

// Validate time
$time = trim($_POST["Time"]); // corrected to "Time" instead of "time"
if (!empty($time) && !preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $time)) {
    // Time format is invalid
    // Handle the error, show a message, redirect, or take appropriate action
}

$urgency = trim($_POST["urgency"]);
$description = trim($_POST["description"]);



  // Validate collection location length
  if (strlen($collection_location) < MIN_LOCATION_LENGTH || strlen($collection_location) > MAX_LOCATION_LENGTH) {
    echo "<script>alert('Collection location must be between " . MIN_LOCATION_LENGTH . " and " . MAX_LOCATION_LENGTH . " characters')</script>";
  } elseif (empty($date) || empty($time) || empty($urgency) || empty($description)) {
    echo "<script>alert('Please fill in all fields')</script>";
  } elseif (strlen($description) < MIN_DESCRIPTION_LENGTH || strlen($description) > MAX_DESCRIPTION_LENGTH) {
    echo "<script>alert('Description must be between " . MIN_DESCRIPTION_LENGTH . " and " . MAX_DESCRIPTION_LENGTH . " characters')</script>";
  } else {
    // Prepare and execute SQL insert query using prepared statements
    $sql = "INSERT INTO requests (collection_location, pick_up_date, pick_up_time, urgency, description, uploads) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssss", $collection_location, $date, $time, $urgency, $description, $photo_path);

    if (mysqli_stmt_execute($stmt)) {
      echo "<script>alert('Request submitted successfully')</script>";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
  }
}
?>


<div class="container">
  <h1>Garbage Collection Request Form</h1>
  <form action="requestForm.php" method="post">

    <label for="collection_location">Collection Location</label>
    <input type="text" id="collection_location" name="collection_location" placeholder="Basantapur, Kathmandu">

    <label for="date">Pick Up Date</label>
    <input type="date" id="date" name="date">

    <label for="Time">Pick Up Time</label>
    <input type="time" id="Time" name="Time"> <!-- corrected to "Time" -->

    <label for="urgency">Urgency Level:</label><br>
    <select id="urgency" name="urgency" required>
      <option value="high">High</option>
      <option value="medium">Medium</option>
      <option value="low">Low</option>
    </select>

    <label for="description">Description</label>
    <textarea id="description" name="description" placeholder="Write something.." style="height:200px"></textarea>
    <?php
// Check if the form was submitted and the file was uploaded
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
  // Check if file was uploaded without errors
  if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
      $upload_dir = "photo_path/"; // Directory where uploaded files will be stored
      $target_file = $photo_path_dir . basename($_FILES["image"]["name"]);
      $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

      // Check if the file is an actual image
      $check = getimagesize($_FILES["image"]["tmp_name"]);
      if ($check !== false) {
          // Allow only certain file formats
          $allowed_types = array("jpg", "jpeg", "png", "gif");
          if (in_array($imageFileType, $allowed_types)) {
              // Move the uploaded file to the specified directory
              if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                  // File uploaded successfully, now update the database
                  $photo_path = $target_file;
                  $sql = "UPDATE requests SET photo_path = '$photo_path' WHERE id = 1"; // Adjust the WHERE clause according to your database schema
                  if ($conn->query($sql) === TRUE) {
                      echo "The file " . basename($_FILES["image"]["name"]) . " has been uploaded and database updated successfully.";
                  } else {
                      echo "Error updating database: " . $conn->error;
                  }
              } else {
                  echo "Sorry, there was an error uploading your file.";
              }
          } else {
              echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
          }
      } else {
          echo "File is not an image.";
      }
  } else {
      echo "No file uploaded.";
  }
}

?>
    <h2>Upload an Image</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <input type="file" name="image" accept="image/*" required>
        <input type="submit" value="Upload Image" name="submit">
    </form>

    <button type="submit">Request Collection</button>
  </form>
</div>

<?php
require_once './assets/components/footer.php';
?>
