<?php
$content = "Your request has been accepted."; // Notification content
$userId = $r['user_id']; // Assuming you have a field for user ID in your requests table
$sql = "INSERT INTO notifications (user_id, content) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $userId, $content);
$stmt->execute();
$stmt->close();
