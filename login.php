<?php
$page = 'login';
require_once './assets/components/head.php';
?>
<?php
// login.php

// Database connection (use the same as signup.php)
include 'database.php';

// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script> alert('Invalid email format'); </script>";
        return;
    }

    // Retrieve user from the database
    $query = "SELECT * FROM users WHERE email = ?";
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

            $_SESSION['user'] = $user; // Store user info in session
            header('location: index.php');
            exit();
        } else {
            // Passwords don't match
            echo "<script> alert('Incorrect email or password'); </script>";
        }
    } else {
        // User not found
        echo "<script> alert('User not found'); </script>";
    }

    $stmt->close(); // Close statement
    $conn->close(); // Close connection
}
?>

<main>
  <form class="form" method="post" action="/login.php">
  <h1 id="title">Log In</h1>
    <input type="text" name="email" id="email" placeholder="E-mail" required title="Enter e-mail address">
    <input type="password" name="password" id="password" placeholder="Password" required title="Enter password" minlength="8" maxlength="16">
    <button class="input" type="submit" id="button">Log In</button>
  </form>
</main>

