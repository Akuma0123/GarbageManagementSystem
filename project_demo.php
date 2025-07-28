<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dijkstra Optimized Truck Demo</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 350px;
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

        .status.optimizing {
            background: #d1ecf1;
            color: #0c5460;
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-width: 250px;
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

        .route-info {
            position: absolute;
            bottom: 10px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 300px;
        }

        .route-info h4 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .route-step {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .route-step.completed {
            color: #4CAF50;
        }

        .route-step.current {
            color: #ff6b35;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <div class="controls">
        <h3><i class="fa-solid fa-route"></i> Dijkstra Optimized Demo</h3>
        <button id="optimizeRoute" onclick="optimizeRoute()">
            <i class="fa-solid fa-calculator"></i> Optimize Route
        </button>
        <button id="startDemo" onclick="startDemo()" disabled>
            <i class="fa-solid fa-play"></i> Start Optimized Demo
        </button>
        <button id="pauseDemo" onclick="pauseDemo()" disabled>
            <i class="fa-solid fa-pause"></i> Pause
        </button>
        <button id="resetDemo" onclick="resetDemo()">
            <i class="fa-solid fa-undo"></i> Reset
        </button>

        <div id="status" class="status pending">
            <i class="fa-solid fa-clock"></i> Ready to optimize route
        </div>
    </div>

    <div class="truck-info">
        <h4><i class="fa-solid fa-truck"></i> Truck Status</h4>
        <p><strong>Location:</strong> <span id="truckLocation">Basantapur</span></p>
        <p><strong>Speed:</strong> <span id="truckSpeed">0 km/h</span></p>
        <p><strong>Status:</strong> <span id="truckStatus">Idle</span></p>
        <p><strong>Current Task:</strong> <span id="currentTask">None</span></p>
        <p><strong>Total Distance:</strong> <span id="totalDistance">0 km</span></p>

        <div class="progress-bar">
            <div id="progressFill" class="progress-fill"></div>
        </div>
        <p id="progressText">0% Complete</p>
    </div>

    <div class="route-info">
        <h4><i class="fa-solid fa-route"></i> Optimized Route</h4>
        <div id="routeSteps"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Configuration
        const config = {
            center: [27.703772132527337, 85.3058176944241], // Basantapur
            truckSpeed: 100, // km/h
            collectionTime: 3, // minutes per stop
            requests: [
                {
                    id: 1,
                    location: "Thamel",
                    coords: [27.71703164638153, 85.31578477564008],
                    description: "Household waste collection",
                    urgency: "medium"
                },
                {
                    id: 2,
                    location: "Durbar Marg",
                    coords: [27.712620942915343, 85.31773870894052],
                    description: "Commercial waste pickup",
                    urgency: "medium"
                },
                {
                    id: 3,
                    location: "Lazimpat",
                    coords: [27.721552542772734, 85.32034992798435],
                    description: "Restaurant waste",
                    urgency: "medium"
                },
                {
                    id: 4,
                    location: "Baneshwor",
                    coords: [27.689060248453945, 85.33269434151663],
                    description: "Office waste",
                    urgency: "medium"
                }
            ]
        };

        // Global variables
        let map, truckMarker, requestMarkers = [];
        let optimizedRoute = [];
        let currentRequestIndex = 0;
        let isDemoRunning = false;
        let demoInterval;
        let currentPosition = [...config.center];
        let targetPosition = null;
        let progress = 0;
        let totalDistance = 0;

        // Initialize map
        function initMap() {
            map = L.map('map').setView(config.center, 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Create truck marker
            const truckIcon = L.divIcon({
                className: 'custom-truck-icon',
                html: '<i class="fa-solid fa-truck" style="color: #ff6b35; font-size: 24px;"></i>',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            truckMarker = L.marker(config.center, { icon: truckIcon })
                .addTo(map)
                .bindPopup('<b>Garbage Collection Truck</b><br>Status: Ready for optimization');

            // Add request markers
            addRequestMarkers();
        }

        // Add request markers to map
        function addRequestMarkers() {
            config.requests.forEach((request, index) => {
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

        // Optimize route using Dijkstra's algorithm
        function optimizeRoute() {
            document.getElementById('status').className = 'status optimizing';
            document.getElementById('status').innerHTML = '<i class="fa-solid fa-calculator"></i> Optimizing route with Dijkstra algorithm...';

            // Build distance graph
            const locations = {
                'center': { coords: config.center, name: 'Basantapur' },
                ...config.requests.reduce((acc, req) => {
                    acc[req.id] = { coords: req.coords, name: req.location };
                    return acc;
                }, {})
            };

            const graph = buildDistanceGraph(locations);
            const optimizedPath = dijkstraOptimization(graph, 'center');

            // Convert path to route
            optimizedRoute = optimizedPath.map(nodeId => {
                if (nodeId === 'center') {
                    return { type: 'center', coords: config.center, name: 'Basantapur' };
                } else {
                    const request = config.requests.find(r => r.id == nodeId);
                    return { type: 'request', ...request };
                }
            });

            // Calculate total distance
            totalDistance = calculateTotalDistance(optimizedRoute);
            document.getElementById('totalDistance').textContent = `${totalDistance.toFixed(2)} km`;

            // Update route display
            updateRouteDisplay();

            document.getElementById('status').className = 'status pending';
            document.getElementById('status').innerHTML = '<i class="fa-solid fa-check"></i> Route optimized! Ready to start demo.';
            document.getElementById('startDemo').disabled = false;
        }

        // Build distance graph
        function buildDistanceGraph(locations) {
            const graph = {};

            Object.keys(locations).forEach(fromId => {
                graph[fromId] = {};
                Object.keys(locations).forEach(toId => {
                    if (fromId !== toId) {
                        const distance = calculateDistance(
                            locations[fromId].coords,
                            locations[toId].coords
                        );
                        graph[fromId][toId] = distance;
                    }
                });
            });

            return graph;
        }

        // Dijkstra's algorithm for route optimization
        function dijkstraOptimization(graph, start) {
            const distances = {};
            const previous = {};
            const queue = {};

            // Initialize
            Object.keys(graph).forEach(node => {
                distances[node] = Infinity;
                previous[node] = null;
                queue[node] = true;
            });
            distances[start] = 0;

            while (Object.keys(queue).length > 0) {
                // Find node with minimum distance
                let minNode = null;
                Object.keys(queue).forEach(node => {
                    if (minNode === null || distances[node] < distances[minNode]) {
                        minNode = node;
                    }
                });

                if (minNode === null) break;

                
                delete queue[minNode];

                // Update neighbors
                Object.keys(graph[minNode]).forEach(neighbor => {
                    if (queue[neighbor]) {
                        const alt = distances[minNode] + graph[minNode][neighbor];
                        if (alt < distances[neighbor]) {
                            distances[neighbor] = alt;
                            previous[neighbor] = minNode;
                        }
                    }
                });
            }

            // Build path (nearest neighbor approach for TSP)
            const path = ['center'];
            const visited = new Set(['center']);

            while (visited.size < Object.keys(graph).length) {
                let nearest = null;
                let minDist = Infinity;

                Object.keys(graph).forEach(node => {
                    if (!visited.has(node)) {
                        const dist = graph[path[path.length - 1]][node];
                        if (dist < minDist) {
                            minDist = dist;
                            nearest = node;
                        }
                    }
                });

                if (nearest) {
                    path.push(nearest);
                    visited.add(nearest);
                }
            }

            return path;
        }

        // Calculate total distance of optimized route
        function calculateTotalDistance(route) {
            let total = 0;
            for (let i = 1; i < route.length; i++) {
                total += calculateDistance(route[i - 1].coords, route[i].coords);
            }
            return total;
        }

        // Update route display
        function updateRouteDisplay() {
            const routeSteps = document.getElementById('routeSteps');
            let html = '';

            optimizedRoute.forEach((step, index) => {
                const stepClass = index < currentRequestIndex ? 'completed' :
                    index === currentRequestIndex ? 'current' : '';
                const stepIcon = index < currentRequestIndex ? 'fa-check-circle' :
                    index === currentRequestIndex ? 'fa-truck' : 'fa-map-marker-alt';

                html += `
                    <div class="route-step ${stepClass}">
                        <i class="fa-solid ${stepIcon}"></i> 
                        ${index + 1}. ${step.name}
                        ${step.type === 'request' ? ` (Request #${step.id})` : ''}
                    </div>
                `;
            });

            routeSteps.innerHTML = html;
        }

        // Start demo
        function startDemo() {
            if (isDemoRunning || optimizedRoute.length === 0) return;

            isDemoRunning = true;
            currentRequestIndex = 0;
            progress = 0;

            document.getElementById('startDemo').disabled = true;
            document.getElementById('pauseDemo').disabled = false;
            document.getElementById('status').className = 'status collecting';
            document.getElementById('status').innerHTML = '<i class="fa-solid fa-truck"></i> Following optimized route - Truck is moving';

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
            optimizedRoute = [];
            currentPosition = [...config.center];
            targetPosition = null;
            totalDistance = 0;

            truckMarker.setLatLng(config.center);
            truckMarker.getPopup().setContent('<b>Garbage Collection Truck</b><br>Status: Ready for optimization');

            // Clear route lines
            map.eachLayer(layer => {
                if (layer instanceof L.Polyline) {
                    map.removeLayer(layer);
                }
            });

            // Reset markers
            requestMarkers.forEach(marker => {
                marker.setIcon(L.marker().getIcon());
            });

            document.getElementById('startDemo').disabled = true;
            document.getElementById('pauseDemo').disabled = true;
            document.getElementById('status').className = 'status pending';
            document.getElementById('status').innerHTML = '<i class="fa-solid fa-clock"></i> Ready to optimize route';

            updateTruckInfo();
            updateRouteDisplay();
        }

        // Move to next request
        function moveToNextRequest() {
            if (currentRequestIndex >= optimizedRoute.length) {
                completeDemo();
                return;
            }

            const step = optimizedRoute[currentRequestIndex];
            targetPosition = step.coords;

            updateTruckStatus(`Moving to ${step.name}`);
            updateCurrentTask(`Following optimized route to ${step.name}`);
            updateRouteDisplay();

            // Calculate distance and time
            const distance = calculateDistance(currentPosition, targetPosition);
            const timeToReach = (distance / config.truckSpeed) * 60; // minutes

            // Simulate movement with real road routing
            simulateMovement(targetPosition, timeToReach, () => {
                // Arrived at destination
                updateTruckStatus(`Arrived at ${step.name}`);

                if (step.type === 'request') {
                    updateCurrentTask(`Collecting garbage from Request #${step.id}`);

                    // Simulate collection time
                    setTimeout(() => {
                        updateTruckStatus(`Completed collection at ${step.name}`);
                        updateCurrentTask(`Finished Request #${step.id}`);

                        // Mark as completed
                        const requestIndex = config.requests.findIndex(r => r.id === step.id);
                        if (requestIndex >= 0) {
                            requestMarkers[requestIndex].setIcon(L.divIcon({
                                className: 'completed-icon',
                                html: '<i class="fa-solid fa-check-circle" style="color: #4CAF50; font-size: 24px;"></i>',
                                iconSize: [24, 24],
                                iconAnchor: [12, 12]
                            }));
                        }

                        currentRequestIndex++;
                        progress = (currentRequestIndex / optimizedRoute.length) * 100;
                        updateProgress();
                        updateRouteDisplay();

                        // Move to next request
                        setTimeout(moveToNextRequest, 2000);
                    }, config.collectionTime * 1000);
                } else {
                    // Center point - just pass through
                    currentRequestIndex++;
                    progress = (currentRequestIndex / optimizedRoute.length) * 100;
                    updateProgress();
                    updateRouteDisplay();
                    setTimeout(moveToNextRequest, 1000);
                }
            });
        }

        // Simulate truck movement with real road routing
        function simulateMovement(target, duration, onComplete) {
            const apiKey = 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6ImM4ODA1Nzg5NmZmMTQ5ZGNhN2IyMjg4ZjUxMTkyNmNkIiwiaCI6Im11cm11cjY0In0=';

            
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
                totalDistance += calculateDistance(coords[i - 1], coords[i]);
            }
            return totalDistance;
        }

        // Get position along route at specific distance
        function getPositionAlongRoute(coords, targetDistance) {
            let currentDistance = 0;

            for (let i = 1; i < coords.length; i++) {
                const segmentDistance = calculateDistance(coords[i - 1], coords[i]);

                if (currentDistance + segmentDistance >= targetDistance) {
                    // Interpolate within this segment
                    const segmentProgress = (targetDistance - currentDistance) / segmentDistance;
                    return [
                        coords[i - 1][0] + (coords[i][0] - coords[i - 1][0]) * segmentProgress,
                        coords[i - 1][1] + (coords[i][1] - coords[i - 1][1]) * segmentProgress
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
            document.getElementById('status').innerHTML = '<i class="fa-solid fa-check-circle"></i> Optimized route completed - All collections finished';

            updateTruckStatus('Demo completed');
            updateCurrentTask('All requests completed');
            updateProgress();
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
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(pos1[0] * Math.PI / 180) * Math.cos(pos2[0] * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function () {
            initMap();
        });
    </script>
</body>

</html>