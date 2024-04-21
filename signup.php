<?php
$page = 'signup';
require_once './assets/components/head.php';
?>
<?php
// signup.php

// Database connection (use the same as login.php)
include 'database.php';

// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["full_name"]);
    $password = htmlspecialchars($_POST["password"]);
    $rePassword = htmlspecialchars($_POST["re-password"]); // Changed to match the input name
    $email = $_POST['email'];

    // Validate name length
    if (strlen($name) > 50) {
        echo "<script> alert('Name must be less than 50 characters'); </script>";
        return;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script> alert('Invalid email format'); </script>";
        return;
    }

    // Validate password length
    if (strlen($password) < 8 || strlen($password) > 16) {
        echo "<script> alert('Password must be between 8 and 16 characters'); </script>";
        return;
    }

    // Check if passwords match
    if ($password !== $rePassword) {
        echo "<script> alert('Passwords do not match'); </script>";
        return;
    }

    // Check if the email already exists
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script> alert('Email already used'); </script>";
        $stmt->close();
        return;
    }

    // Insert user in db
    $username = explode("@", $email)[0]; // Use first part of email as username
    $password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users(name,username,email,password) VALUES(?,?,?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $name, $username, $email, $password);
    $stmt->execute();

    // Check if insertion was successful
    if ($stmt->affected_rows == 1) {
        header('location: login.php');
        exit(); // Use exit instead of die
    } else {
        echo "<script> alert('Failed to sign up. Please try again later.'); </script>";
    }

    $stmt->close(); // Close statement
    $conn->close(); // Close connection
}
?>

<main>
  <form class="form" method="post" action="/signup.php">
  <h1 id="title">Sign Up</h1>
    <input type="text" name="full_name" id="full_name" placeholder="Full Name" required title="Enter name" maxlength="50">
    <input type="text" name="email" id="email" placeholder="E-mail" required title="Enter e-mail address">
    <input type="password" name="password" id="password" placeholder="Password" required title="Enter password" minlength="8" maxlength="16">
    <input type="password" name="re-password" id="re-password" placeholder="Confirm Password" required title="Re-enter password">
    <button class="input" type="submit" id="button">Sign Up</button>
  </form>
  
</main>


