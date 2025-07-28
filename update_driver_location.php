<?php
session_start();
include 'database.php';

// Check if driver is logged in
if (!isset($_SESSION['driver_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$driver_id = $_SESSION['driver_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['latitude']) && isset($data['longitude'])) {
        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];
        
        // Update driver location in database
        $query = "UPDATE drivers SET current_latitude = ?, current_longitude = ?, last_updated = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ddi", $latitude, $longitude, $driver_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Location updated']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update location']);
        }
        
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing latitude or longitude']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?> 