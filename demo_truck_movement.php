<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truck Movement Demo - Garbage Collection</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        
        #map {
            height: 100vh;
            width: 100%;
        }
        
        .controls {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 300px;
        }
        
        .controls h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .controls button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            margin: 5px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .controls button:hover {
            background: #45a049;
        }
        
        .controls button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .status {
            margin-top: 10px;
            padding: 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status.collecting {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status.completed {
            background: #d4edda;
            color: #155724;
        }
        
        .truck-info {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-width: 200px;
        }
        
        .truck-info h4 {
            margin: 0 0 10px 0;
            color: #ff6b35;
        }
        
        .truck-info p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            margin: 5px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: #4CAF50;
            border-radius: 4px;
            width: 0%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    
    <div class="controls">
        <h3><i class="fa-solid fa-truck"></i> Demo Controls</h3>
        <button id="startDemo" onclick="startDemo()">
            <i class="fa-solid fa-play"></i> Start Demo
        </button>
        <button id="pauseDemo" onclick="pauseDemo()" disabled>
            <i class="fa-solid fa-pause"></i> Pause
        </button>
        <button id="resetDemo" onclick="resetDemo()">
            <i class="fa-solid fa-undo"></i> Reset
        </button>
        <button id="addRequest" onclick="addRandomRequest()">
            <i class="fa-solid fa-plus"></i> Add Request
        </button>
        
        <div id="status" class="status pending">
            <i class="fa-solid fa-clock"></i> Ready to start demo
        </div>
    </div>
    
    <div class="truck-info">
        <h4><i class="fa-solid fa-truck"></i> Truck Status</h4>
        <p><strong>Location:</strong> <span id="truckLocation">Basantapur</span></p>
        <p><strong>Speed:</strong> <span id="truckSpeed">0 km/h</span></p>
        <p><strong>Status:</strong> <span id="truckStatus">Idle</span></p>
        <p><strong>Current Task:</strong> <span id="currentTask">None</span></p>
        
        <div class="progress-bar">
            <div id="progressFill" class="progress-fill"></div>
        </div>
        <p id="progressText">0% Complete</p>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Demo configuration
        const config = {
            center: [27.7046, 85.3096], // Basantapur
            truckSpeed: 100, // km/h
            collectionTime: 5, // minutes per stop
            demoRequests: [
                {
                    id: 1,
                    location: "Thamel",
                    coords: [27.7172, 85.3240],
                    description: "Household waste collection",
                    urgency: "medium"
                },
                {
                    id: 2,
                    location: "Durbar Marg",
                    coords: [27.7120, 85.3170],
                    description: "Commercial waste pickup",
                    urgency: "high"
                },
                {
                    id: 3,
                    location: "Lazimpat",
                    coords: [27.7200, 85.3300],
                    description: "Restaurant waste",
                    urgency: "low"
                }
            ]
        };

        // Global variables
        let map, truckMarker, requestMarkers = [];
        let currentRequestIndex = 0;
        let isDemoRunning = false;
        let demoInterval;
        let currentPosition = [...config.center];
        let targetPosition = null;
        let progress = 0;

        // Initialize map
        function initMap() {
            map = L.map('map').setView(config.center, 14);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Create truck marker with custom icon
            const truckIcon = L.divIcon({
                className: 'custom-truck-icon',
                html: '<i class="fa-solid fa-truck" style="color: #ff6b35; font-size: 24px;"></i>',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            truckMarker = L.marker(config.center, { icon: truckIcon })
                .addTo(map)
                .bindPopup('<b>Garbage Collection Truck</b><br>Status: Ready for pickup');

            // Add request markers
            addRequestMarkers();
        }

        // Add request markers to map
        function addRequestMarkers() {
            config.demoRequests.forEach(request => {
                const urgencyColors = {
                    'high': '#ff4444',
                    'medium': '#ffaa00',
                    'low': '#44ff44'
                };

                const marker = L.marker(request.coords)
                    .addTo(map)
                    .bindPopup(`
                        <b>Request #${request.id}</b><br>
                        <b>Location:</b> ${request.location}<br>
                        <b>Description:</b> ${request.description}<br>
                        <b>Urgency:</b> <span style="color: ${urgencyColors[request.urgency]}">${request.urgency}</span>
                    `);

                // Add urgency indicator
                const urgencyIcon = L.divIcon({
                    className: 'urgency-icon',
                    html: `<div style="background: ${urgencyColors[request.urgency]}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;"></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                L.marker(request.coords, { icon: urgencyIcon })
                    .addTo(map)
                    .bindPopup(`Urgency: ${request.urgency}`);

                requestMarkers.push(marker);
            });
        }

        // Start demo
        function startDemo() {
            if (isDemoRunning) return;
            
            isDemoRunning = true;
            currentRequestIndex = 0;
            progress = 0;
            
            document.getElementById('startDemo').disabled = true;
            document.getElementById('pauseDemo').disabled = false;
            document.getElementById('status').className = 'status collecting';
            document.getElementById('status').innerHTML = '<i class="fa-solid fa-truck"></i> Demo running - Truck is moving';
            
            moveToNextRequest();
        }

        // Pause demo
        function pauseDemo() {
            isDemoRunning = false;
            clearInterval(demoInterval);
            
            document.getElementById('startDemo').disabled = false;
            document.getElementById('pauseDemo').disabled = true;
            document.getElementById('status').className = 'status pending';
            document.getElementById('status').innerHTML = '<i class="fa-solid fa-pause"></i> Demo paused';
            
            updateTruckStatus('Paused');
        }

        // Reset demo
        function resetDemo() {
            isDemoRunning = false;
            clearInterval(demoInterval);
            
            currentRequestIndex = 0;
            progress = 0;
            currentPosition = [...config.center];
            targetPosition = null;
            
            truckMarker.setLatLng(config.center);
            truckMarker.getPopup().setContent('<b>Garbage Collection Truck</b><br>Status: Ready for pickup');
            
            document.getElementById('startDemo').disabled = false;
            document.getElementById('pauseDemo').disabled = true;
            document.getElementById('status').className = 'status pending';
            document.getElementById('status').innerHTML = '<i class="fa-solid fa-clock"></i> Ready to start demo';
            
            updateTruckInfo();
        }

        // Move to next request
        function moveToNextRequest() {
            if (currentRequestIndex >= config.demoRequests.length) {
                completeDemo();
                return;
            }

            const request = config.demoRequests[currentRequestIndex];
            targetPosition = request.coords;
            
            updateTruckStatus(`Moving to ${request.location}`);
            updateCurrentTask(`Heading to Request #${request.id} - ${request.location}`);
            
            // Calculate distance and time
            const distance = calculateDistance(currentPosition, targetPosition);
            const timeToReach = (distance / config.truckSpeed) * 60; // minutes
            
            // Simulate movement
            simulateMovement(targetPosition, timeToReach, () => {
                // Arrived at destination
                updateTruckStatus(`Arrived at ${request.location}`);
                updateCurrentTask(`Collecting garbage from Request #${request.id}`);
                
                // Simulate collection time
                setTimeout(() => {
                    updateTruckStatus(`Completed collection at ${request.location}`);
                    updateCurrentTask(`Finished Request #${request.id}`);
                    
                    // Mark as completed
                    requestMarkers[currentRequestIndex].setIcon(L.divIcon({
                        className: 'completed-icon',
                        html: '<i class="fa-solid fa-check-circle" style="color: #4CAF50; font-size: 24px;"></i>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    }));
                    
                    currentRequestIndex++;
                    progress = (currentRequestIndex / config.demoRequests.length) * 100;
                    updateProgress();
                    
                    // Move to next request
                    setTimeout(moveToNextRequest, 2000);
                }, config.collectionTime * 1000);
            });
        }

        // Simulate truck movement with real road routing
        function simulateMovement(target, duration, onComplete) {
            const apiKey = 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6ImM4ODA1Nzg5NmZmMTQ5ZGNhN2IyMjg4ZjUxMTkyNmNkIiwiaCI6Im11cm11cjY0In0=';
            
            // Get route from OpenRouteService
            fetch(`https://api.openrouteservice.org/v2/directions/driving-car?api_key=${apiKey}&start=${currentPosition[1]},${currentPosition[0]}&end=${target[1]},${target[0]}`)
                .then(response => response.json())
                .then(data => {
                    if (data.features && data.features.length > 0) {
                        // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                        const routeCoords = data.features[0].geometry.coordinates.map(c => [c[1], c[0]]);
                        
                        // Draw the route on map
                        const routeLine = L.polyline(routeCoords, { 
                            color: '#ff6b35', 
                            weight: 4, 
                            opacity: 0.7 
                        }).addTo(map);
                        
                        // Animate truck along the route
                        animateAlongRoute(routeCoords, duration, onComplete, routeLine);
                    } else {
                        // Fallback to straight line if no route found
                        console.warn('No route found, using straight line');
                        animateStraightLine(target, duration, onComplete);
                    }
                })
                .catch(error => {
                    console.error('Error fetching route:', error);
                    // Fallback to straight line
                    animateStraightLine(target, duration, onComplete);
                });
        }

        // Animate truck along the actual road route
        function animateAlongRoute(routeCoords, duration, onComplete, routeLine) {
            const startTime = Date.now();
            const totalDistance = calculateRouteDistance(routeCoords);
            let currentDistance = 0;
            
            demoInterval = setInterval(() => {
                const elapsed = (Date.now() - startTime) / 1000;
                const progress = Math.min(elapsed / (duration * 60), 1);
                
                // Calculate position along route
                const targetDistance = totalDistance * progress;
                const position = getPositionAlongRoute(routeCoords, targetDistance);
                
                if (position) {
                    currentPosition = position;
                    truckMarker.setLatLng(currentPosition);
                    updateTruckLocation();
                    updateTruckSpeed();
                }
                
                if (progress >= 1) {
                    clearInterval(demoInterval);
                    currentPosition = routeCoords[routeCoords.length - 1];
                    onComplete();
                }
            }, 100);
        }

        // Fallback: Animate in straight line
        function animateStraightLine(target, duration, onComplete) {
            const startTime = Date.now();
            const startPos = [...currentPosition];
            
            demoInterval = setInterval(() => {
                const elapsed = (Date.now() - startTime) / 1000;
                const progress = Math.min(elapsed / (duration * 60), 1);
                
                // Interpolate position
                currentPosition[0] = startPos[0] + (target[0] - startPos[0]) * progress;
                currentPosition[1] = startPos[1] + (target[1] - startPos[1]) * progress;
                
                // Update truck marker
                truckMarker.setLatLng(currentPosition);
                updateTruckLocation();
                updateTruckSpeed();
                
                if (progress >= 1) {
                    clearInterval(demoInterval);
                    currentPosition = [...target];
                    onComplete();
                }
            }, 100);
        }

        // Calculate total distance of route
        function calculateRouteDistance(coords) {
            let totalDistance = 0;
            for (let i = 1; i < coords.length; i++) {
                totalDistance += calculateDistance(coords[i-1], coords[i]);
            }
            return totalDistance;
        }

        // Get position along route at specific distance
        function getPositionAlongRoute(coords, targetDistance) {
            let currentDistance = 0;
            
            for (let i = 1; i < coords.length; i++) {
                const segmentDistance = calculateDistance(coords[i-1], coords[i]);
                
                if (currentDistance + segmentDistance >= targetDistance) {
                    // Interpolate within this segment
                    const segmentProgress = (targetDistance - currentDistance) / segmentDistance;
                    return [
                        coords[i-1][0] + (coords[i][0] - coords[i-1][0]) * segmentProgress,
                        coords[i-1][1] + (coords[i][1] - coords[i-1][1]) * segmentProgress
                    ];
                }
                
                currentDistance += segmentDistance;
            }
            
            return coords[coords.length - 1];
        }

        // Complete demo
        function completeDemo() {
            isDemoRunning = false;
            clearInterval(demoInterval);
            
            document.getElementById('startDemo').disabled = false;
            document.getElementById('pauseDemo').disabled = true;
            document.getElementById('status').className = 'status completed';
            document.getElementById('status').innerHTML = '<i class="fa-solid fa-check-circle"></i> Demo completed - All collections finished';
            
            updateTruckStatus('Demo completed');
            updateCurrentTask('All requests completed');
            updateProgress();
        }

        // Add random request
        function addRandomRequest() {
            const randomLat = config.center[0] + (Math.random() - 0.5) * 0.02;
            const randomLng = config.center[1] + (Math.random() - 0.5) * 0.02;
            const urgencies = ['high', 'medium', 'low'];
            const locations = ['Random Location 1', 'Random Location 2', 'Random Location 3'];
            
            const newRequest = {
                id: config.demoRequests.length + 1,
                location: locations[Math.floor(Math.random() * locations.length)],
                coords: [randomLat, randomLng],
                description: 'Random garbage collection request',
                urgency: urgencies[Math.floor(Math.random() * urgencies.length)]
            };
            
            config.demoRequests.push(newRequest);
            
            // Add marker to map
            const marker = L.marker(newRequest.coords)
                .addTo(map)
                .bindPopup(`
                    <b>Request #${newRequest.id}</b><br>
                    <b>Location:</b> ${newRequest.location}<br>
                    <b>Description:</b> ${newRequest.description}<br>
                    <b>Urgency:</b> ${newRequest.urgency}
                `);
            
            requestMarkers.push(marker);
            
            alert(`Added new request at ${newRequest.location}`);
        }

        // Update functions
        function updateTruckStatus(status) {
            document.getElementById('truckStatus').textContent = status;
        }

        function updateCurrentTask(task) {
            document.getElementById('currentTask').textContent = task;
        }

        function updateTruckLocation() {
            document.getElementById('truckLocation').textContent = 
                `${currentPosition[0].toFixed(4)}, ${currentPosition[1].toFixed(4)}`;
        }

        function updateTruckSpeed() {
            document.getElementById('truckSpeed').textContent = `${config.truckSpeed} km/h`;
        }

        function updateProgress() {
            document.getElementById('progressFill').style.width = `${progress}%`;
            document.getElementById('progressText').textContent = `${Math.round(progress)}% Complete`;
        }

        function updateTruckInfo() {
            updateTruckLocation();
            updateTruckSpeed();
            updateProgress();
        }

        // Utility functions
        function calculateDistance(pos1, pos2) {
            const R = 6371; // Earth's radius in km
            const dLat = (pos2[0] - pos1[0]) * Math.PI / 180;
            const dLon = (pos2[1] - pos1[1]) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                     Math.cos(pos1[0] * Math.PI / 180) * Math.cos(pos2[0] * Math.PI / 180) *
                     Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });
    </script>
</body>
</html> 