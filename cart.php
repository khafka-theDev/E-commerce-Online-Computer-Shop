<?php
session_start();
include('include/header.php');
include("include/connect.php");

// Ensure the user is logged in
if (!isset($_SESSION['aid']) || $_SESSION['aid'] < 1) {
    echo "<script>
        alert('Please log in to access your cart.');
        window.location.href = 'login.php';
    </script>";
    exit();
}

$aid = $_SESSION['aid'];

// Remove item from cart
if (isset($_GET['re'])) {
    $pidToRemove = intval($_GET['re']); // Sanitize the product ID
    $query = "DELETE FROM cart WHERE aid = ? AND pid = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('ii', $aid, $pidToRemove);
    if ($stmt->execute()) {
        echo "<script>
            alert('Item removed from the cart.');
            window.location.href = 'cart.php';
        </script>";
    } else {
        echo "<script>
            alert('Failed to remove the item. Please try again.');
            window.location.href = 'cart.php';
        </script>";
    }
    $stmt->close();
}

// Fetch membership tier for the user
$query = "SELECT membership_tier FROM accounts WHERE aid = $aid";
$result = mysqli_query($con, $query);
$user = mysqli_fetch_assoc($result);
$membership_tier = $user['membership_tier'];

// Determine discount based on membership tier
$discount = 0;
if ($membership_tier == 'silver') {
    $discount = 0.03; // 3% discount
} elseif ($membership_tier == 'gold') {
    $discount = 0.05; // 5% discount
} elseif ($membership_tier == 'platinum') {
    $discount = 0.10; // 10% discount
}

// Fetch cart items
$query = "SELECT cart.*, products.pname, products.price, products.points, products.img, products.qtyavail 
          FROM cart JOIN products ON cart.pid = products.pid WHERE cart.aid = $aid";
$result = mysqli_query($con, $query);

$cartTotal = 0;
$totalPoints = 0;
$cartItemCount = mysqli_num_rows($result); // Count the number of items in the cart

while ($row = mysqli_fetch_assoc($result)) {
    $subtotal = $row['price'] * $row['cqty'];
    $cartTotal += $subtotal;
    $totalPoints += $row['points'] * $row['cqty'];
}

// Calculate discount and final total
$discountAmount = $cartTotal * $discount;
$finalTotal = $cartTotal - $discountAmount;

// Store in session variables
$_SESSION['cartSubtotal'] = $cartTotal;
$_SESSION['discountAmount'] = $discountAmount;
$_SESSION['finalTotal'] = $finalTotal;
$_SESSION['totalPoints'] = $totalPoints;
$_SESSION['membershipDiscount'] = $discount * 100;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cart</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="include/css/style.css" />
    <script>
        function updateSubtotal(input, price, subtotalElementId) {
            const quantity = parseInt(input.value);
            const subtotal = quantity * price;
            document.getElementById(subtotalElementId).innerText = RM${subtotal.toFixed(2)};
            calculateTotals();
        }

        function calculateTotals() {
            const subtotals = document.querySelectorAll('.subtotal');
            let total = 0;
            subtotals.forEach((subtotal) => {
                total += parseFloat(subtotal.innerText.replace('RM', ''));
            });

            const discount = parseFloat(document.getElementById('discountValue').innerText.replace('%', '')) / 100;
            const discountAmount = total * discount;
            const finalTotal = total - discountAmount;

            document.getElementById('cartSubtotal').innerText = RM${total.toFixed(2)};
            document.getElementById('discountAmount').innerText = - RM${discountAmount.toFixed(2)};
            document.getElementById('finalTotal').innerText = RM${finalTotal.toFixed(2)};
        }
    </script>
</head>

<body>
    <section id="cart" class="section-p1">
        <table width="100%">
            <thead>
                <tr>
                    <td>Remove</td>
                    <td>Image</td>
                    <td>Product</td>
                    <td>Price</td>
                    <td>Quantity</td>
                    <td>Subtotal</td>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($con, $query);
                if ($cartItemCount > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $pid = $row['pid'];
                        $pname = $row['pname'];
                        $price = $row['price'];
                        $cqty = $row['cqty'];
                        $subtotal = $price * $cqty;

                        echo "
                            <tr>
                                <td>
                                    <a href='cart.php?re=$pid'><i class='far fa-times-circle'></i></a>
                                </td>
                                <td><img src='product_images/{$row['img']}' alt='' /></td>
                                <td>$pname</td>
                                <td class='pr'>RM" . number_format($price, 2) . "</td>
                                <td><input type='number' class='aqt' value='$cqty' min='1' max='{$row['qtyavail']}' 
                                    onchange='updateSubtotal(this, $price, \"subtotal-$pid\")' /></td>
                                <td id='subtotal-$pid' class='subtotal'>RM" . number_format($subtotal, 2) . "</td>
                            </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>Your cart is empty!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <section id="cart-add" class="section-p1">
        <div id="subtotal">
            <h3>Cart Totals</h3>
            <table>
                <tr>
                    <td>Cart Subtotal</td>
                    <td id="cartSubtotal">RM<?php echo number_format($_SESSION['cartSubtotal'], 2); ?></td>
                </tr>
                <tr>
                    <td>Discount (<?php echo ucfirst($membership_tier); ?>)</td>
                    <td id="discountAmount">- RM<?php echo number_format($_SESSION['discountAmount'], 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td id="finalTotal"><strong>RM<?php echo number_format($_SESSION['finalTotal'], 2); ?></strong></td>
                </tr>
                <tr>
                    <td><strong>Total Reward Points</strong></td>
                    <td><strong><?php echo $_SESSION['totalPoints']; ?></strong></td>
                </tr>
            </table>

            <form method="post" action="checkout.php">
                <button class="normal" type="submit" <?php echo $cartItemCount > 0 ? '' : 'disabled'; ?>>Proceed to checkout</button>
            </form>
        </div>
    </section>

    <?php include('include/footer.php'); ?>
</body>

</html>