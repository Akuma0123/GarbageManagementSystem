<?php

$page = 'request';
require_once './assets/components/head.php';
require_once './assets/components/nav.php';
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        $collection_location = trim($_POST["collection_location"]);
        $latitude = isset($_POST["latitude"]) && $_POST["latitude"] !== '' ? $_POST["latitude"] : null;
        $longitude = isset($_POST["longitude"]) && $_POST["longitude"] !== '' ? $_POST["longitude"] : null;
        $date = trim($_POST["date"]);
        $time = trim($_POST["Time"]);
        $phone_number = trim($_POST["number"]);
        $urgency = trim($_POST["urgency"]);
        $description = trim($_POST["description"]);
        $imageFiles = $_FILES["imageFiles"];

        $errors = [];

        // Haversine formula to check distance from Basantapur
        $basantapurLat = 27.7046;
        $basantapurLon = 85.3076;
        $radius = 10; // 5km
        if ($latitude !== null && $longitude !== null) {
            $earthRadius = 6371; // km
            $dLat = deg2rad($latitude - $basantapurLat);
            $dLon = deg2rad($longitude - $basantapurLon);
            $a = sin($dLat/2) * sin($dLat/2) +
                cos(deg2rad($basantapurLat)) * cos(deg2rad($latitude)) *
                sin($dLon/2) * sin($dLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $distance = $earthRadius * $c;
            if ($distance > $radius) {
                echo "<script>alert('Sorry, we only accept requests within 5km of Basantapur, Kathmandu.'); window.history.back();</script>";
                exit;
            }
        }

        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date) || $date < date("Y-m-d")) {
            $errors[] = 'Invalid or past date.';
        }

        if (!preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $time)) {
            $errors[] = 'Invalid time.';
        }

        if (strlen($collection_location) < 5 || strlen($collection_location) > 50) {
            $errors[] = 'Location length should be between 5 and 50 characters.';
        }

        if (strlen($description) < 10 || strlen($description) > 500) {
            $errors[] = 'Description length should be between 10 and 500 characters.';
        }

        if (!preg_match("/^[0-9]{10}$/", $phone_number)) {
            $errors[] = 'Invalid phone number. Please enter a valid 10-digit number.';
        }

        if (!in_array($urgency, ["high", "medium", "low"])) {
            $errors[] = 'Invalid urgency level.';
        }

        if (!empty($errors)) {
            echo "<script>alert('" . implode("\\n", $errors) . "');</script>";
            exit;
        }

        $uploadDirectory = './uploads/';
        $uploadedFilePaths = [];
        foreach ($imageFiles["tmp_name"] as $key => $tmp_name) {
            $file_extension = pathinfo($imageFiles["name"][$key], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $uploadDirectory . $file_name;
            if (move_uploaded_file($tmp_name, $file_path)) {
                $uploadedFilePaths[] = $file_path;
            } else {
                echo "<script>alert('Error uploading file: {$imageFiles['name'][$key]}');</script>";
                exit;
            }
        }

        $uploadedFilePaths = array_pad($uploadedFilePaths, 3, null); // Ensure 3 image slots

        $sql = "INSERT INTO requests (user_id, collection_location, latitude, longitude, pick_up_date, pick_up_time, phone_number, urgency, description, img_path_1, img_path_2, img_path_3) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssssssssss", $user_id, $collection_location, $latitude, $longitude, $date, $time, $phone_number, $urgency, $description, $uploadedFilePaths[0], $uploadedFilePaths[1], $uploadedFilePaths[2]);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Request submitted successfully'); window.location.href='requestForm.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('You are not logged in. Please log in first.');</script>";
    }
}
?>

