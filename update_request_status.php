<?php
session_start();

include 'database.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin_login.php');
    exit();
}

if (isset($_POST['accept'])) {
    // Validate and sanitize the request_id
    $requestId = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);
    if ($requestId === false || $requestId <= 0) {
        // Invalid request_id, handle error or redirect to adminDas.php with an error message
        header('Location: adminDas.php?error=invalid_request_id');
        exit();
    }

    // Fetch the request details including user_id and current status
    $query = "SELECT status, user_id FROM requests WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if ($request) {
        $currentStatus = $request['status'];
        $userId = $request['user_id'];

        // Update the status based on the current status
        if ($currentStatus === 'pending') {
            $newStatus = 'accepted';
        } elseif ($currentStatus === 'accepted') {
            $newStatus = 'finished';
        } else {
            // If the status is already 'Finished', no further action is needed
            header('Location: adminDas.php');
            exit();
        }

        // Update the status in the database
        $query = "UPDATE requests SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $newStatus, $requestId);
        $stmt->execute();

        // Check if the status was updated successfully
        if ($stmt->affected_rows > 0) {
            $stmt->close();
            $conn->close();

            // Redirect only if the status was successfully updated
            header('Location: adminDas.php');
            exit();
        } else {
            // Handle error or redirect to adminDas.php with an error message
            header('Location: adminDas.php?error=status_not_updated');
            exit();
        }
    } else {
        // Handle error or redirect to adminDas.php with an error message
        header('Location: adminDas.php?error=request_not_found');
        exit();
    }
}
?>
