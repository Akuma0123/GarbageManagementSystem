<?php
require_once './assets/components/head.php';
require_once './assets/components/nav.php';
include 'database.php';


$user_id = $_SESSION['user_id'];

// Fetch requests made by the current user
$query = "SELECT * FROM requests WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();


// Delete Button Action
if (isset($_POST['delete'])) {
    // Get the request ID from the form submission
    $requestId = $_POST['request_id']; // Assuming you have a hidden input field for request ID

    // Perform database delete query
    $deleteQuery = "DELETE FROM requests WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $requestId);
    if ($deleteStmt->execute()) {
        // Deletion successful
        // Redirect or perform any other action after deletion
        // For example, redirect to the same page to refresh the list
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        // Deletion failed
        echo "Error deleting request.";
    }
}



?>

        <h2>My Requests Table</h2>

        <table>
            <tr>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Location</th>
                <th>Date</th>
                <th>Time</th>
                <th>Phone No</th>
                <th>Urgency</th>
                <th>Description</th>
                <th>Image</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
            <?php foreach ($result as $r) { ?>
            <tr>
                <td>
                    <?php echo $r['created_at']; ?>
                </td>
                <td>
                    <?php echo $r['updated_at']; ?>
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
                    <!-- edit form -->
                    <form method="post">
                        <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                        <button type="submit" name="accept" class="acceptBtn">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                    </form>
                </td>
                <td>
                    <!-- delete form -->
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this request?');">
                        <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                        <button type="submit" name="delete" class="deleteBtn">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </button>
                    </form>
                </td>
            </tr>

            <?php } ?>
        </table>

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

    </script>

<?php
require_once './assets/components/footer.php';