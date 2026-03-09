<?php
session_start();
include('adminheader.php');
include("../include/connect.php");

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin.php");
    exit();
}

// Fetch all orders with item details, including the receipt blob
$query = "
    SELECT 
        orders.oid, 
        orders.dateod, 
        orders.receipt_blob, -- Fetching the receipt blob
        accounts.afname, 
        accounts.alname, 
        orders.total, 
        orders.status,
        GROUP_CONCAT(CONCAT(products.pname, ' (x', `order-details`.qty, ')')) AS items
    FROM 
        orders 
    JOIN 
        accounts ON orders.aid = accounts.aid
    JOIN 
        `order-details` ON orders.oid = `order-details`.oid
    JOIN 
        products ON `order-details`.pid = products.pid
    GROUP BY 
        orders.oid";
$result = mysqli_query($con, $query);

if (!$result) {
    die("Error fetching orders: " . mysqli_error($con));
}

// Handle order update
if (isset($_POST['update'])) {
    $oid = isset($_POST['oid']) ? intval($_POST['oid']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($oid > 0 && in_array($status, ['Pending', 'Delivered'])) {
        $stmt = $con->prepare("UPDATE orders SET status = ? WHERE oid = ?");
        $stmt->bind_param('si', $status, $oid);

        if ($stmt->execute()) {
            echo "<script>
                alert('Order status updated successfully!');
                window.location.href = 'manageorder.php';
            </script>";
        } else {
            echo "<script>
                alert('Failed to update order status. Please try again.');
                window.location.href = 'manageorder.php';
            </script>";
        }
        $stmt->close();
    } else {
        echo "<script>
            alert('Invalid order ID or status.');
            window.location.href = 'manageorder.php';
        </script>";
    }
    exit();
}

// Handle order deletion
if (isset($_GET['delete'])) {
    $oid = isset($_GET['delete']) ? intval($_GET['delete']) : 0;

    if ($oid > 0) {
        $deleteStmt = $con->prepare("DELETE FROM orders WHERE oid = ?");
        $deleteStmt->bind_param('i', $oid);

        if ($deleteStmt->execute()) {
            echo "<script>
                alert('Order deleted successfully!');
                window.location.href = 'manageorder.php';
            </script>";
        } else {
            echo "<script>
                alert('Failed to delete order. Please try again.');
                window.location.href = 'manageorder.php';
            </script>";
        }
        $deleteStmt->close();
    } else {
        echo "<script>
            alert('Invalid order ID.');
            window.location.href = 'manageorder.php';
        </script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Orders</title>
    <link rel="stylesheet" href="css/invent.css">
    <style>
        .btn-view {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            text-decoration: none;
        }
        .btn-view:hover {
            background-color: #0056b3;
        }
        .btn-disabled {
            background-color: grey;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <h1>Manage Orders</h1>
    <table border="1">
        <tr>
            <th>Order ID</th>
            <th>Order Date</th>
            <th>User</th>
            <th>Total Amount</th>
            <th>Items</th>
            <th>Status</th>
            <th>Actions</th>
            <th>Receipt</th>
        </tr>
        <?php while ($order = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($order['oid']); ?></td>
            <td><?php echo htmlspecialchars($order['dateod']); ?></td>
            <td><?php echo htmlspecialchars($order['afname'] . ' ' . $order['alname']); ?></td>
            <td>RM<?php echo number_format($order['total'], 2); ?></td>
            <td><?php echo htmlspecialchars($order['items']); ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="oid" value="<?php echo htmlspecialchars($order['oid']); ?>">
                    <select name="status">
                        <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                    </select>
                    <button type="submit" name="update">Update</button>
                </form>
            </td>
            <td>
                <a class="delete" href="manageorder.php?delete=<?php echo htmlspecialchars($order['oid']); ?>" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
            </td>
            <td>
                <?php if (!empty($order['receipt_blob'])): ?>
                    <form method="post" action="viewpdf.php" target="_blank">
                        <input type="hidden" name="receipt_blob" value="<?php echo base64_encode($order['receipt_blob']); ?>">
                        <button type="submit" class="btn-view">View Receipt</button>
                    </form>
                <?php else: ?>
                    <button class="btn-disabled" disabled>No Receipt Available</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
<?php include('adminfooter.php'); ?>
</html>
