<?php

$page = 'login';
require_once './assets/components/head.php';

// Database connection (use the same as signup.php)
include 'database.php';

// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve user from the database
    $query = "SELECT user_id, username, email, password FROM users WHERE email = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // User found, verify password
        $user = $result->fetch_assoc();
        $hashedPassword = $user['password'];

        if (password_verify($password, $hashedPassword)) {
            // Passwords match, login successful

            // Store user info in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username']; // Optionally store username
            $_SESSION['email'] = $user['email']; // Optionally store email
            $stmt->close(); // Close statement
            $conn->close(); // Close connection
            header('Location: index.php'); // Redirect to index.php
            exit();
        } else {
            // Passwords don't match
            echo "<script>alert('Invalid email or password');</script>";
        }
    } else {
        // User not found
        echo "<script>alert('Invalid email or password');</script>";
    }

    $stmt->close(); // Close statement
    $conn->close(); // Close connection
}
?>

<main>
  <form class="form" method="post" action="/login.php">
    <h1 id="title">Log In</h1>
    <input type="text" name="email" id="email" placeholder="E-mail" required autocomplete="off">
    <input type="password" name="password" id="password" placeholder="Password" required minlength="8" maxlength="16">
    <button class="input" type="submit" id="button">Log In</button>
  </form>
</main>
