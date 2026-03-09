<?php
include("include/connect.php");
include("adminheader.php");

// Handle product insertion with reward points
if (isset($_POST['ins'])) {
    $pname = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $brand = $_POST['brand'];
    $point = $_POST['point']; // New reward points field
    $image = $_FILES['photo']['name'];
    $temp_image = $_FILES['photo']['tmp_name'];

    if ($category == "all") {
        echo "<script> alert('Select a valid category'); setTimeout(function(){ window.location.href = 'inventory.php'; }, 100); </script>";
        exit();
    }

    move_uploaded_file($temp_image, "product_images/$image");

    $query = "INSERT INTO `products` (pname, category, description, price, qtyavail, img, brand, point) 
              VALUES ('$pname', '$category', '$description', '$price', '$quantity', '$image', '$brand', '$point')";

    $result = mysqli_query($con, $query);

    if ($result) {
        echo "<script> alert('Successfully added product with reward points!') </script>";
    } else {
        echo "<script> alert('Error adding product: " . mysqli_error($con) . "') </script>";
    }
}

// Handle product update with reward points
if (isset($_POST['submitt'])) {
    $pname = $_POST['name1'];
    $category = $_POST['category1'];
    $description = $_POST['description1'];
    $quantity = $_POST['quantity1'];
    $price = $_POST['price1'];
    $brand = $_POST['brand1'];
    $point = $_POST['point1']; // Updated reward points
    $image = $_FILES['photo1']['name'];
    $temp_image = $_FILES['photo1']['tmp_name'];
    $pid2 = $_POST['pid1'];
    $image2 = $_POST['prevphoto'];
    $prevcat = $_POST['prev'];

    if ($category == "all") {
        $category = $prevcat;
    }

    if (!empty($image)) {
        move_uploaded_file($temp_image, "product_images/$image");
    }

    $query = !empty($image)
        ? "UPDATE `products` SET pname = '$pname', category = '$category', description = '$description', qtyavail = $quantity, 
           brand = '$brand', price = $price, img = '$image', point = '$point' WHERE pid = $pid2"
        : "UPDATE `products` SET pname = '$pname', category = '$category', description = '$description', qtyavail = $quantity, 
           brand = '$brand', price = $price, img = '$image2', point = '$point' WHERE pid = $pid2";

    $result = mysqli_query($con, $query);

    if ($result) {
        echo "<script> alert('Successfully updated product with reward points!') </script>";
    } else {
        echo "<script> alert('Error updating product: " . mysqli_error($con) . "') </script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Ecommerce Inventory Management</title>
    <link rel="stylesheet" href="css/invent.css">
</head>

<body>
    <div class="container1">
        <div class="form-container">
            <h2>Insert Product</h2>
            <form id="insert-form" action="inventory.php" enctype="multipart/form-data" method="post">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="category">Category:</label>
                <select id="category-filter" name="category" required>
                    <option value="all">All</option>
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
                <input type="number" id="price" name="price" required min='0'>
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" required min='0'>
                <label for="point">Reward Points:</label> <!-- New field -->
                <input type="number" id="point" name="point" required min='0'>
                <label for="image">Image:</label>
                <input type="file" name="photo" id="fileInput" required>
                <label for="brand">Brand:</label>
                <input type="text" id="brand" name="brand" required>
                <button name="ins" type="submit" class="insert-btn">Save</button>
            </form>
        </div>
    </div>
</body>
<?php include("adminfooter.php"); ?>
</html>
