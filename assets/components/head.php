<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Garbage Management System</title>
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <?php
  $uri =  parse_url($_SERVER["REQUEST_URI"])['path'];
  $styles = [
    '/login.php' => ['loginAndSignup.css'],
    '/signup.php' => ['loginAndSignup.css'],
    '/index.php' => ['style.css'],
    '/requestForm.php'=> ['requestForm.css'],
    '/aboutUs.php'=> ['abt.css'],
    '/myRequest.php'=> ['myRequest.css'],
    '/notification.php'=> ['notification.css'],
    '/user.php'=> ['user.css']




  ];
  $scripts = [
    '/login.php' => ['login.js'],
    '/signup.php' => ['signup.js'],
    /*'/requestForm.php' => ['dateAndTime.js']  */
  ];

  // Respective styles
  if (array_key_exists($uri, $styles)) {
    foreach ($styles[$uri] as $style) {
      echo '<link rel="stylesheet" href="/assets/css/' . $style . '">';
    }
  }

  // Respective scripts
  if (array_key_exists($uri, $scripts)) {
    foreach ($scripts[$uri] as $script) {
      echo '<script src="/assets/js/' . $script . '" defer ></script>';
    }
  }
  ?>
</head>

<body>
  <?php session_start(); ?>
