<!DOCTYPE html>
<html>

<head>
  <title>Optimized Route Map</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <style>
    #map {
      height: 100vh;
    }
    .loading {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(0, 0, 0, 0.8);
      color: white;
      padding: 20px;
      border-radius: 10px;
      z-index: 1000;
    }
    .custom-truck-icon {
      background: none;
      border: none;
    }
  </style>
</head>

<body>

  <div id="map"></div>
  <div id="loading" class="loading" style="display: none;">
    <h3>Calculating optimal routes...</h3>
    <p>Please wait while we fetch road-following routes.</p>
  </div>
  
  <!-- Control buttons -->
  <div style="position: absolute; top: 10px; right: 10px; z-index: 1000;">
    <button id="refresh-drivers" style="background: #ff6b35; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; margin-bottom: 10px;">
      <i class="fa-solid fa-sync-alt"></i> Refresh Driver Locations
    </button>
  </div>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <?php
  include 'database.php';
  // === CONFIG ===
  $center = ['id' => 'center', 'lat' => 27.7046, 'lng' => 85.3096]; // Basantapur
  

  // === FETCH REQUEST LOCATIONS ===
  $sql = "SELECT id, latitude, longitude FROM requests WHERE status='accepted'";
  $result = $conn->query($sql);

  $locations = [];
  $locations['center'] = $center;

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $locations[$row['id']] = [
        'lat' => (float) $row['latitude'],
        'lng' => (float) $row['longitude']
      ];
    }
  } else {
    die("No requests found.");
  }
  $conn->close();

  // === HAVERSINE DISTANCE ===
  function haversine($lat1, $lon1, $lat2, $lon2)
  {
    $earth_radius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
      cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
      sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius * $c;
  }

  // === BUILD DISTANCE GRAPH ===
  $graph = [];
  foreach ($locations as $fromId => $fromLoc) {
    foreach ($locations as $toId => $toLoc) {
      if ($fromId !== $toId) {
        $graph[$fromId][$toId] = haversine($fromLoc['lat'], $fromLoc['lng'], $toLoc['lat'], $toLoc['lng']);
      }
    }
  }

  // === DIJKSTRA ===
  function dijkstra($graph, $start)
  {
    $dist = [];
    $prev = [];
    $queue = [];

    foreach ($graph as $node => $edges) {
      $dist[$node] = INF;
      $prev[$node] = null;
      $queue[$node] = true;
    }
    $dist[$start] = 0;

    while (!empty($queue)) {
      $minNode = null;
      foreach ($queue as $node => $_) {
        if ($minNode === null || $dist[$node] < $dist[$minNode]) {
          $minNode = $node;
        }
      }

      foreach ($graph[$minNode] as $neighbor => $weight) {
        $alt = $dist[$minNode] + $weight;
        if ($alt < $dist[$neighbor]) {
          $dist[$neighbor] = $alt;
          $prev[$neighbor] = $minNode;
        }
      }

      unset($queue[$minNode]);
    }

    return [$dist, $prev];
  }

  // === SHORTEST PATH TREE ===
  list($distances, $previous) = dijkstra($graph, 'center');

  // === RECONSTRUCT PATHS ===
  $paths = [];
  foreach ($distances as $node => $distance) {
    if ($node === 'center')
      continue;
    $path = [];
    $cur = $node;
    while ($cur !== null) {
      array_unshift($path, $cur);
      $cur = $previous[$cur];
    }
    $paths[] = $path;
  }

  // === SEND TO JS ===
  echo "<script>\n";
  echo "var locations = " . json_encode($locations) . ";\n";
  echo "var paths = " . json_encode($paths) . ";\n";
  echo "</script>\n";
  ?>

  <script>
    const map = L.map('map').setView([27.7046, 85.3096], 13);
    const apiKey = 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6ImM4ODA1Nzg5NmZmMTQ5ZGNhN2IyMjg4ZjUxMTkyNmNkIiwiaCI6Im11cm11cjY0In0=';

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Add markers for requests
    for (let id in locations) {
      const loc = locations[id];
      const label = id === "center" ? "Center (Basantapur)" : `Request ${id}`;
      L.marker([loc.lat, loc.lng]).addTo(map).bindPopup(label);
    }

    // Function to fetch and display driver locations
    async function loadDriverLocations() {
      try {
        const response = await fetch('get_driver_locations.php');
        const data = await response.json();
        
        if (data.drivers && data.drivers.length > 0) {
          data.drivers.forEach(driver => {
            // Create a custom truck icon
            const truckIcon = L.divIcon({
              className: 'custom-truck-icon',
              html: '<i class="fa-solid fa-truck" style="color: #ff6b35; font-size: 24px;"></i>',
              iconSize: [30, 30],
              iconAnchor: [15, 15]
            });
            
            const marker = L.marker([driver.latitude, driver.longitude], { icon: truckIcon })
              .addTo(map)
              .bindPopup(`
                <div style="text-align: center;">
                  <h4 style="margin: 0 0 5px 0; color: #ff6b35;"><i class="fa-solid fa-truck"></i> ${driver.name}</h4>
                  <p style="margin: 5px 0; font-size: 12px;">${driver.email}</p>
                  <p style="margin: 5px 0; font-size: 11px; color: #666;">Last updated: ${new Date(driver.last_updated).toLocaleString()}</p>
                </div>
              `);
          });
        }
        return Promise.resolve();
      } catch (error) {
        console.error('Error loading driver locations:', error);
        return Promise.reject(error);
      }
    }

    // Load driver locations
    loadDriverLocations();

    // Add refresh button functionality
    document.getElementById('refresh-drivers').addEventListener('click', function() {
      this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Refreshing...';
      this.disabled = true;
      
      // Remove existing driver markers
      map.eachLayer(layer => {
        if (layer._icon && layer._icon.className && layer._icon.className.includes('custom-truck-icon')) {
          map.removeLayer(layer);
        }
      });
      
      // Reload driver locations
      loadDriverLocations().then(() => {
        this.innerHTML = '<i class="fa-solid fa-sync-alt"></i> Refresh Driver Locations';
        this.disabled = false;
      });
    });

    // Function to get route from OpenRouteService
    async function getRoute(start, end) {
      try {
        const response = await fetch(`https://api.openrouteservice.org/v2/directions/driving-car?api_key=${apiKey}&start=${start[1]},${start[0]}&end=${end[1]},${end[0]}`);
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.features && data.features.length > 0) {
          // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
          const coords = data.features[0].geometry.coordinates.map(c => [c[1], c[0]]);
          return coords;
        } else {
          throw new Error('No route found');
        }
      } catch (error) {
        console.error('Error fetching route:', error);
        // Fallback to straight line if API fails
        return [start, end];
      }
    }

    // Function to draw routes with real road paths
    async function drawRoutes() {
      document.getElementById('loading').style.display = 'block';
      
      for (let i = 0; i < paths.length; i++) {
        const path = paths[i];
        
        // Draw route from center to each request location
        if (path.length >= 2) {
          const start = [locations[path[0]].lat, locations[path[0]].lng];
          const end = [locations[path[path.length - 1]].lat, locations[path[path.length - 1]].lng];
          
          try {
            const routeCoords = await getRoute(start, end);
            
            // Draw the route with different colors for each path
            const colors = ['blue', 'red', 'green', 'purple', 'orange', 'brown', 'pink', 'gray'];
            const color = colors[i % colors.length];
            
            L.polyline(routeCoords, { 
              color: color, 
              weight: 4,
              opacity: 0.8
            }).addTo(map).bindPopup(`Route to Request ${path[path.length - 1]}`);
            
            // Add a small delay to avoid overwhelming the API
            await new Promise(resolve => setTimeout(resolve, 200));
            
          } catch (error) {
            console.error(`Error drawing route ${i}:`, error);
            // Fallback to straight line
            const fallbackCoords = path.map(id => [locations[id].lat, locations[id].lng]);
            L.polyline(fallbackCoords, { color: 'gray', weight: 2, opacity: 0.5 }).addTo(map);
          }
        }
      }
      
      document.getElementById('loading').style.display = 'none';
    }

    // Start drawing routes when page loads
    drawRoutes();
  </script>

</body>

</html>