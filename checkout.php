<?php
session_start();
include('include/header.php');
include("include/connect.php");

// Enable error reporting and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'checkout_error_log.log');

// Ensure the user is logged in
if (!isset($_SESSION['aid']) || $_SESSION['aid'] <= 0) {
    echo "<script>
        alert('Please log in to proceed to checkout.');
        window.location.href = 'login.php';
    </script>";
    exit();
}

$aid = $_SESSION['aid']; // Retrieve user ID from session

// Fetch user details from the database
$query = "SELECT address, city, state, account_number FROM accounts WHERE aid = $aid";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $userDetails = mysqli_fetch_assoc($result);
} else {
    // Log error if user details are missing
    error_log("User details not found for aid: $aid");
    echo "<script>
        alert('Your profile information is incomplete. Please update your profile to proceed.');
        window.location.href = 'profile.php';
    </script>";
    exit();
}

// Check if user details are missing
$missingDetails = empty($userDetails['address']) || empty($userDetails['city']) || empty($userDetails['state']) || empty($userDetails['account_number']);

if ($missingDetails) {
    error_log("Incomplete user details for aid: $aid. Address, city, state, or account number missing.");
    echo "<script>
        alert('Please update your profile with your address, city, state, and account number before proceeding to checkout.');
        window.location.href = 'profile.php';
    </script>";
    exit();
}

