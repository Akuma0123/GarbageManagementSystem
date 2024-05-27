<?php
$page = 'notification';
require_once './assets/components/head.php';
require_once './assets/components/nav.php';
include 'database.php';


$userId = $_SESSION['user_id']; // Assuming you have user authentication and this stores the user ID
$sql = "SELECT * FROM requests WHERE user_id = ? AND (status = 'accepted' OR status = 'finished')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

?>

<style>
    .container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        padding: 2rem;
    }

    .notification {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .notification .images {
        margin-top: 1rem;
        display: flex;
        gap: 1rem;
    }

    .notification .images img {
        border-radius: 0.25rem 0.25rem 0 0;
        overflow: hidden;
        aspect-ratio: 2/1;
        object-fit: cover;
        overflow: hidden;
        height: 8rem;
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

                // Determine the notification content based on the status
                $notificationContent = '';
                if ($row['status'] === 'accepted') {
                    $notificationContent = 'Request Accepted';
                } elseif ($row['status'] === 'finished') {
                    $notificationContent = 'Request Finished';
                }

                echo '<p class="content">' . $notificationContent . '</p>';
                echo '<p class="timestamp">' . date('jS M Y : g:i a', strtotime($row['updated_at'])) . '</p>';

                echo '<div class="images">';
                if ($row['img_path_1'] != null): ?>
                    <img class="request-img" src="<?php echo $row['img_path_1']; ?>" alt="Image">
                <?php endif;
                if (isset($row['img_path_2']) && $row['img_path_2'] != null): ?>
                    <img class="request-img" src="<?php echo $row['img_path_2']; ?>" alt="Image">
                <?php endif;
                if ($row['img_path_3'] != null): ?>
                    <img class="request-img" src="<?php echo $row['img_path_3']; ?>" alt="Image">
                <?php endif;
                echo '</div>'; // Closing images div
                echo '</div>'; // Closing notification div
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