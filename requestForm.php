<?php
$page = 'request';
require_once './assets/components/head.php';
require_once './assets/components/nav.php';

// Database connection
include 'database.php';

// Define constants for minimum and maximum lengths
define('MIN_LOCATION_LENGTH', 5);
define('MAX_LOCATION_LENGTH', 50);
define('MIN_DESCRIPTION_LENGTH', 10);
define('MAX_DESCRIPTION_LENGTH', 500);

// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize form inputs
    $collection_location = trim($_POST["collection_location"]);
    $date = trim($_POST["date"]);
    $time = trim($_POST["Time"]); // corrected to "Time" instead of "time"
    $phone_number = trim($_POST["phone_number"]);

    $urgency = trim($_POST["urgency"]);
    $description = trim($_POST["description"]);
    $imageFiles = $_FILES["imageFiles"];

    // Validate date
    if (!empty($date) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        echo "<script>alert('Invalid date');</script>";
    }

    // Validate time
    if (!empty($time) && !preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $time)) {
        echo "<script>alert('Invalid Time');</script>";
    }

    // Validate length of location and description
    if (strlen($collection_location) < MIN_LOCATION_LENGTH || strlen($collection_location) > MAX_LOCATION_LENGTH) {
        echo "<script>alert('Location length should be between 5 and 50 characters.');</script>";
    }

    if (strlen($description) < MIN_DESCRIPTION_LENGTH || strlen($description) > MAX_DESCRIPTION_LENGTH) {
        echo "<script>alert('Description length should be between 10 and 500 characters.');</script>";
    }

    // Validate urgency level
    $valid_urgency_levels = array("high", "medium", "low");
    if (!in_array($urgency, $valid_urgency_levels)) {
        echo "<script>alert('Invalid urgency level');</script>";
    }

        // Validate image files size and move them to a directory
        $uploadDirectory = './uploads/';
        $uploadedFilePaths = [];
        foreach ($imageFiles["tmp_name"] as $key => $tmp_name) {
            $file_extension = pathinfo($imageFiles["name"][$key], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $uploadDirectory . $file_name;
            if (move_uploaded_file($tmp_name, $file_path)) {
                $uploadedFilePaths[] = $file_path;
            } else {
                echo "<script>alert('Error uploading file: $file_name');</script>";
            }
        }

        // Prepare and execute SQL insert query using prepared statements
        $sql = "INSERT INTO requests (collection_location, pick_up_date, pick_up_time, phone_number, urgency, description, img_path_1, img_path_2, img_path_3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssss", $collection_location, $date, $time, $phone_number, $urgency, $description, $uploadedFilePaths[0], $uploadedFilePaths[1], $uploadedFilePaths[2]);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Request submitted successfully')</script>";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
?>


<div class="container">
    <h1>Garbage Collection Request Form</h1>
    <form action="requestForm.php" method="post" enctype="multipart/form-data">

        <label for="collection_location">Collection Location</label>
        <input type="text" id="collection_location" name="collection_location" placeholder="Basantapur, Kathmandu" required>

        <label for="date">Pick Up Date</label>
        <input type="date" id="date" name="date" required>

        
        <label for="Time">Pick Up Time</label>
        <input type="time" id="Time" name="Time" required> 
        
        <label for="Number">Phone No</label>
        <input type="number" id="number" name="number" placeholder="Enter Number" required>
        
        <label for="urgency">Urgency Level:</label><br>
        <select id="urgency" name="urgency" required>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>

        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Write something.." style="height:200px" required></textarea>
        
        <input type="file" name="imageFiles[]" id="imageFiles" multiple accept="image/jpeg, image/png, image/jpg" onchange="validateFiles(this)" required>
        <button type="submit">Request Collection</button>

        <?php
        $statusMsg = ''; 
        // Display status message 
        echo $statusMsg; 
        ?>
    </form>
</div>

<script>
function validateFiles(input) {
    var files = input.files;
    var totalSize = 0;
    
    // Check the number of selected files
    if (files.length > 3) {
        alert("Please select a maximum of 3 images.");
        input.value = ''; // Clear selected files
        return false;
    }
    
    // Calculate total size of selected files
    for (var i = 0; i < files.length; i++) {
        totalSize += files[i].size;
    }
    
    // Check total size of selected files
    if (totalSize > 5 * 1024 * 1024) { // 5MB in bytes
        alert("Total size of images cannot exceed 5MB.");
        input.value = ''; // Clear selected files
        return false;
    }
    
    return true;
}
</script>

<?php
require_once './assets/components/footer.php';
?>
