<?php
include("include/connect.php");
include("include/header.php"); // Include the header file

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstName'] ?? '';
    $lastname = $_POST['lastName'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmpassword = $_POST['confirmPassword'] ?? '';
    $contact = $_POST['phone'] ?? '';
    $gen = $_POST['gender'] ?? '';
    $email = $_POST['email'] ?? '';

    // Validate fields
    if (empty($firstname)) {
        $errors['firstName'] = 'First Name is required.';
    }

    if (empty($lastname)) {
        $errors['lastName'] = 'Last Name is required.';
    }

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }

    if (empty($confirmpassword)) {
        $errors['confirmPassword'] = 'Confirm Password is required.';
    } elseif ($password !== $confirmpassword) {
        $errors['confirmPassword'] = 'Passwords do not match.';
    }

    if (empty($contact)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^\d{11}$/', $contact)) {
        $errors['phone'] = 'Phone number must be exactly 11 digits.';
    }

    if ($gen === 'S') {
        $errors['gender'] = 'Please select a gender.';
    }

    // If no errors, proceed to check existing credentials and insert data
    if (empty($errors)) {
        $query = "SELECT * FROM accounts WHERE username = '$username' OR phone = '$contact' OR email = '$email'";
        $result = mysqli_query($con, $query);
        $row = mysqli_fetch_assoc($result);

        if (!empty($row['aid'])) {
            echo "<script>alert('Credentials already exist'); window.location.href = 'signup.php';</script>";
            exit();
        }

        $query = "INSERT INTO `accounts` (afname, alname, phone, email, username, gender, password) 
                  VALUES ('$firstname', '$lastname', '$contact', '$email', '$username', '$gen', '$password')";
        $result = mysqli_query($con, $query);

        if ($result) {
            echo "<script>alert('Successfully registered'); window.location.href = 'login.php';</script>";
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
    <title>Sign up</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="include/css/style.css" />
</head>

<body>
    <form method="post" id="form">
        <h3 style="color: darkred; margin: auto"></h3>

        <input class="input1" id="fn" name="firstName" type="text" placeholder="First Name *" value="<?php echo htmlspecialchars($_POST['firstName'] ?? ''); ?>">
        <?php if (!empty($errors['firstName'])) : ?><span class="error"><?php echo $errors['firstName']; ?></span><?php endif; ?>

        <input class="input1" id="ln" name="lastName" type="text" placeholder="Last Name *" value="<?php echo htmlspecialchars($_POST['lastName'] ?? ''); ?>">
        <?php if (!empty($errors['lastName'])) : ?><span class="error"><?php echo $errors['lastName']; ?></span><?php endif; ?>

        <input class="input1" id="user" name="username" type="text" placeholder="Username *" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        <?php if (!empty($errors['username'])) : ?><span class="error"><?php echo $errors['username']; ?></span><?php endif; ?>

        <input class="input1" id="email" name="email" type="email" placeholder="Email *" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        <?php if (!empty($errors['email'])) : ?><span class="error"><?php echo $errors['email']; ?></span><?php endif; ?>

        <input class="input1" id="pass" name="password" type="password" placeholder="Password *">
        <?php if (!empty($errors['password'])) : ?><span class="error"><?php echo $errors['password']; ?></span><?php endif; ?>

        <input class="input1" id="cpass" name="confirmPassword" type="password" placeholder="Confirm Password *">
        <?php if (!empty($errors['confirmPassword'])) : ?><span class="error"><?php echo $errors['confirmPassword']; ?></span><?php endif; ?>

        <input class="input1" id="contact" name="phone" type="number" placeholder="601********" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
        <?php if (!empty($errors['phone'])) : ?><span class="error"><?php echo $errors['phone']; ?></span><?php endif; ?>

        <select class="select1" id="gen" name="gender">
            <option value="S">Select Gender</option>
            <option value="M" <?php echo ($_POST['gender'] ?? '') === 'M' ? 'selected' : ''; ?>>Male</option>
            <option value="F" <?php echo ($_POST['gender'] ?? '') === 'F' ? 'selected' : ''; ?>>Female</option>
        </select>
        <?php if (!empty($errors['gender'])) : ?><span class="error"><?php echo $errors['gender']; ?></span><?php endif; ?>

        <button name="submit" type="submit" class="btn">Submit</button>
    </form>

    <div class="sign">
        <a href="login.php" class="signn">Already have an account?</a>
    </div>

    <?php include('include/footer.php'); ?>
</body>

</html>
