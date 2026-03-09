<?php
session_start();
include("include/connect.php");
include('include/header.php');

// Redirect to login if user is not logged in
if (!isset($_SESSION['aid']) || $_SESSION['aid'] <= 0) {
    header("Location: login.php");
    exit();
}

$aid = $_SESSION['aid'];

// Handle profile update submission
if (isset($_POST['submit'])) {
    $firstname = $_POST['a1'];
    $lastname = $_POST['a2'];
    $email = $_POST['a3'];
    $phone = $_POST['a5'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $account_number = $_POST['account_number'];

    // Validate inputs
    if (empty($firstname) || empty($lastname) || empty($email) || empty($phone)) {
        echo "<script>alert('All fields are required!'); window.location.href='profile.php?upd=1';</script>";
        exit();
    }

    // Ensure unique email and phone
    $query = "SELECT * FROM accounts WHERE (phone='$phone' OR email='$email') AND aid != $aid";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email or phone already exists!'); window.location.href='profile.php?upd=1';</script>";
        exit();
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES['profile_picture']['name']);
        $targetFilePath = $targetDir . time() . "_" . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        // Allow only certain file formats
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileType, $allowedTypes)) {
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
                $updatePictureQuery = "UPDATE accounts SET profile_picture = '$targetFilePath' WHERE aid = $aid";
                if (!mysqli_query($con, $updatePictureQuery)) {
                    echo "<script>alert('Error updating profile picture in the database.'); window.location.href='profile.php?upd=1';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Error uploading profile picture.'); window.location.href='profile.php?upd=1';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid file format. Only JPG, PNG, and GIF are allowed.'); window.location.href='profile.php?upd=1';</script>";
            exit();
        }
    }

    // Handle background image upload
    if (isset($_FILES['bg_image']) && $_FILES['bg_image']['error'] == 0) {
        $targetDir = "uploads/";
        $bgFileName = basename($_FILES['bg_image']['name']);
        $bgFilePath = $targetDir . time() . "_bg_" . $bgFileName;
        $bgFileType = pathinfo($bgFilePath, PATHINFO_EXTENSION);

        // Allow only certain file formats
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($bgFileType, $allowedTypes)) {
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (move_uploaded_file($_FILES['bg_image']['tmp_name'], $bgFilePath)) {
                $updateBgQuery = "UPDATE accounts SET bg_image = '$bgFilePath' WHERE aid = $aid";
                if (!mysqli_query($con, $updateBgQuery)) {
                    echo "<script>alert('Error updating background image in the database.'); window.location.href='profile.php?upd=1';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Error uploading background image.'); window.location.href='profile.php?upd=1';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid file format for background image. Only JPG, PNG, and GIF are allowed.'); window.location.href='profile.php?upd=1';</script>";
            exit();
        }
    }

    $query = "UPDATE accounts SET 
                afname = '$firstname', 
                alname = '$lastname', 
                email = '$email', 
                phone = '$phone', 
                address = '$address', 
                city = '$city', 
                state = '$state', 
                account_number = '$account_number' 
              WHERE aid = $aid";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Failed to update profile. Please try again.'); window.location.href='profile.php?upd=1';</script>";
    }
    exit();
}

$query = "SELECT * FROM accounts WHERE aid = $aid";
$result = mysqli_query($con, $query);
$user = mysqli_fetch_assoc($result);

$orderQuery = "
    SELECT 
        orders.oid, 
        orders.dateod, 
        orders.total, 
        orders.status, 
        GROUP_CONCAT(CONCAT(products.pname, ' (x', `order-details`.qty, ')')) AS items
    FROM 
        orders
    JOIN 
        `order-details` ON orders.oid = `order-details`.oid
    JOIN 
        products ON `order-details`.pid = products.pid
    WHERE 
        orders.aid = $aid
    GROUP BY 
        orders.oid";
