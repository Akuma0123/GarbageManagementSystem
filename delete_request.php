<?php
// delete_request.php

include 'database.php';

// Check if request is sent via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the request ID from the request body
    
    $requestId = $_POST['request_id'];

    // Perform deletion operation in your database
    // Example:
    $sql = "DELETE FROM requests WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt-> bind_param('s',$requestId);
    $stmt->execute();
}

header('location:AdminDas.php');

?>