<div class="container">
    <h1>Garbage Collection Request Form</h1>
    <form id="requestForm" action="requestForm.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="latitude" id="latitude" required>
        <input type="hidden" name="longitude" id="longitude" required>

        <!-- Map for selecting location -->
        <label for="map" style="font-weight: bold; margin-bottom: 0.5em; display: block;">Select Collection Location on Map <span style="font-weight: normal; color: #666; font-size: 0.95em;">(Drag the marker or click anywhere)</span></label>
        <div id="map" style="height: 320px; margin-bottom: 1em; border: 2px solid #e0e0e0; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.07);"></div>
        <button type="button" onclick="setToCurrentLocation()" style="margin-bottom: 1.5em; background: #4CAF50; color: white; border: none; padding: 8px 18px; border-radius: 5px; font-size: 1em; cursor: pointer; transition: background 0.2s;">Use My Location</button>

        <label for="collection_location">Collection Location
            <span id="collection_location_error" class="error"></span>
        </label>
        <input type="text" id="collection_location" name="collection_location" placeholder="Basantapur, Kathmandu" required>

        <label for="date">Pick Up Date
            <span id="date_error" class="error"></span>
        </label>
        <input type="date" id="date" name="date" required>

        <label for="Time">Pick Up Time
            <span id="time_error" class="error"></span>
        </label>
        <input type="time" id="Time" name="Time" required>

        <label for="number">Phone No
            <span id="phone_error" class="error"></span>
        </label>
        <input type="tel" id="number" name="number" placeholder="Enter Number" required>

        <label for="urgency">Urgency Level:
            <span id="urgency_error" class="error"></span>
        </label>
        <select id="urgency" name="urgency" required>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>

        <label for="description">Description
            <span id="description_error" class="error"></span>
        </label>
        <textarea id="description" name="description" placeholder="Write something.." style="height:200px" required></textarea>

        <label for="imageFiles">Upload Images
            <span id="imageFiles_error" class="error"></span>
        </label>
        <input type="file" name="imageFiles[]" id="imageFiles" multiple accept="image/jpeg, image/png, image/jpg" required>

        <button type="submit">Request Collection</button>
    </form>
</div>

<!-- Client-side validation and geolocation -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script>
    let map, marker;
    // Default center (Basantapur)
    const defaultLat = 27.7046;
    const defaultLng = 85.3076;

    function initMap() {
        map = L.map('map').setView([defaultLat, defaultLng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);
        // Update form fields when marker is moved
        marker.on('dragend', function(e) {
            const {lat, lng} = marker.getLatLng();
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        });
        // Update marker and fields when map is clicked
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
        });
        // Set initial values
        document.getElementById('latitude').value = defaultLat;
        document.getElementById('longitude').value = defaultLng;
    }
    // Button: Use My Location
    function setToCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 16);
                marker.setLatLng([lat, lng]);
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            });
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        var dateInput = document.getElementById('date');
        var timeInput = document.getElementById('Time');
        var today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
        dateInput.addEventListener('change', function() {
            if (dateInput.value === today) {
                var now = new Date();
                var currentTime = now.toISOString().substring(11, 16);
                timeInput.setAttribute('min', currentTime);
            } else {
                timeInput.removeAttribute('min');
            }
        });
        document.getElementById("requestForm").addEventListener("submit", function(event) {
            event.preventDefault();
            var form = event.target;
            var isValid = true;
            function showError(elementId, message) {
                document.getElementById(elementId).innerText = message;
            }
            function clearError(elementId) {
                document.getElementById(elementId).innerText = "";
            }
            function validateField(value, elementId, condition, message) {
                if (condition) {
                    showError(elementId, message);
                    isValid = false;
                } else {
                    clearError(elementId);
                }
            }
            validateField(form.collection_location.value, "collection_location_error", form.collection_location.value.length < 5 || form.collection_location.value.length > 50, "Location must be 5-50 characters.");
            validateField(form.date.value, "date_error", form.date.value === "", "Please select a date.");
            validateField(form.Time.value, "time_error", form.Time.value === "", "Please select a time.");
            validateField(form.number.value, "phone_error", !/^\d{10}$/.test(form.number.value), "Enter a valid 10-digit phone number.");
            validateField(form.description.value.trim(), "description_error", form.description.value.trim() === "", "Please provide a description.");
            var imageFiles = form.imageFiles;
            var totalSize = Array.from(imageFiles.files).reduce((acc, file) => acc + file.size, 0);
            validateField(imageFiles, "imageFiles_error", imageFiles.files.length > 3 || totalSize > 5 * 1024 * 1024, "Select a maximum of 3 images with total size not exceeding 5MB.");
            if (isValid) {
                form.submit();
            }
        });
    });
</script>

<style>
    .error {
        color: red;
        font-size: 0.9em;
        margin-left: 10px;
    }
</style>