$orderResult = mysqli_query($con, $orderQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="include/css/style.css" />
    <title>User Profile</title>
    <style>
        body {
            background: url('<?php echo $user['bg_image'] ?? "default-bg.jpg"; ?>') no-repeat center center fixed;
            background-size: cover;
        }

        @media (max-width: 768px) {
            body {
                background-size: contain;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- Sidebar -->
        <aside class="sidenav">
            <div class="profile">
                <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'default-profile.png'); ?>" alt="Profile Picture">
                <div class="name"><?php echo htmlspecialchars($user['afname'] . ' ' . $user['alname']); ?></div>
                <div class="points">Reward Points: <?php echo htmlspecialchars($user['membership_points']); ?></div>
                <div class="membership">Membership: <?php echo htmlspecialchars(ucfirst($user['membership_tier'])); ?></div>
                <div class="membership-date">Member Since: <?php echo htmlspecialchars($user['membership_start_date']); ?></div>
            </div>
            <div class="sidenav-url">
                <a href="profile.php?upd=1" class="sidebtn">Update Profile</a>
                <img src="img/membership.gif" class="logo1" alt="Logo" />
                <a href="membership.php" class="sidebtn">Apply for Membership</a>

                <img src="img/mario.gif" class="logo1" alt="Logo" />
                <a href="reward.php" class="sidebtn">Claim Reward</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <h2>USER INFORMATION</h2>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_GET['upd']) && $_GET['upd'] == 1): ?>
                        <form method="post" enctype="multipart/form-data">
                            <table>
                                <tr><td>Profile Picture</td><td><input type="file" name="profile_picture"></td></tr>
                                <tr><td>Background Image</td><td><input type="file" name="bg_image"></td></tr>
                                <tr><td>First Name</td><td><input name="a1" type="text" value="<?php echo htmlspecialchars($user['afname']); ?>"></td></tr>
                                <tr><td>Last Name</td><td><input name="a2" type="text" value="<?php echo htmlspecialchars($user['alname']); ?>"></td></tr>
                                <tr><td>Email</td><td><input name="a3" type="email" value="<?php echo htmlspecialchars($user['email']); ?>"></td></tr>
                                <tr><td>Phone</td><td><input name="a5" type="text" value="<?php echo htmlspecialchars($user['phone']); ?>"></td></tr>
                                <tr><td>Address</td><td><input name="address" type="text" value="<?php echo htmlspecialchars($user['address']); ?>"></td></tr>
                                <tr><td>City</td><td><input name="city" type="text" value="<?php echo htmlspecialchars($user['city']); ?>"></td></tr>
                                <tr><td>State</td><td><input name="state" type="text" value="<?php echo htmlspecialchars($user['state']); ?>"></td></tr>
                                <tr><td>Account Number</td><td><input name="account_number" type="text" value="<?php echo htmlspecialchars($user['account_number']); ?>"></td></tr>
                                <tr><td colspan="2"><button name="submit" type="submit">Update Profile</button></td></tr>
                            </table>
                        </form>
                    <?php else: ?>
                        <table>
                            <tr><td>First Name</td><td><?php echo htmlspecialchars($user['afname']); ?></td></tr>
                            <tr><td>Last Name</td><td><?php echo htmlspecialchars($user['alname']); ?></td></tr>
                            <tr><td>Email</td><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
                            <tr><td>Phone</td><td><?php echo htmlspecialchars($user['phone']); ?></td></tr>
                            <tr><td>Address</td><td><?php echo htmlspecialchars($user['address']); ?></td></tr>
                            <tr><td>City</td><td><?php echo htmlspecialchars($user['city']); ?></td></tr>
                            <tr><td>State</td><td><?php echo htmlspecialchars($user['state']); ?></td></tr>
                            <tr><td>Visa/ Paypall account number</td><td><?php echo htmlspecialchars($user['account_number']); ?></td></tr>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <h2>Delivery Tracking</h2>
            <div class="order-history">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Order Date</th>
                            <th>Items</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orderResult && mysqli_num_rows($orderResult) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($orderResult)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['oid']); ?></td>
                                    <td><?php echo htmlspecialchars($order['dateod']); ?></td>
                                    <td><?php echo htmlspecialchars($order['items']); ?></td>
                                    <td>RM<?php echo number_format($order['total'], 2); ?></td>
                                    <td><?php echo ucfirst($order['status']); ?></td>
                                    <td><a href="receipt.php?oid=<?php echo urlencode($order['oid']); ?>" class="btn-receipt">View Receipt</a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <?php include('include/footer.php'); ?>
</body>
</html>
