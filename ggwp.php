<?php
$password = 'admin123'; // Replace 'admin123' with the actual password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo $hashed_password;
?>
