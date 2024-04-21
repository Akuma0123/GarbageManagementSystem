<?php
// delete_request.php

include 'database.php';

// Check if request is sent via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the request ID from the request body
    $data = json_decode(file_get_contents("php://input"));
    $requestId = $data->requestId;

    // Perform deletion operation in your database
    // Example:
    $sql = "DELETE FROM requests WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$requestId]);

    // Send response status
    http_response_code(200);
    echo json_encode(["message" => "Request deleted successfully"]);
} else {
    // Send error response if request method is not POST
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Method Not Allowed"]);
}
?>
