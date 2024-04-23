<?php
$page = 'notification';
require_once './assets/components/head.php';
require_once './assets/components/nav.php';
include 'database.php';

$userId = $_SESSION['user_id']; // Assuming you have user authentication and this stores the user ID
$sql = "SELECT content, created_at FROM notifications WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background-color: #f5f5f5;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .notification {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .notification p {
        margin: 0;
    }

    .notification p.content {
        font-weight: bold;
    }

    .notification p.timestamp {
        color: #666;
        font-size: 0.8em;
    }
</style>
</head>
<body>

<div class="container">
    <?php
    // Check if any notifications exist
    if ($result->num_rows > 0) {  
        // Notifications exist, display them
        while ($row = $result->fetch_assoc()) {
            echo '<div class="notification">';
            echo '<p class="content">' . $row['content'] . '</p>';
            echo '<p class="timestamp">Received at: ' . $row['created_at'] . '</p>'; // Assuming you have a timestamp field named 'created_at'
            echo '</div>';
        }
    } else {
        // No notifications
        echo '<p>No notifications to display.</p>';
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
    ?>
</div>

</body>
</html>
