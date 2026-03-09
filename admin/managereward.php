<?php
session_start();
include('adminheader.php');
include("../include/connect.php");

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin.php");
    exit();
}

// Fetch rewards
$query = "SELECT * FROM reward";
$result = mysqli_query($con, $query);

if (!$result) {
    die("Error fetching rewards: " . mysqli_error($con));
}

// Handle reward addition
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $points = intval($_POST['points']);
    $stock = intval($_POST['stock']);

    // Handle reward image upload
    $imagePath = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "../uploads/rewards/";
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $imagePath = $targetDir . $fileName;

        // Ensure the upload directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Move the uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo "<script>alert('Error uploading image.');</script>";
            $imagePath = ""; // Reset path if upload fails
        } else {
            // Store relative path in the database
            $imagePath = "uploads/rewards/" . $fileName;
        }
    }

    // Insert reward into the database
    $query = "INSERT INTO reward (reward_name, description, points, stock, image_path)
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssiis", $name, $description, $points, $stock, $imagePath);

    if ($stmt->execute()) {
        echo "<script>
            alert('Reward added successfully!');
            window.location.href = 'managereward.php';
        </script>";
    } else {
        echo "<script>alert('Failed to add reward.');</script>";
    }
    $stmt->close();
}

// Handle reward deletion
if (isset($_GET['delete'])) {
    $rewardId = intval($_GET['delete']);
    $query = "DELETE FROM reward WHERE reward_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $rewardId);

    if ($stmt->execute()) {
        echo "<script>
            alert('Reward deleted successfully!');
            window.location.href = 'managereward.php';
        </script>";
    } else {
        echo "<script>alert('Failed to delete reward.');</script>";
    }
    $stmt->close();
}

// Handle reward update
if (isset($_POST['update'])) {
    $rewardId = intval($_POST['reward_id']);
    $name = $_POST['name'];
    $description = $_POST['description'];
    $points = intval($_POST['points']);
    $stock = intval($_POST['stock']);

    $query = "UPDATE reward SET reward_name = ?, description = ?, points = ?, stock = ? WHERE reward_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssiii", $name, $description, $points, $stock, $rewardId);

    if ($stmt->execute()) {
        echo "<script>
            alert('Reward updated successfully!');
            window.location.href = 'managereward.php';
        </script>";
    } else {
        echo "<script>alert('Failed to update reward.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Rewards</title>
    <link rel="stylesheet" href="css/invent.css">
</head>
<body>
<div class="container1">
    <div class="form-container">
        <h1>Manage Rewards</h1>
        <h2>Add New Reward</h2>
        <form method="post" enctype="multipart/form-data">
            <label>Reward Name:</label><br>
            <input type="text" name="name" required><br>
            <label>Description:</label><br>
            <textarea name="description" required></textarea><br>
            <label>Points:</label><br>
            <input type="number" name="points" required><br>
            <label>Stock:</label><br>
            <input type="number" name="stock" required><br>
            <label>Image:</label><br>
            <input type="file" name="image" accept="image/*" required><br>
            <button type="submit" name="add">Add Reward</button>
        </form>
    </div>
</div>

<div class="container2">
    <h2>Existing Rewards</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Description</th>
            <th>Points</th>
            <th>Stock</th>
            <th class="special">Actions</th>
        </tr>
        <?php while ($reward = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($reward['reward_id']); ?></td>
            <td>
                <img src="<?php echo htmlspecialchars("../" . $reward['image_path']); ?>" 
                     alt="Reward Image" width="50">
            </td>
            <td><?php echo htmlspecialchars($reward['reward_name']); ?></td>
            <td><?php echo htmlspecialchars($reward['description'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($reward['points']); ?></td>
            <td><?php echo htmlspecialchars($reward['stock']); ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="reward_id" value="<?php echo htmlspecialchars($reward['reward_id']); ?>">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($reward['reward_name']); ?>" required>
                    <textarea name="description"><?php echo htmlspecialchars($reward['description'] ?? ''); ?></textarea>
                    <input type="number" name="points" value="<?php echo htmlspecialchars($reward['points']); ?>" required>
                    <input type="number" name="stock" value="<?php echo htmlspecialchars($reward['stock']); ?>" required>
                    <button type="submit" name="update">Update</button>
                </form>
                <a href="managereward.php?delete=<?php echo htmlspecialchars($reward['reward_id']); ?>" 
                   onclick="return confirm('Are you sure you want to delete this reward?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
<?php include('adminfooter.php'); ?>
</html>
