<?php
session_start(); // Start the session
include("../include/connect.php");

// Redirect to login if user is not logged in or does not have the admin role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin.php?error=unauthorized_access");
    exit();
}

// Include admin header for session timeout and navigation
include('adminheader.php');

// Handle product deletion
if (isset($_GET['delete'])) {
    $pid = intval($_GET['delete']);
    $deleteQuery = "DELETE FROM products WHERE pid = $pid";

    if (mysqli_query($con, $deleteQuery)) {
        echo "<script>
            alert('Product deleted successfully.');
            window.location.href = 'adminpanel.php';
        </script>";
    } else {
        echo "<script>
            alert('Failed to delete product. Please try again.');
        </script>";
    }
}

// Fetch data from the database
$productsQuery = "SELECT * FROM products";
$productsResult = mysqli_query($con, $productsQuery);

$ordersQuery = "SELECT * FROM orders";
$ordersResult = mysqli_query($con, $ordersQuery);

$accountsQuery = "SELECT * FROM accounts";
$accountsResult = mysqli_query($con, $accountsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/invent.css" />
</head>

<body>
    <!-- Admin Dashboard -->
    <div class="admin-dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="stats">
            <div class="stat-item">
                <h3>Total Products</h3>
                <p><?php echo mysqli_num_rows($productsResult); ?></p>
            </div>
            <div class="stat-item">
                <h3>Total Orders</h3>
                <p><?php echo mysqli_num_rows($ordersResult); ?></p>
            </div>
            <div class="stat-item">
                <h3>Total Users</h3>
                <p><?php echo mysqli_num_rows($accountsResult); ?></p>
            </div>
        </div>

        <!-- Manage Products Section -->
        <h2>Manage Products</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Points</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = mysqli_fetch_assoc($productsResult)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['pname']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td>RM<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($product['qtyavail']); ?></td>
                        <td><?php echo htmlspecialchars($product['points']); ?></td>
                        <td>
                            <a class='edit' href="editproduct.php?id=<?php echo $product['pid']; ?>">Edit</a>
                            <a class='delete' href="adminpanel.php?delete=<?php echo $product['pid']; ?>" 
                               onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php include('adminfooter.php'); ?>
</body>

</html>
