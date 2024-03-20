<?php
$host= "localhost";
$username = "root";
$password = "1234";
$database = "project";

$conn = mysqli_connect($host,$username,$password,$database);
if(!$conn){
  die("");
}