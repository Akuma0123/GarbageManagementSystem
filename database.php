<?php
$host= "localhost";
$username = "root";
$password = "abcd1234";
$database = "project";

$conn = mysqli_connect($host,$username,$password,$database);
if(!$conn){
  die("");
}