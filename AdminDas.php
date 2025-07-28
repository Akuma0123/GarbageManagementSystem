<?php
session_start();

include 'database.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT password_hash FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password_hash'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: adminDas.php');
            exit();
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Invalid username or password";
    }

    $stmt->close();
    $conn->close();
}

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: adminDas.php');
    exit();
}

// === New Function: Auto-Accept Requests Within Radius and Assign Drivers ===
function autoAcceptRequestsWithinRadius($conn, $baseLat, $baseLon, $radiusKm)
{
    // Select pending requests within the radius
    $query = "SELECT id, user_id FROM requests
              WHERE status = 'pending' AND latitude IS NOT NULL AND longitude IS NOT NULL AND
              (
                6371 * ACOS(
                  COS(RADIANS(?)) * COS(RADIANS(latitude)) *
                  COS(RADIANS(longitude) - RADIANS(?)) +
                  SIN(RADIANS(?)) * SIN(RADIANS(latitude))
                )
              ) <= ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dddi", $baseLat, $baseLon, $baseLat, $radiusKm);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all drivers
    $driversResult = $conn->query("SELECT id FROM drivers");
    $drivers = $driversResult->fetch_all(MYSQLI_ASSOC);
    if (empty($drivers))
        return; // No drivers available, skip

    $driverCount = count($drivers);
    $driverIndex = 0;

    while ($request = $result->fetch_assoc()) {
        $driverId = $drivers[$driverIndex]['id'];
        $driverIndex = ($driverIndex + 1) % $driverCount;

        // Update request status and assign driver
        $updateStmt = $conn->prepare("UPDATE requests SET status = 'accepted', driver_id = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $driverId, $request['id']);
        $updateStmt->execute();
        $updateStmt->close();

        $content = "Your request has been auto-accepted and assigned to a driver.";
        $requestId = $request['id'];    // Request ID from your request array/object
        $userId = $request['user_id'];  // User ID linked to the request

        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, request_id, content) VALUES (?, ?, ?)");
        $notifStmt->bind_param("iis", $userId, $requestId, $content);
        $notifStmt->execute();
        $notifStmt->close();
    }

    $stmt->close();
}

// Call the auto accept function before fetching requests
$basantapurLat = 27.7046;
$basantapurLon = 85.3076;
$radius = 5; // 5km radius
autoAcceptRequestsWithinRadius($conn, $basantapurLat, $basantapurLon, $radius);

// Accept Button Action
if (isset($_POST['accept'])) {
    $requestId = $_POST['request_id'];

    $query = "SELECT * FROM requests WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    $updateStatus = $conn->prepare("UPDATE requests SET status = 'accepted' WHERE id = ?");
    $updateStatus->bind_param("i", $requestId);
    $updateStatus->execute();
    $updateStatus->close();

    $content = "Your request has been accepted.";
    $userId = $request['user_id'];
    $sql = "INSERT INTO notifications (user_id, content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $content);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['finished'])) {
    $requestId = $_POST['request_id'];

    $query = "SELECT * FROM requests WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    $updateStatus = $conn->prepare("UPDATE requests SET status = 'finished' WHERE id = ?");
    $updateStatus->bind_param("i", $requestId);
    $updateStatus->execute();
    $updateStatus->close();

    $content = "Your request has been finished.";
    $userId = $request['user_id'];
    $sql = "INSERT INTO notifications (user_id, content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $content);
    $stmt->execute();
    $stmt->close();
}

// Delete Button Action
if (isset($_POST['delete'])) {
    $requestId = $_POST['request_id'];

    $query = "SELECT * FROM requests WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    $deleteStmt = $conn->prepare("DELETE FROM requests WHERE id = ?");
    $deleteStmt->bind_param("i", $requestId);
    $deleteStmt->execute();
    $deleteStmt->close();

    $content = "Your request has been declined by the admin.";
    $userId = $request['user_id'];
    $sql = "INSERT INTO notifications (user_id, content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $content);
    $stmt->execute();
    $stmt->close();
}

// Fetch data for display
$query = "SELECT *, (
    6371 * ACOS(
        COS(RADIANS(?)) * COS(RADIANS(latitude)) *
        COS(RADIANS(longitude) - RADIANS(?)) +
        SIN(RADIANS(?)) * SIN(RADIANS(latitude))
    )
) AS distance
FROM requests
WHERE latitude IS NOT NULL AND longitude IS NOT NULL
HAVING distance <= ?
ORDER BY distance ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("dddi", $basantapurLat, $basantapurLon, $basantapurLat, $radius);
$stmt->execute();
$result = $stmt->get_result();

// Fetch drivers for assignment dropdown
$drivers_result = $conn->query("SELECT id, name, email FROM drivers");
$drivers = [];
while ($d = $drivers_result->fetch_assoc()) {
    $drivers[] = $d;
}

