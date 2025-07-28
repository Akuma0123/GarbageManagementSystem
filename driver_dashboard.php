<?php
session_start();

include 'database.php';

if (!isset($_SESSION['driver_id'])) {
  header('Location: index.php');
  exit();
}
$driver_id = $_SESSION['driver_id'];
$driver_name = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : 'Driver';
$driver_email = isset($_SESSION['driver_email']) ? $_SESSION['driver_email'] : '';

// Fetch driver profile (including location)
$profile_query = "SELECT * FROM drivers WHERE id = ?";
$profile_stmt = $conn->prepare($profile_query);
$profile_stmt->bind_param('i', $driver_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();

// Fetch assigned requests with user info
$requests_query = "SELECT r.*, u.name as user_name, u.email as user_email FROM requests r LEFT JOIN users u ON r.user_id = u.user_id WHERE r.driver_id = ?";
$requests_stmt = $conn->prepare($requests_query);
$requests_stmt->bind_param('i', $driver_id);
$requests_stmt->execute();
$requests_result = $requests_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Driver Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 min-h-screen">

  <main class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-extrabold text-blue-800 mb-8 tracking-tight">Welcome,
      <?php echo htmlspecialchars($driver_name); ?>!</h1>
    <div class="flex flex-col md:flex-row gap-8 w-full">
      <!-- Assigned Requests Section (Left on desktop) -->
      <div class="flex-1 w-full">
        <h2 class="text-xl font-bold text-blue-700 mb-4">Assigned Requests</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <?php if ($requests_result->num_rows > 0): ?>
            <?php while ($req = $requests_result->fetch_assoc()): ?>
              <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col gap-4 hover:shadow-xl transition">
                <div class="flex-1">
                  <div class="flex flex-wrap gap-2 items-center mb-2">
                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">Request #<?php echo htmlspecialchars($req['id']); ?></span>
                    <span class="inline-block bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full"><?php echo htmlspecialchars($req['urgency']); ?> Urgency</span>
                    <span class="inline-block bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full"><?php echo htmlspecialchars($req['status']); ?></span>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
                    <div>
                      <div class="text-xs text-gray-500 font-semibold uppercase">Location</div>
                      <div class="text-blue-900 font-medium"><?php echo htmlspecialchars($req['collection_location']); ?></div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500 font-semibold uppercase">Date & Time</div>
                      <div class="text-blue-900 font-medium"><?php echo htmlspecialchars($req['pick_up_date']); ?> @ <?php echo htmlspecialchars($req['pick_up_time']); ?></div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500 font-semibold uppercase">User</div>
                      <div class="text-blue-900 font-medium"><?php echo htmlspecialchars($req['user_name']); ?> (<?php echo htmlspecialchars($req['user_email']); ?>)</div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500 font-semibold uppercase">Description</div>
                      <div class="text-gray-600 break-words" style="word-wrap: break-word; overflow-wrap: break-word;">
                          <?php echo htmlspecialchars($req['description']); ?>
                      </div>
                    </div>
                  </div>
                  <div class="flex flex-wrap gap-2 mt-2">
                    <?php if ($req['img_path_1']): ?>
                      <img src="<?php echo $req['img_path_1']; ?>" alt="Image 1" class="rounded shadow w-16 h-16 object-cover cursor-pointer" onclick="showModal(this.src)">
                    <?php endif; ?>
                    <?php if ($req['img_path_2']): ?>
                      <img src="<?php echo $req['img_path_2']; ?>" alt="Image 2" class="rounded shadow w-16 h-16 object-cover cursor-pointer" onclick="showModal(this.src)">
                    <?php endif; ?>
                    <?php if ($req['img_path_3']): ?>
                      <img src="<?php echo $req['img_path_3']; ?>" alt="Image 3" class="rounded shadow w-16 h-16 object-cover cursor-pointer" onclick="showModal(this.src)">
                    <?php endif; ?>
                  </div>
                </div>
                <div class="flex flex-col items-end gap-2 min-w-[180px]">
                    <!-- REMOVE this block:
                    <form method="post" action="update_request_status.php" class="flex flex-col gap-2 w-full">
                        <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                        <select name="status" class="border rounded px-2 py-1 focus:ring-2 focus:ring-green-400">
                            <option value="pending" <?php if ($req['status'] === 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="accepted" <?php if ($req['status'] === 'accepted') echo 'selected'; ?>>Accepted</option>
                            <option value="finished" <?php if ($req['status'] === 'finished') echo 'selected'; ?>>Finished</option>
                        </select>
                        <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Update</button>
                    </form>
                    -->
                    <!-- INSTEAD, just show the status: -->
                    <span class="inline-block px-3 py-1 rounded-full text-white font-semibold
                        <?php
                            if ($req['status'] === 'pending') echo 'bg-yellow-500';
                            elseif ($req['status'] === 'accepted') echo 'bg-blue-700';
                            elseif ($req['status'] === 'finished') echo 'bg-green-600';
                        ?>">
                        <?php echo htmlspecialchars($req['status']); ?>
                    </span>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="text-center py-8 text-gray-400 bg-white rounded-xl shadow col-span-2">No requests assigned to you yet.</div>
          <?php endif; ?>
        </div>
        <!-- Modal for images -->
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
          <span class="absolute top-8 right-8 text-white text-4xl font-bold cursor-pointer" onclick="closeModal()">&times;</span>
          <img class="max-w-2xl max-h-[70vh] rounded-xl shadow-lg" id="modalImage">
        </div>
        <script>
          function showModal(src) {
            var modal = document.getElementById('imageModal');
            var modalImg = document.getElementById('modalImage');
            modal.style.display = 'flex';
            modalImg.src = src;
          }
          function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
          }
          window.onclick = function (event) {
            var modal = document.getElementById('imageModal');
            if (event.target == modal) {
              modal.style.display = 'none';
            }
          }
        </script>
      </div>

      <!-- Driver Profile Section (Right on desktop) -->
      <div class="w-full md:w-1/3 md:order-2">
        <h2 class="text-xl font-bold text-blue-700 mb-2">Driver Profile</h2>
        <!-- Modern Profile Info List -->
        <div class="w-full mb-4">
          <div class="grid grid-cols-1 gap-3">
            <div class="flex items-center bg-white rounded-lg shadow p-3 hover:shadow-md transition">
              <div class="flex-shrink-0 bg-blue-100 rounded-full p-2 mr-3">
                <i class="fa-solid fa-id-badge text-blue-600 text-lg"></i>
              </div>
              <div>
                <div class="text-xs text-gray-500 font-semibold uppercase">Name</div>
                <div class="text-blue-900 font-bold text-base tracking-wide"><?php echo htmlspecialchars($profile['name']); ?></div>
              </div>
            </div>
            <div class="flex items-center bg-white rounded-lg shadow p-3 hover:shadow-md transition">
              <div class="flex-shrink-0 bg-green-100 rounded-full p-2 mr-3">
                <i class="fa-solid fa-envelope text-green-600 text-lg"></i>
              </div>
              <div>
                <div class="text-xs text-gray-500 font-semibold uppercase">Email</div>
                <div class="text-blue-900 font-medium text-base tracking-wide"><?php echo htmlspecialchars($profile['email']); ?></div>
              </div>
            </div>
            <div class="flex items-center bg-white rounded-lg shadow p-3 hover:shadow-md transition">
              <div class="flex-shrink-0 bg-yellow-100 rounded-full p-2 mr-3">
                <i class="fa-solid fa-location-dot text-yellow-600 text-lg"></i>
              </div>
              <div>
                <div class="text-xs text-gray-500 font-semibold uppercase">Last Location</div>
                <div class="text-blue-900 font-medium text-base tracking-wide"><?php echo ($profile['current_latitude'] && $profile['current_longitude']) ? htmlspecialchars($profile['current_latitude'] . ', ' . $profile['current_longitude']) : 'N/A'; ?></div>
              </div>
            </div>
            <div class="flex items-center bg-white rounded-lg shadow p-3 hover:shadow-md transition">
              <div class="flex-shrink-0 bg-purple-100 rounded-full p-2 mr-3">
                <i class="fa-solid fa-clock text-purple-600 text-lg"></i>
              </div>
              <div>
                <div class="text-xs text-gray-500 font-semibold uppercase">Last Updated</div>
                <div class="text-blue-900 font-medium text-base tracking-wide"><?php echo htmlspecialchars($profile['last_updated']); ?></div>
              </div>
            </div>
          </div>
        </div>
        <div class="mb-4 w-full">
          <h3 class="font-semibold mb-2 text-green-700">Live Location Tracking</h3>
          <div id="map" class="rounded-lg shadow" style="height: 200px; width: 100%;"></div>
          <div class="mt-2 text-sm text-gray-600">
            <span id="location-status">Getting location...</span>
          </div>
        </div>
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script>
          let map, marker, watchId;
          
          document.addEventListener('DOMContentLoaded', function () {
            // Initialize map with default location (Basantapur)
            const defaultLat = <?php echo $profile['current_latitude'] ?: 27.7046; ?>;
            const defaultLng = <?php echo $profile['current_longitude'] ?: 85.3096; ?>;
            
            map = L.map('map').setView([defaultLat, defaultLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            
            // Create marker
            marker = L.marker([defaultLat, defaultLng]).addTo(map);
            
            // Start location tracking
            startLocationTracking();
          });
          
          function startLocationTracking() {
            if (navigator.geolocation) {
              const statusElement = document.getElementById('location-status');
              
              // Get initial position
              navigator.geolocation.getCurrentPosition(
                function(position) {
                  updateLocation(position.coords.latitude, position.coords.longitude);
                  statusElement.textContent = 'Location tracking active';
                  statusElement.className = 'mt-2 text-sm text-green-600';
                },
                function(error) {
                  statusElement.textContent = 'Location access denied. Please enable location services.';
                  statusElement.className = 'mt-2 text-sm text-red-600';
                }
              );
              
              // Watch for position changes
              watchId = navigator.geolocation.watchPosition(
                function(position) {
                  updateLocation(position.coords.latitude, position.coords.longitude);
                },
                function(error) {
                  console.error('Location tracking error:', error);
                },
                {
                  enableHighAccuracy: true,
                  timeout: 10000,
                  maximumAge: 30000
                }
              );
            } else {
              document.getElementById('location-status').textContent = 'Geolocation not supported by this browser.';
            }
          }
          
          function updateLocation(lat, lng) {
            // Update marker position
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng]);
            
            // Update database
            fetch('update_driver_location.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                latitude: lat,
                longitude: lng
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                console.log('Location updated successfully');
              } else {
                console.error('Failed to update location:', data.error);
              }
            })
            .catch(error => {
              console.error('Error updating location:', error);
            });
          }
          
          // Clean up when page unloads
          window.addEventListener('beforeunload', function() {
            if (watchId) {
              navigator.geolocation.clearWatch(watchId);
            }
          });
        </script>
        <a href="logout.php"
          class="inline-block mt-4 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white px-6 py-2 rounded-full font-bold shadow transition">Logout</a>
      </div>
    </div>
  </main>
  <?php require_once './assets/components/footer.php'; ?>
</body>

</html>