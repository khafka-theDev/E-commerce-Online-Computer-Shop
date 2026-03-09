<?php
session_start(); // Start session at the beginning

include('include/connect.php'); // Include the database connection file
include("include/header.php");

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Check credentials in the database
    $query = "SELECT * FROM accounts WHERE username='$username' AND password='$password'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify the user has an admin role
        if (isset($user['role']) && $user['role'] === 'admin') { // Check 'role' column in the accounts table
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'admin'; // Set role to 'admin' in the session
            $_SESSION['last_activity'] = time(); // Set last activity time for inactivity timeout
            header("Location: admin/adminpanel.php"); // Redirect to the admin panel
            exit();
        } else {
            // If user is not an admin
            echo "<script>
                alert('Access denied. Admins only.');
                window.location.href = 'admin.php'; // Stay on the same page
            </script>";
        }
    } else {
        // If login fails
        echo "<script>
            alert('Wrong credentials. Please try again.');
            window.location.href = 'admin.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login</title>
    <link rel="stylesheet" href="include/css/style.css" />
</head>
<body>
    <form method="post" id="form">
        <h2>Admin Login</h2>
        <input class="input1" name="username" type="text" placeholder="Username *" required>
        <input class="input1" name="password" type="password" placeholder="Password *" required>
        <button type="submit" class="btn" name="submit">Login</button>
    </form>

    <div class="sign">
        <a href="login.php" class="signn">User Login</a>
    </div>
</body>
<?php include('include/footer.php'); ?>
</html>
