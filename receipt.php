<?php
session_start();
include("include/connect.php");
include("include/header.php");

// Check if the user is logged in
if (!isset($_SESSION['aid']) || $_SESSION['aid'] <= 0) {
    header("Location: login.php");
    exit();
}

$aid = $_SESSION['aid'];
$oid = $_GET['oid'] ?? null;

if (!$oid) {
    echo "<script>alert('Order ID is missing!'); window.location.href='profile.php';</script>";
    exit();
}

// Fetch order details, including the receipt content
$query = "
    SELECT 
        orders.*, 
        GROUP_CONCAT(CONCAT(products.pname, ' (x', `order-details`.qty, ')')) AS items,
        orders.receipt_blob
    FROM 
        orders
    JOIN 
        `order-details` ON orders.oid = `order-details`.oid
    JOIN 
        products ON `order-details`.pid = products.pid
    WHERE 
        orders.oid = ? AND orders.aid = ?
    GROUP BY 
        orders.oid
";
$stmt = $con->prepare($query);
$stmt->bind_param("ii", $oid, $aid);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "<script>alert('Order not found!'); window.location.href='profile.php';</script>";
    exit();
}

// Check if the receipt exists
$receiptAvailable = !empty($order['receipt_blob']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt</title>
    <link rel="stylesheet" href="include/css/style.css">
    <style>
        .btn-receipt {
            display: block;
            width: fit-content;
            margin: 10px 0;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .btn-receipt:hover {
            background-color: #0056b3;
        }

        .btn-disabled {
            background-color: grey;
            color: white;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="receipt-wrapper">
        <h2>Order Receipt</h2>
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['oid']); ?></p>
        <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['dateod']); ?></p>
        <p><strong>Total Price:</strong> RM<?php echo number_format($order['total'], 2); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
        <p><strong>Items:</strong> <?php echo htmlspecialchars($order['items']); ?></p>
        
        <!-- View Receipt Button -->
        <?php if ($receiptAvailable): ?>
            <form method="post" action="viewpdf.php" target="_blank">
                <input type="hidden" name="receipt_blob" value="<?php echo base64_encode($order['receipt_blob']); ?>">
                <button type="submit" class="btn-receipt">View Payment Receipt</button>
            </form>
        <?php else: ?>
            <button class="btn-receipt btn-disabled" disabled>No Receipt Available</button>
        <?php endif; ?>
        
        <!-- Back to Profile Button -->
        <a href="profile.php" class="btn-receipt">Back to Profile</a>
    </div>
</body>
</html>
<?php include("include/footer.php"); ?>
