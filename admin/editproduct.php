<?php
session_start();
include("../include/connect.php");

// Check if admin is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin.php?error=unauthorized_access"); // Redirect to login if not logged in or unauthorized
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: adminpanel.php?error=invalid_product_id");
    exit();
}

$pid = intval($_GET['id']); // Get product ID from URL

// Fetch product details
$query = "SELECT * FROM products WHERE pid = $pid";
$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: adminpanel.php?error=product_not_found");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Handle form submission
if (isset($_POST['update'])) {
    $pname = mysqli_real_escape_string($con, $_POST['pname']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $price = floatval($_POST['price']);
    $qtyavail = intval($_POST['qtyavail']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $points = intval($_POST['points']);
    
    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "product_images/" . basename($image);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = $target;
        } else {
            echo "<script>alert('Failed to upload image.');</script>";
        }
    } else {
        $imagePath = $product['img']; // Use existing image if no new image uploaded
    }

    // Update product in database
    $updateQuery = "UPDATE products SET 
                        pname = '$pname', 
                        category = '$category', 
                        price = $price, 
                        qtyavail = $qtyavail, 
                        description = '$description', 
                        points = $points, 
                        img = '$imagePath'
                    WHERE pid = $pid";

    if (mysqli_query($con, $updateQuery)) {
        echo "<script>
            alert('Product updated successfully.');
            window.location.href = 'adminpanel.php';
        </script>";
    } else {
        echo "<script>alert('Failed to update product. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="css/invent.css">
</head>
<body>
    <?php include('adminheader.php'); ?>

    <div class="container">
        <h1>Edit Product</h1>
        <form method="post" enctype="multipart/form-data">
            <label for="pname">Product Name:</label>
            <input type="text" id="pname" name="pname" value="<?php echo htmlspecialchars($product['pname']); ?>" required>

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" required>

            <label for="qtyavail">Stock Quantity:</label>
            <input type="number" id="qtyavail" name="qtyavail" value="<?php echo htmlspecialchars($product['qtyavail']); ?>" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>

            <label for="points">Reward Points:</label>
            <input type="number" id="points" name="points" value="<?php echo htmlspecialchars($product['points']); ?>" required>

            <label for="image">Product Image:</label>
            <input type="file" id="image" name="image">
            <p>Current Image:</p>
            <img src="<?php echo htmlspecialchars($product['img']); ?>" alt="Product Image" style="max-width: 150px;">

            <button type="submit" name="update">Update Product</button>
        </form>
    </div>

    <?php include('adminfooter.php'); ?>
</body>
</html>