// Handle driver assignment
if (isset($_POST['assign_driver'])) {
    $requestId = $_POST['request_id'];
    $driverId = $_POST['driver_id'];
    $stmt = $conn->prepare("UPDATE requests SET driver_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $driverId, $requestId);
    $stmt->execute();
    $stmt->close();
    header('Location: AdminDas.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .driver-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .driver-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .driver-card.best-match {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
        
        .driver-card.assigned {
            border-color: #6b7280;
            background-color: #f9fafb;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <header class="bg-white shadow mb-6">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-blue-700">ADMIN Dashboard</h1>
            <nav class="flex gap-4">
                <a href="#" class="text-blue-700 font-semibold">Requests</a>
                <a href="/user.php" class="text-gray-700 hover:text-blue-700">Users</a>
                <a href="/admin_login.php" class="text-gray-700 hover:text-blue-700">Logout</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4">
        <h2 class="text-xl font-semibold mb-4 text-blue-700">Requests Table</h2>
        <div class="overflow-x-auto rounded-lg shadow-lg bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-blue-700 to-green-600 text-white">
                    <tr>
                        <th class="px-4 py-2">User ID</th>
                        <th class="px-4 py-2">Location</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Time</th>
                        <th class="px-4 py-2">Phone No</th>
                        <th class="px-4 py-2">Urgency</th>
                        <th class="px-4 py-2">Description</th>
                        <th class="px-4 py-2">Image</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Assigned Driver</th>
                        <th class="px-4 py-2">Assign/Change Driver</th>
                        <th class="px-4 py-2">Accept</th>
                        <th class="px-4 py-2">Delete</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($result as $r) { ?>
                        <tr class="hover:bg-blue-50">
                            <td class="px-4 py-2"><?php echo $r['user_id']; ?></td>
                            <td class="px-4 py-2"><?php echo $r['collection_location']; ?></td>
                            <td class="px-4 py-2"><?php echo $r['pick_up_date']; ?></td>
                            <td class="px-4 py-2"><?php echo $r['pick_up_time']; ?></td>
                            <td class="px-4 py-2"><?php echo $r['phone_number']; ?></td>
                            <td class="px-4 py-2"><?php echo $r['urgency']; ?></td>
                            <td class="px-4 py-2"><?php echo $r['description']; ?></td>
                            <td class="px-4 py-2 flex gap-1">
                                <?php if ($r['img_path_1'] != null): ?>
                                    <img class="rounded shadow w-12 h-12 object-cover cursor-pointer"
                                        src="<?php echo $r['img_path_1']; ?>" alt="Image">
                                <?php endif; ?>
                                <?php if (isset($r['img_path_2']) && $r['img_path_2'] != null): ?>
                                    <img class="rounded shadow w-12 h-12 object-cover cursor-pointer"
                                        src="<?php echo $r['img_path_2']; ?>" alt="Image">
                                <?php endif; ?>
                                <?php if ($r['img_path_3'] != null): ?>
                                    <img class="rounded shadow w-12 h-12 object-cover cursor-pointer"
                                        src="<?php echo $r['img_path_3']; ?>" alt="Image">
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-white font-semibold
                        <?php if ($r['status'] === 'pending')
                            echo 'bg-yellow-500';
                        elseif ($r['status'] === 'accepted')
                            echo 'bg-blue-700';
                        elseif ($r['status'] === 'finished')
                            echo 'bg-green-600'; ?>"><?php echo $r['status']; ?></span>
                            </td>
                            <td class="px-4 py-2">
                                <?php
                                if ($r['driver_id']) {
                                    foreach ($drivers as $d) {
                                        if ($d['id'] == $r['driver_id']) {
                                            echo '<span class="font-semibold text-green-700">' . htmlspecialchars($d['name']) . '</span><br><span class="text-xs text-gray-500">' . htmlspecialchars($d['email']) . '</span>';
                                            break;
                                        }
                                    }
                                } else {
                                    echo '<span class="text-gray-400 italic">Unassigned</span>';
                                }
                                ?>
                            </td>
                            <td class="px-4 py-2">
                                <button onclick="showAllDrivers(<?php echo $r['id']; ?>)" 
                                        class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                    <i class="fa-solid fa-users"></i> Assign Driver
                                </button>
                                
                                <!-- Driver Assignment Modal -->
                                <div id="driverModal-<?php echo $r['id']; ?>" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50">
                                    <div class="modal-content bg-white rounded-lg shadow-xl max-w-2xl mx-auto mt-20 p-6">
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="text-lg font-bold text-gray-800">Assign Driver to Request #<?php echo $r['id']; ?></h3>
                                            <span class="close text-gray-500 hover:text-gray-700 text-2xl cursor-pointer" onclick="closeDriverModal(<?php echo $r['id']; ?>)">&times;</span>
                                        </div>
                                        
                                        <div id="driverList-<?php echo $r['id']; ?>" class="max-h-96 overflow-y-auto">
                                            <div class="text-center text-gray-500">
                                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                                <p>Loading drivers...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <!-- accept form  -->
                                <form action="update_request_status.php" method="post">
                                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" name="accept"
                                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded"
                                        data-status="<?php echo $r['status']; ?>">
                                        <?php if ($r['status'] == 'pending'): ?>
                                            <i class="fa-regular fa-circle-check"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-clock"></i>
                                        <?php endif ?>
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-2">
                                <!-- delete form -->
                                <form action="delete_request.php" method="post">
                                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" name="delete"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                                        <i class="fa-regular fa-circle-xmark"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>


    <script>
        // JavaScript
        document.addEventListener('DOMContentLoaded', function () {
            // Get the modal
            var modal = document.getElementById('imageModal');

            // Get the image and insert it inside the modal
            var img = document.querySelectorAll('.request-img');
            var modalImg = document.getElementById('modalImage');

            // Loop through all images and add click event listeners
            img.forEach(function (image) {
                image.addEventListener('click', function () {
                    modal.style.display = 'block';
                    modalImg.src = this.src;
                });
            });

            // Get the <span> element that closes the modal
            var span = document.getElementsByClassName('close')[0];

            // When the user clicks on <span> (x), close the modal
            span.onclick = function () {
                modal.style.display = 'none';
            };

            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            };
        });

        // Driver Assignment Functions
        function showAllDrivers(requestId) {
            const modal = document.getElementById(`driverModal-${requestId}`);
            const driverList = document.getElementById(`driverList-${requestId}`);
            
            modal.classList.remove('hidden');
            driverList.innerHTML = '<div class="text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p>Loading all drivers...</p></div>';
            
            fetch(`get_driver_suggestions.php?request_id=${requestId}&all=true`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayDrivers(requestId, data.drivers, false);
                    } else {
                        driverList.innerHTML = `<div class="text-red-500 text-center">${data.error || 'Failed to load drivers'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    driverList.innerHTML = '<div class="text-red-500 text-center">Failed to load drivers. Please try again.</div>';
                });
        }

        function displayDrivers(requestId, drivers, isSuggestions) {
            const driverList = document.getElementById(`driverList-${requestId}`);
            
            if (!drivers || drivers.length === 0) {
                driverList.innerHTML = '<div class="text-gray-500 text-center">No drivers available.</div>';
                return;
            }
            
            let html = '';
            if (isSuggestions) {
                html += '<h4 class="font-semibold text-green-700 mb-3"><i class="fa-solid fa-star"></i> Best Driver Matches</h4>';
            } else {
                html += '<h4 class="font-semibold text-blue-700 mb-3"><i class="fa-solid fa-users"></i> All Available Drivers</h4>';
            }
            
            drivers.forEach((driver, index) => {
                const isBestMatch = isSuggestions && index === 0;
                const isAssigned = driver.current_assignments > 0;
                
                html += `
                    <div class="driver-card bg-gray-50 rounded-lg p-4 mb-3 ${isBestMatch ? 'best-match border-2 border-green-400' : ''} ${isAssigned ? 'assigned opacity-75' : ''}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800">${driver.name}</h4>
                                <p class="text-sm text-gray-600">${driver.email}</p>
                                <div class="mt-2 space-y-1">
                                    <p class="text-xs text-gray-500">Distance: <span class="font-semibold">${driver.distance.toFixed(2)} km</span></p>
                                    <p class="text-xs text-gray-500">Current Assignments: <span class="font-semibold">${driver.current_assignments}</span></p>
                                    <p class="text-xs text-gray-500">Last Updated: <span class="font-semibold">${driver.last_updated}</span></p>
                                </div>
                                ${isBestMatch ? '<div class="mt-2"><span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Best Match</span></div>' : ''}
                            </div>
                            <button onclick="assignDriver(${requestId}, ${driver.id}, '${driver.name}')" 
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 ${isAssigned ? 'opacity-50 cursor-not-allowed' : ''}"
                                    ${isAssigned ? 'disabled' : ''}>
                                Assign
                            </button>
                        </div>
                    </div>
                `;
            });
            
            driverList.innerHTML = html;
        }

        function assignDriver(requestId, driverId, driverName) {
            const button = event.target;
            const originalText = button.textContent;
            
            button.textContent = 'Assigning...';
            button.disabled = true;
            
            fetch('assign_driver.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    request_id: requestId,
                    driver_id: driverId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the UI to show assigned driver
                    const requestCard = document.querySelector(`[data-request-id="${requestId}"]`);
                    if (requestCard) {
                        const driverInfo = requestCard.querySelector('.driver-info');
                        if (driverInfo) {
                            driverInfo.innerHTML = `
                                <div class="text-xs text-gray-500 font-semibold uppercase">Assigned Driver</div>
                                <div class="text-green-600 font-medium">${driverName}</div>
                            `;
                        }
                    }
                    
                    // Show success message
                    alert(`Driver ${driverName} assigned successfully!`);
                    closeDriverModal(requestId);
                } else {
                    alert(`Error: ${data.error || 'Failed to assign driver'}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to assign driver. Please try again.');
            })
            .finally(() => {
                button.textContent = originalText;
                button.disabled = false;
            });
        }

        function closeDriverModal(requestId) {
            const modal = document.getElementById(`driverModal-${requestId}`);
            modal.classList.add('hidden');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.add('hidden');
            }
        });
    </script>

</body>

</html>