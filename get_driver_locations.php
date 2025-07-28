<?php
session_start();
include 'database.php';

// Check if user is logged in (admin or regular user)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch all drivers with their current locations
    $query = "SELECT id, name, email, current_latitude, current_longitude, last_updated FROM drivers WHERE current_latitude IS NOT NULL AND current_longitude IS NOT NULL";
    $result = $conn->query($query);
    
    $drivers = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $drivers[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'latitude' => (float) $row['current_latitude'],
                'longitude' => (float) $row['current_longitude'],
                'last_updated' => $row['last_updated']
            ];
        }
    }
    
    echo json_encode(['drivers' => $drivers]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();
?> 