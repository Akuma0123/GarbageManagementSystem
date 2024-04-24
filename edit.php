<?php

require_once './assets/components/head.php';
require_once './assets/components/nav.php';

include 'database.php';

if (isset($_POST['edit'])){
  $id = $_POST['request_id'];
  $sql = "SELECT * FROM requests WHERE id =".$id;
  $result = mysqli_query($conn,$sql);
  $row = mysqli_fetch_array($result);
  
}

?>
<div class="container">
    <h1>Garbage Collection Edit Request Form</h1>
    <form action="edit_form.php" method="post" enctype="multipart/form-data">

        <label for="collection_location">Collection Location</label>
        <input type="text" id="collection_location" name="collection_location" placeholder="Basantapur, Kathmandu"
            required value="<?php echo $row['collection_location'];?>">

        <label for="date">Pick Up Date</label>
        <input type="date" id="date" name="date" required>

        <label for="Time">Pick Up Time</label>
        <input type="time" id="Time" name="Time" required>

        <label for="number">Phone No</label>
        <input type="tel" id="number" name="number" placeholder="Enter Number" required  value="<?php echo $row['phone_number'];?>">

        <label for="urgency">Urgency Level:</label><br>
        <select id="urgency" name="urgency" required>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>

        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Write something.." style="height:200px"
            required  value="<?php echo $row['description'];?>"></textarea>
            <input type="hidden" name="id" value="<?php echo $id;?>"
            >

        <button type="submit" name="edit_submit">Edit Request Collection</button>
    </form>
</div>


