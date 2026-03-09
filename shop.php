<?php
session_start();
include('include/header.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ah Chong IT Shop</title>
    <link rel="stylesheet" href="include/css/style.css" />
</head>

<?php
include("include/connect.php");

function displayProduct($pid, $pname, $price, $brand, $img) {
    // Updated image path to match the new directory structure
    $imagePath = "img/product_images/$img";

    // Check if the image exists, use fallback if not
    if (!file_exists($imagePath) || empty($img)) {
        $imagePath = "img/fallback.jpg"; // Fallback image if the product image doesn't exist
    }

    // Stars and ratings removed as the `reviews` table no longer exists
    $stars = 0;
    $empty = 5;

    echo "
        <div class='pro' onclick='topage($pid)'>
            <img src='$imagePath' height='235px' width='235px' alt='Product Image' />
            <div class='des'>
                <span>$brand</span>
                <h5>$pname</h5>
                <div class='star'>";
    for ($i = 1; $i <= $stars; $i++) {
        echo "<i class='fas fa-star'></i>";
    }
    for ($i = 1; $i <= $empty; $i++) {
        echo "<i class='far fa-star'></i>";
    }
    echo "</div>
                <h4>RM " . number_format($price, 2) . "</h4>
            </div>
            <a onclick='topage($pid)'><i class='fal fa-shopping-cart cart'></i></a>
        </div>";
}

if (isset($_POST['search1'])) {
    $search = $_POST['search'];
    $category = $_POST['cat'];
    $query = "";

    if (!empty($search)) {
        $query = "SELECT * FROM `products` WHERE ((pname LIKE '%$search%') OR (brand LIKE '%$search%') OR (description LIKE '%$search%'))";
    } else {
        $query = "SELECT * FROM `products`";
    }

    if ($category != "all") {
        $query .= (empty($search) ? " WHERE" : " AND") . " category = '$category'";
    }

    $result = mysqli_query($con, $query);

    if ($result) {
        echo "<section id='product1' class='section-p1'>
                <div class='pro-container'>";
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $pid = $row['pid'];
        $pname = strlen($row['pname']) > 35 ? substr($row['pname'], 0, 35) . '...' : $row['pname'];
        $price = $row['price'];
        $brand = $row['brand'];
        $img = $row['img'];
        displayProduct($pid, $pname, $price, $brand, $img);
    }

    if ($result) {
        echo "</div></section>";
    }
} else {
    $select = "SELECT * FROM products WHERE qtyavail > 0 ORDER BY RAND()";
    $result = mysqli_query($con, $select);

    if ($result) {
        echo "<section id='product1' class='section-p1'>
                <div class='pro-container'>";
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $pid = $row['pid'];
        $pname = strlen($row['pname']) > 35 ? substr($row['pname'], 0, 35) . '...' : $row['pname'];
        $price = $row['price'];
        $brand = $row['brand'];
        $img = $row['img'];
        displayProduct($pid, $pname, $price, $brand, $img);
    }

    if ($result) {
        echo "</div></section>";
    }
}
?>

<?php include('include/footer.php'); ?>

<script src="js/script.js"></script>
<script>
    function topage(pid) {
        window.location.href = `sproduct.php?pid=${pid}`;
    }
</script>
</body>

</html>
