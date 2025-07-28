<?php
session_start();
include 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['request_id']) || !isset($data['driver_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing request_id or driver_id']);
        exit();
    }
    
    $request_id = (int) $data['request_id'];
    $driver_id = (int) $data['driver_id'];
    
    // Update the request with the assigned driver
    $query = "UPDATE requests SET driver_id = ?, status = 'accepted' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $driver_id, $request_id);
    
    if ($stmt->execute()) {
        // Create notification for driver
        $driver_notification = "You have been assigned to request #$request_id";
        $driver_query = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'driver_assignment')";
        $driver_stmt = $conn->prepare($driver_query);
        $driver_stmt->bind_param("is", $driver_id, $driver_notification);
        $driver_stmt->execute();
        
        // Create notification for user
        $user_query = "SELECT user_id FROM requests WHERE id = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("i", $request_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_row = $user_result->fetch_assoc()) {
            $user_notification = "A driver has been assigned to your request #$request_id";
            $user_notif_query = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'request_update')";
            $user_notif_stmt = $conn->prepare($user_notif_query);
            $user_notif_stmt->bind_param("is", $user_row['user_id'], $user_notification);
            $user_notif_stmt->execute();
        }
        
        echo json_encode(['success' => true, 'message' => 'Driver assigned successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to assign driver']);
    }
    
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

$conn->close();
?> 