<?php
include("../include/connect.php");
include("adminheader.php");

// Handle product insertion
if (isset($_POST['ins'])) {
    $pname = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $brand = $_POST['brand'];
    $points = $_POST['points']; // Reward points field
    $image = $_FILES['photo']['name'];
    $temp_image = $_FILES['photo']['tmp_name'];

    $targetDir = "../img/product_images/"; // Updated directory for image storage

    // Create directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetPath = $targetDir . basename($image);
    move_uploaded_file($temp_image, $targetPath);

    $query = "INSERT INTO `products` (pname, category, description, price, qtyavail, img, brand, points) 
              VALUES ('$pname', '$category', '$description', '$price', '$quantity', '$image', '$brand', '$points')";

    if (mysqli_query($con, $query)) {
        echo "<script> alert('Product added successfully!'); window.location.href = 'inventory.php'; </script>";
    } else {
        echo "<script> alert('Error adding product: " . mysqli_error($con) . "') </script>";
    }
}

// Handle product update
if (isset($_POST['submitt'])) {
    $pid = $_POST['pid'];
    $pname = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $brand = $_POST['brand'];
    $points = $_POST['points']; // Reward points field
    $image = $_FILES['photo']['name'];
    $temp_image = $_FILES['photo']['tmp_name'];
    $targetDir = "../img/product_images/"; // Updated directory for image storage

    // Create directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (!empty($image)) {
        $targetPath = $targetDir . basename($image);
        move_uploaded_file($temp_image, $targetPath);
        $query = "UPDATE `products` SET 
                  pname = '$pname', category = '$category', description = '$description', 
                  qtyavail = $quantity, price = $price, img = '$image', brand = '$brand', points = '$points' 
                  WHERE pid = $pid";
    } else {
        $query = "UPDATE `products` SET 
                  pname = '$pname', category = '$category', description = '$description', 
                  qtyavail = $quantity, price = $price, brand = '$brand', points = '$points' 
                  WHERE pid = $pid";
    }

    if (mysqli_query($con, $query)) {
        echo "<script> alert('Product updated successfully!'); window.location.href = 'inventory.php'; </script>";
    } else {
        echo "<script> alert('Error updating product: " . mysqli_error($con) . "') </script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Product</title>
    <link rel="stylesheet" href="css/invent.css">
</head>

<body>
    <div class="container1">
        <div class="form-container">
            <h2>Add Product</h2>
            <form action="inventory.php" enctype="multipart/form-data" method="post">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="all">Select Category</option>
                    <option value="keyboard">Keyboard</option>
                    <option value="motherboard">Motherboard</option>
                    <option value="mouse">Mouse</option>
                    <option value="cpu">CPU</option>
                    <option value="gpu">GPU</option>
                    <option value="ram">RAM</option>
                </select>
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" required>
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" min="0" required>
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" min="0" required>
                <label for="points">Reward Points:</label>
                <input type="number" id="points" name="points" min="0" required>
                <label for="photo">Image:</label>
                <input type="file" id="photo" name="photo" required>
                <label for="brand">Brand:</label>
                <input type="text" id="brand" name="brand" required>
                <button type="submit" name="ins" class="insert-btn">Add Product</button>
            </form>
        </div>
    </div>
</body>
<?php include("adminfooter.php"); ?>
</html>
