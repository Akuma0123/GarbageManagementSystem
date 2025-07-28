<?php
$page = 'notification';
require_once './assets/components/head.php';
require_once './assets/components/nav.php';
include 'database.php';

$userId = $_SESSION['user_id']; // Assuming you have user authentication and this stores the user ID

// Fetch notifications joined with requests to get image paths
$sql = "SELECT n.content, n.created_at, r.img_path_1, r.img_path_2, r.img_path_3
        FROM notifications n
        LEFT JOIN requests r ON n.request_id = r.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
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
    }

    .notification .images {
        margin-top: 1rem;
        display: flex;
        gap: 1rem;
    }

    .notification .images img {
        border-radius: 0.25rem 0.25rem 0 0;
        height: 8rem;
        object-fit: cover;
        aspect-ratio: 2 / 1;
    }

    .notification p.content {
        font-weight: bold;
        margin: 0;
    }

    .notification p.timestamp {
        color: #666;
        font-size: 0.8em;
        margin: 0.2rem 0 0 0;
    }
</style>

</head>
<body>

<div class="container">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="notification">
                <p class="content"><?php echo htmlspecialchars($row['content']); ?></p>
                <p class="timestamp"><?php echo date('jS M Y : g:i a', strtotime($row['created_at'])); ?></p>
                <div class="images">
                    <?php if (!empty($row['img_path_1'])): ?>
                        <img src="<?php echo htmlspecialchars($row['img_path_1']); ?>" alt="Image 1" />
                    <?php endif; ?>
                    <?php if (!empty($row['img_path_2'])): ?>
                        <img src="<?php echo htmlspecialchars($row['img_path_2']); ?>" alt="Image 2" />
                    <?php endif; ?>
                    <?php if (!empty($row['img_path_3'])): ?>
                        <img src="<?php echo htmlspecialchars($row['img_path_3']); ?>" alt="Image 3" />
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No notifications to display.</p>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
?>
</body>
</html>