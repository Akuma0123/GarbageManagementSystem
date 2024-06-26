<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<style>
    body {
        font-family: Arial, sans-serif;
    }
    .container {
        max-width: 400px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    input[type="text"],
    input[type="password"],
    input[type="submit"] {
        width: 100%;
        padding: 10px;
        margin: 5px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }
    input[type="submit"]:hover {
        background-color: #45a049;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Admin Login</h2>
    <form id="adminLoginForm" method="post" action="/AdminDas.php" onsubmit="return validateForm()">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <span id="passwordError" style="color: red; display: none;">Password is incorrect.</span> <!-- Hidden by default -->
        <input type="submit" name="login" value="Login">
    </form>
</div>

<script>
    function validateForm() {
        var password = document.getElementById("password").value;

        // Perform your validation here
        if (password !== "admin123") { 
            document.getElementById("passwordError").style.display = "block"; // Display error message
            return false; // Prevent form submission
        } else {
            
            return true;
        }
    }
</script>

</body>
</html>
