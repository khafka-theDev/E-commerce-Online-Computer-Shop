<?php
session_start();
include('include/header.php');
include("include/connect.php");

$errors = []; // Array to store validation errors

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($username)) {
        $errors['username'] = 'Please enter username first.';
    }
    if (empty($password)) {
        $errors['password'] = 'Please enter your password bruh.';
    }

    // If no validation errors, check credentials
    if (empty($errors)) {
        $query = "SELECT * FROM accounts WHERE username='$username' AND password='$password'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            // Set session variables
            $_SESSION['aid'] = $row['aid'];
            $_SESSION['username'] = $username;

            // Optionally, set a session cookie
            setcookie('user_session', session_id(), 0, "/", "", true, true); // Cookie expires when browser is closed

            // Redirect to profile page
            header("Location: index.php");
            exit();
        } else {
            $errors['credentials'] = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ah Chong IT Shop</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="include/css/style.css"/>
</head>
<body>
    <!-- Display timeout message if redirected -->
    <?php if (isset($_GET['timeout']) && $_GET['timeout'] == 1): ?>
        <p style="color: red; text-align: center;">Your session has expired due to inactivity. Please log in again.</p>
    <?php endif; ?>

    <form method="post" id="form">
        <h3 style="color: darkred; margin: auto"></h3>

        <input class="input1" id="user" name="username" type="text" placeholder="Username *" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        <?php if (!empty($errors['username'])): ?>
            <span class="error"><?php echo $errors['username']; ?></span>
        <?php endif; ?>

        <input class="input1" id="pass" name="password" type="password" placeholder="Password *">
        <?php if (!empty($errors['password'])): ?>
            <span class="error"><?php echo $errors['password']; ?></span>
        <?php endif; ?>

        <?php if (!empty($errors['credentials'])): ?>
            <span class="error"><?php echo $errors['credentials']; ?></span>
        <?php endif; ?>

        <button type="submit" class="btn" name="submit">Login</button>
    </form>

    <div class="sign">
        <a href="signup.php" class="signn">Do not have an account?</a>
    </div>

    <div class="sign">
        <a href="admin.php" class="signn">Admin</a>
    </div>

<?php include('include/footer.php'); ?>

    <script src="js/script.js"></script>
</body>
</html>
