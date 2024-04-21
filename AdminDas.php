<?php
include 'database.php';

// Accept Button Action
if (isset($_POST['accept'])) {
  // Code to update the status of the request in the database
  $requestId = $_POST['request_id']; // Assuming you have a hidden input field for request ID
  // Perform database update query
  // Example: UPDATE requests SET status = 'Accepted' WHERE id = $requestId;

  // Fetch the request details including user_id
  $query = "SELECT * FROM requests WHERE id = ?";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $requestId);
  $stmt->execute();
  $result = $stmt->get_result();
  $request = $result->fetch_assoc(); // Fetch the request details

  // After updating the request status
  // Insert a notification for the user
  $content = "Your request has been accepted."; // Notification content
  $userId = $request['user_id']; // Assuming 'user_id' is the field in your requests table
  $sql = "INSERT INTO notifications (user_id, content) VALUES (?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("is", $userId, $content);
  $stmt->execute();
  $stmt->close();
} 

// Delete Button Action
if (isset($_POST['delete'])) {
  // Code to delete the request from the database
  $requestId = $_POST['request_id']; // Assuming you have a hidden input field for request ID
  // Perform database delete query
  // Example: DELETE FROM requests WHERE id = $requestId

  // Fetch the request details including user_id
  $query = "SELECT * FROM requests WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $requestId);
  $stmt->execute();
  $result = $stmt->get_result();
  $request = $result->fetch_assoc(); // Fetch the request details

  // After deleting the request
  // Insert a notification for the user
  $content = "Your request has been deleted by the admin."; // Notification content
  $userId = $request['user_id']; // Assuming 'user_id' is the field in your requests table
  $sql = "INSERT INTO notifications (user_id, content) VALUES (?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("is", $userId, $content);
  $stmt->execute();
  $stmt->close();
}

// Your existing code to fetch data from the database
$query = "SELECT * FROM requests";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Admin Dashboard</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./assets/css/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body>

  <header class="header">
    <div class="header-content">
      <div class="header-logo">
        <h1 class="logo">ADMIN Dashboard</h1>
      </div>
      <nav class="header-navigation">
        <a href="/user.php" class="<?= $page == 'user' ? 'active-nav' : '' ?>">Users</a>
        <a href="/admin_requests.php" class="<?= $page == 'requests' ? 'active-nav' : '' ?>">Requests</a>
        <a href="/admin_notify.php" class="<?= $page == 'notify' ? 'active-nav' : '' ?>">Notify</a>
      </nav>
    </div>
  </header>

  <main>
    <h2>Requests Table</h2>

    <table>
      <tr>
       <th>User ID</th>
        <th>Location</th>
        <th>Date</th>
        <th>Time</th>
        <th>Phone No</th>
        <th>Urgency</th>
        <th>Description</th>
        <th>Image</th>
        <th>Accept</th>
        <th>Delete</th>
      </tr>
      <?php foreach ($result as $r) { ?>
        <tr>
        <td>
            <?php echo $r['user_id']; ?>
          </td>
          <td>
            <?php echo $r['collection_location']; ?>
          </td>
          <td>
            <?php echo $r['pick_up_date']; ?>
          </td>
          <td>
            <?php echo $r['pick_up_time']; ?>
          </td>
          <td>
            <?php echo $r['phone_number']; ?>
          </td>
          <td>
            <?php echo $r['urgency']; ?>
          </td>
          <td>
            <?php echo $r['description']; ?>
          </td>
          <td class="imgCell">
            <?php if ($r['img_path_1'] != null): ?>
              <img class="request-img" src="<?php echo $r['img_path_1']; ?>" alt="Image">
            <?php endif; ?>
            <?php if (isset($r['img_path_2']) && $r['img_path_2'] != null): ?>
              <img class="request-img" src="<?php echo $r['img_path_2']; ?>" alt="Image">
            <?php endif; ?>
            <?php if ($r['img_path_3'] != null): ?>
              <img class="request-img" src="<?php echo $r['img_path_3']; ?>" alt="Image">
            <?php endif; ?>
          </td>

          <div id="imageModal" class="modal">
            <span class="close">&times;</span>
            <img class="modal-content" id="modalImage">
          </div>
          <td>
            <!-- accept form  -->
            <form method="post">
              <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
              <button type="submit" name="accept" class="acceptBtn">
                <i class="fa-regular fa-circle-check"></i>
              </button>
            </form>
          </td>
          <td>
            <!-- delete form -->
            <form method="post">
              <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
              <button type="submit" name="delete" class="deleteBtn">
                <i class="fa-regular fa-circle-xmark"></i>
              </button>
            </form>
          </td>
        </tr>

      <?php } ?>
    </table>

  </main>
  <script>
    // JavaScript
document.addEventListener('DOMContentLoaded', function() {
  // Get the modal
  var modal = document.getElementById('imageModal');

  // Get the image and insert it inside the modal
  var img = document.querySelectorAll('.request-img');
  var modalImg = document.getElementById('modalImage');

  // Loop through all images and add click event listeners
  img.forEach(function(image) {
    image.addEventListener('click', function() {
      modal.style.display = 'block';
      modalImg.src = this.src;
    });
  });

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName('close')[0];

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = 'none';
  };

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = 'none';
    }
  };
});
  
  </script>
</body>

</html>
