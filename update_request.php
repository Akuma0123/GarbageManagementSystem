<?php
session_start();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
  $request_id = $_POST['request_id'];
  $name = $_POST['name']; // Assuming you have name field in edit form
  $description = $_POST['description']; // Assuming you have description field

  // Validate data (e.g., check if name is empty)
  if (empty($name)) {
    echo "Error: Name is required!";
    exit;
  } 

  include 'database.php';

  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Update request in database
  $sql = "UPDATE requests SET name = ?, description = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $request_id);
  if ($stmt->execute()) {
    echo "Request updated successfully!";

    // Optionally, redirect back to the main page after successful update
    // header("Location: your_main_page.php");
  } else {
    echo "Error: Update failed!";
  }

  mysqli_stmt_close($stmt); // Close statement
  mysqli_close($conn); // Close connection
} else {
  // Handle case where request_id is not provided
  echo "Error: Request ID is missing!";
}
?>
