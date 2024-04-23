<?php
session_start();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $collection_location = $_POST['collection_location'];
    $pick_up_date = $_POST['pick_up_date'];
    $pick_up_time = $_POST['pick_up_time'];
    $urgency = $_POST['urgency'];
    $description = $_POST['description'];
    $img_path_1 = $_POST['img_path_1'];
    $img_path_2 = $_POST['img_path_2'];
    $img_path_3 = $_POST['img_path_3'];
    $phone_number = $_POST['phone_number'];

    // Validate data (e.g., check if collection_location is empty)
    if (empty($collection_location)) {
        echo "Error: Collection location is required!";
        exit;
    }

    // Connect to your database (replace with your connection details)
     include 'database.php';
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Update request in database
    $sql = "UPDATE requests SET 
            collection_location = ?,
            pick_up_date = ?,
            pick_up_time = ?,
            urgency = ?,
            description = ?,
            img_path_1 = ?,
            img_path_2 = ?,
            img_path_3 = ?,
            phone_number = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Error: " . $conn->error;
        exit;
    }

    // Bind parameters to the prepared statement
    $stmt->bind_param(
        "sssssssssi", 
        $collection_location, 
        $pick_up_date, 
        $pick_up_time, 
        $urgency, 
        $description, 
        $img_path_1, 
        $img_path_2, 
        $img_path_3, 
        $phone_number, 
        $request_id
    );

    // Execute the prepared statement
    if ($stmt->execute()) {
        echo "Request updated successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    mysqli_close($conn);
} else {
    // Handle case where request_id is not provided
    echo "Error: Request ID is missing!";
}
?>
