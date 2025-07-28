<?php
$page = 'driver_login';
require_once './assets/components/head.php';
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $query = "SELECT id, name, email, password FROM drivers WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $driver = $result->fetch_assoc();
        $hashedPassword = $driver['password'];
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['driver_id'] = $driver['id'];
            $_SESSION['driver_name'] = $driver['name'];
            $_SESSION['driver_email'] = $driver['email'];
            $stmt->close();
            $conn->close();
            header('Location: driver_dashboard.php');
            exit();
        } else {
            echo "<script>alert('Invalid email or password');</script>";
        }
    } else {
        echo "<script>alert('Invalid email or password');</script>";
    }
    $stmt->close();
    $conn->close();
}
?>
<main>
  <form class="form" method="post" action="/driver_login.php">
    <h1 id="title">Driver Log In</h1>
    <input type="text" name="email" id="email" placeholder="E-mail" required autocomplete="off">
    <input type="password" name="password" id="password" placeholder="Password" required minlength="8" maxlength="16">
    <button class="input" type="submit" id="button">Log In</button>
  </form>
</main> 