// Handle order submission
if (isset($_POST['sub'])) {
    $paymentMethod = $_POST['dbt']; // Payment method
    $receiptContent = null;

    // Handle receipt upload if Pay with QR or PayPal/Visa is selected
    if (in_array($paymentMethod, ['qr', 'bank'])) {
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
            $fileTmpPath = $_FILES['receipt']['tmp_name'];
            $fileName = $_FILES['receipt']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension !== 'pdf') {
                error_log("Invalid receipt format uploaded by aid: $aid. Only PDF is allowed.");
                echo "<script>alert('Please upload a valid PDF receipt.');</script>";
                exit();
            }

            // Read file content to store in the database
            $receiptContent = file_get_contents($fileTmpPath);

            if ($receiptContent === false) {
                error_log("Failed to read receipt file for aid: $aid.");
                echo "<script>alert('Error reading receipt file. Please try again.');</script>";
                exit();
            }
        } else {
            error_log("Receipt not uploaded for aid: $aid using payment method: $paymentMethod.");
            echo "<script>alert('Please upload a receipt for this payment method.');</script>";
            exit();
        }
    }

    // Insert the order into the database
    $query = "INSERT INTO orders (dateod, datedel, aid, address, city, state, account, total, receipt_blob) 
              VALUES (CURDATE(), NULL, ?, ?, ?, ?, ?, 0, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param(
        'isssss',
        $aid,
        $userDetails['address'],
        $userDetails['city'],
        $userDetails['state'],
        $userDetails['account_number'],
        $receiptContent
    );

    if (!$stmt->execute()) {
        error_log("Order insertion failed for aid: $aid. Error: " . $stmt->error);
        echo "<script>alert('Error placing your order. Please try again.');</script>";
        exit();
    }

    $oid = $stmt->insert_id; // Get the order ID
    error_log("Order placed successfully. OID: $oid, aid: $aid.");

    // Fetch cart items and calculate totals
    $query = "SELECT cart.*, products.pname, products.price, products.points 
              FROM cart 
              JOIN products ON cart.pid = products.pid 
              WHERE cart.aid = $aid";
    $result = mysqli_query($con, $query);

    $totalAmount = 0;
    $totalPoints = 0;

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pid = $row['pid'];
            $cqty = $row['cqty'];
            $price = $row['price'];
            $points = $row['points'];
            $subtotal = $price * $cqty;

            $totalAmount += $subtotal;
            $totalPoints += $points * $cqty;

            // Insert into `order-details` table
            $query = "INSERT INTO `order-details` (oid, pid, qty) VALUES ($oid, $pid, $cqty)";
            if (!mysqli_query($con, $query)) {
                error_log("Failed to insert order details for OID: $oid, PID: $pid. Error: " . mysqli_error($con));
                echo "<script>alert('Error processing your order details. Please try again.');</script>";
                exit();
            }

            // Update product quantity in the `products` table
            $query = "UPDATE products SET qtyavail = qtyavail - $cqty WHERE pid = $pid";
            if (!mysqli_query($con, $query)) {
                error_log("Failed to update product quantity for PID: $pid. Error: " . mysqli_error($con));
                echo "<script>alert('Error updating product stock. Please try again.');</script>";
                exit();
            }
        }
    }

    // Update the total amount for the order
    $query = "UPDATE orders SET total = $totalAmount WHERE oid = $oid";
    if (!mysqli_query($con, $query)) {
        error_log("Failed to update order total for OID: $oid. Error: " . mysqli_error($con));
        echo "<script>alert('Error finalizing your order. Please try again.');</script>";
        exit();
    }

    // Update user reward points
    $query = "UPDATE accounts SET points = points + $totalPoints WHERE aid = $aid";
    if (!mysqli_query($con, $query)) {
        error_log("Failed to update reward points for aid: $aid. Error: " . mysqli_error($con));
        echo "<script>alert('Error updating reward points. Please try again.');</script>";
        exit();
    }

    // Clear the cart
    $query = "DELETE FROM cart WHERE aid = $aid";
    if (!mysqli_query($con, $query)) {
        error_log("Failed to clear cart for aid: $aid. Error: " . mysqli_error($con));
        echo "<script>alert('Error clearing your cart. Please try again.');</script>";
        exit();
    }
    error_log("Cart cleared successfully for aid: $aid.");

    // Redirect to receipt page with the order ID
    echo "<script>
        alert('Order placed successfully! Your receipt is ready.');
        window.location.href = 'receipt.php?oid=$oid';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout</title>
    <link rel="stylesheet" href="include/css/style.css"/>
    <script>
        function toggleReceiptUpload() {
            const qrOption = document.getElementById('qr');
            const bankOption = document.getElementById('bank');
            const receiptUpload = document.getElementById('receiptUpload');
            const qrImage = document.getElementById('qrImage');
            const submitButton = document.getElementById('submitButton');

            if (qrOption.checked || bankOption.checked) {
                receiptUpload.style.display = 'block';
                qrImage.style.display = qrOption.checked ? 'block' : 'none';
                submitButton.disabled = true;
            } else {
                receiptUpload.style.display = 'none';
                qrImage.style.display = 'none';
                submitButton.disabled = false;
            }
        }

        function validateReceiptUpload() {
            const receiptInput = document.querySelector("input[name='receipt']");
            const submitButton = document.getElementById('submitButton');

            if (receiptInput.files.length > 0) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="titlecheck">
            <h2>Product Order Form</h2>
        </div>
        <div class="d-flex">
            <form method="post" enctype="multipart/form-data">
                <label for="houseadd">Address:</label>
                <input class="input11" type="text" id="houseadd" name="houseadd" value="<?php echo htmlspecialchars($userDetails['address']); ?>" readonly>

                <label for="city">City:</label>
                <input class="input11" type="text" id="city" name="city" value="<?php echo htmlspecialchars($userDetails['city']); ?>" readonly>

                <label for="state">State:</label>
                <input class="input11" type="text" id="state" name="state" value="<?php echo htmlspecialchars($userDetails['state']); ?>" readonly>

                <label for="account">Visa/Paypal Payment Account:</label>
                <input class="input11" type="text" id="account" name="acc" value="<?php echo htmlspecialchars($userDetails['account_number']); ?>" readonly>

                <div>
                    <input class="input2" type="radio" id="ac1" name="dbt" value="cod" checked onclick="toggleReceiptUpload()"> Cash on Delivery
                </div>
                <div>
                    <input class="input2" type="radio" id="bank" name="dbt" value="bank" onclick="toggleReceiptUpload()"> Paypal/Visa/MasterCard 
                    <span><img src="img/pay/pay.png" alt=""/></span>
                </div>
                <div>
                    <input class="input2" type="radio" id="qr" name="dbt" value="qr" onclick="toggleReceiptUpload()"> Pay with QR
                </div>
                <div id="receiptUpload" style="display:none;">
                    <label for="receipt">Upload Receipt (PDF only):</label>
                    <input type="file" name="receipt" accept="application/pdf" onchange="validateReceiptUpload()">
                </div>
                <div id="qrImage" style="display:none; margin-top: 15px;">
                    <img src="img/qr.jpeg" alt="QR Code" style="width: 200px; height: 200px;">
                </div>
                <button name="sub" type="submit" id="submitButton" class="btn112">Place Order</button>
            </form>

            <div class="Yorder">
                <table class="table12">
                    <tr class='tr1'>
                        <th class='th1' colspan='2'>Your order</th>
                    </tr>
                    <?php
                    $query = "SELECT cart.*, products.pname, products.price, products.points 
                              FROM cart 
                              JOIN products ON cart.pid = products.pid 
                              WHERE cart.aid = $aid";
                    $result = mysqli_query($con, $query);

                    $totalAmount = 0;
                    $totalPoints = 0;

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $pname = $row['pname'];
                            $cqty = $row['cqty'];
                            $price = $row['price'];
                            $subtotal = $price * $cqty;
                            $points = $row['points'];

                            $totalAmount += $subtotal;
                            $totalPoints += $points * $cqty;

                            echo "
                            <tr class='tr1'>
                                <td class='td1'>$pname x $cqty</td>
                                <td class='td1'>RM$subtotal</td>
                            </tr>";
                        }
                    }

                    echo "
                    <tr class='tr1'>
                        <td class='td1'>Subtotal</td>
                        <td class='td1'>RM$totalAmount</td>
                    </tr>
                    <tr class='tr1'>
                        <td class='td1'>Points to Earn</td>
                        <td class='td1'>$totalPoints</td>
                    </tr>";
                    ?>
                </table>
            </div>
        </div>
    </div>
    <?php include('include/footer.php'); ?>
</body>
</html>
