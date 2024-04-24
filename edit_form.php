<?php
require_once './assets/components/head.php';
require_once './assets/components/nav.php';

include 'database.php';

if (isset($_POST['edit_submit'])){
  $id = $_POST['id'];
  $collection_location = $_POST['collection_location'];
  $pick_up_date = $_POST['date'];
  $pick_up_time = $_POST['Time'];
  $phone_number = $_POST['number'];
  $description = $_POST['description'];
  $urgency = $_POST['urgency'];

  // Enclose string values within single quotes
  $collection_location = mysqli_real_escape_string($conn, $collection_location);
  $pick_up_date = mysqli_real_escape_string($conn, $pick_up_date);
  $pick_up_time = mysqli_real_escape_string($conn, $pick_up_time);
  $phone_number = mysqli_real_escape_string($conn, $phone_number);
  $description = mysqli_real_escape_string($conn, $description);
  $urgency = mysqli_real_escape_string($conn, $urgency);

  $sql = "UPDATE requests
  SET 
      collection_location = '$collection_location',
      pick_up_date = '$pick_up_date',
      pick_up_time = '$pick_up_time',
      phone_number = '$phone_number',
      description = '$description',
      urgency = '$urgency'
  WHERE
      id = $id";
  
  $result = mysqli_query($conn,$sql);
  if ($result){
    header("location: myRequest.php?message=Updated Sucessfully");
  }else {
    header("location: myRequest.php?message=Not Updated!");


  }
}
?>