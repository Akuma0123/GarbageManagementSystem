<?php
$page = 'driver_signup';
require_once './assets/components/head.php';
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["full_name"]);
    $password = htmlspecialchars($_POST["password"]);
    $rePassword = htmlspecialchars($_POST["re-password"]);
    $email = $_POST['email'];

    if (strlen($name) > 100) {
        echo "<script> alert('Name must be less than 100 characters'); </script>";
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script> alert('Invalid email format'); </script>";
        return;
    }
    if (strlen($password) < 8 || strlen($password) > 16) {
        echo "<script> alert('Password must be between 8 and 16 characters'); </script>";
        return;
    }
    if ($password !== $rePassword) {
        echo "<script> alert('Passwords do not match'); </script>";
        return;
    }
    $query = "SELECT * FROM drivers WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<script> alert('Email already used'); </script>";
        $stmt->close();
        return;
    }
    $password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO drivers(name, email, password) VALUES(?,?,?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $name, $email, $password);
    $stmt->execute();
    if ($stmt->affected_rows == 1) {
        header('location: driver_login.php');
        exit();
    } else {
        echo "<script> alert('Failed to sign up. Please try again later.'); </script>";
    }
    $stmt->close();
    $conn->close();
}
?>
<main>
  <form class="form" method="post" action="/driver_signup.php">
  <h1 id="title">Driver Sign Up</h1>
    <input type="text" name="full_name" id="full_name" placeholder="Full Name" required title="Enter name" maxlength="100" pattern="[A-Za-z\s]+" title="Only alphabets and spaces are allowed">
    <input type="text" name="email" id="email" placeholder="E-mail" required title="Enter e-mail address">
    <input type="password" name="password" id="password" placeholder="Password" required title="Enter password" minlength="8" maxlength="16">
    <input type="password" name="re-password" id="re-password" placeholder="Confirm Password" required title="Re-enter password">
    <button class="input" type="submit" id="button">Sign Up</button>
  </form>
</main> 