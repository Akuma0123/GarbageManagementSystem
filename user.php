<?php
$page = 'users';

?>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }
    th {
        background-color: #f2f2f2;
    }
</style>

<body>

<h2>Users</h2>

<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Sample user data
        $users = [
            ['username' => 'user1', 'email' => 'user1@example.com', 'role' => 'Admin'],
            ['username' => 'user2', 'email' => 'user2@example.com', 'role' => 'Member']
        ];

        // Loop through users and display in table
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td><form method='post'><input type='hidden' name='username' value='{$user['username']}'><button type='submit' name='delete'>Delete</button></form></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>


<?php
// Handling user deletion
if (isset($_POST['delete'])) {
    // Retrieve username of user to delete
    $usernameToDelete = $_POST['username'];

    // Remove the user from the array
    foreach ($users as $key => $user) {
        if ($user['username'] === $usernameToDelete) {
            unset($users[$key]);
            break; // Exit loop once user is found and deleted
        }
    }

    // Display success message
    echo "<p>User deleted successfully.</p>";
}
?> 

</body>
</html>
