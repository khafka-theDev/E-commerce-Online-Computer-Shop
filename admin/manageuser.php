<?php
session_start();
include('adminheader.php');
include("../include/connect.php");

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin.php");
    exit();
}

// Fetch all users
$query = "SELECT aid, afname, alname, email, membership_tier, active FROM accounts";
$result = mysqli_query($con, $query);

// Fetch membership applications
$applicationsQuery = "
    SELECT 
        membership_applications.id, 
        membership_applications.aid, 
        membership_applications.membership_tier, 
        membership_applications.price, 
        membership_applications.status, 
        accounts.afname, 
        accounts.alname 
    FROM 
        membership_applications 
    JOIN 
        accounts ON membership_applications.aid = accounts.aid 
    WHERE 
        membership_applications.status = 'Pending'";
$applicationsResult = mysqli_query($con, $applicationsQuery);

// Handle membership update
if (isset($_POST['update'])) {
    $aid = $_POST['aid'];
    $membership = $_POST['membership_tier'];
    $updateQuery = "UPDATE accounts SET membership_tier = '$membership' WHERE aid = $aid";
    mysqli_query($con, $updateQuery);
    header("Location: manageuser.php");
    exit();
}

// Handle account disabling
if (isset($_GET['disable'])) {
    $aid = $_GET['disable'];
    $disableQuery = "UPDATE accounts SET active = 0 WHERE aid = $aid";
    mysqli_query($con, $disableQuery);
    header("Location: manageuser.php");
    exit();
}

// Handle membership application approval
if (isset($_POST['approve'])) {
    $applicationId = $_POST['application_id'];
    $aid = $_POST['aid'];
    $membershipTier = $_POST['membership_tier'];

    $updateMembershipQuery = "UPDATE accounts SET membership_tier = '$membershipTier' WHERE aid = $aid";
    $updateApplicationQuery = "UPDATE membership_applications SET status = 'Approved' WHERE id = $applicationId";
    mysqli_query($con, $updateMembershipQuery);
    mysqli_query($con, $updateApplicationQuery);
    header("Location: manageuser.php");
    exit();
}

// Handle membership application denial
if (isset($_POST['deny'])) {
    $applicationId = $_POST['application_id'];
    $denyQuery = "UPDATE membership_applications SET status = 'Denied' WHERE id = $applicationId";
    mysqli_query($con, $denyQuery);
    header("Location: manageuser.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/invent.css">
</head>
<body>
    <h1>Manage Users</h1>

    <!-- Manage Users Table -->
    <h2>User Accounts</h2>
    <table border="1">
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Membership Tier</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($user = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $user['aid']; ?></td>
            <td><?php echo $user['afname'] . ' ' . $user['alname']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="aid" value="<?php echo $user['aid']; ?>">
                    <select name="membership_tier">
                        <option value="bronze" <?php echo $user['membership_tier'] == 'bronze' ? 'selected' : ''; ?>>Bronze</option>
                        <option value="silver" <?php echo $user['membership_tier'] == 'silver' ? 'selected' : ''; ?>>Silver</option>
                        <option value="gold" <?php echo $user['membership_tier'] == 'gold' ? 'selected' : ''; ?>>Gold</option>
                        <option value="platinum" <?php echo $user['membership_tier'] == 'platinum' ? 'selected' : ''; ?>>Platinum</option>
                    </select>
                    <button type="submit" name="update">Update</button>
                </form>
            </td>
            <td><?php echo $user['active'] ? 'Active' : 'Disabled'; ?></td>
            <td>
                <?php if ($user['active']): ?>
                <a class="delete" href="manageuser.php?disable=<?php echo $user['aid']; ?>" onclick="return confirm('Are you sure you want to disable this account?')">Disable</a>
                <?php else: ?>
                Disabled
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Manage Membership Applications -->
    <h2>Membership Applications</h2>
    <table border="1">
        <tr>
            <th>Application ID</th>
            <th>User</th>
            <th>Requested Membership Tier</th>
            <th>Price</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($application = mysqli_fetch_assoc($applicationsResult)): ?>
        <tr>
            <td><?php echo $application['id']; ?></td>
            <td><?php echo $application['afname'] . ' ' . $application['alname']; ?></td>
            <td><?php echo ucfirst($application['membership_tier']); ?></td>
            <td>RM<?php echo number_format($application['price'], 2); ?></td>
            <td><?php echo $application['status']; ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                    <input type="hidden" name="aid" value="<?php echo $application['aid']; ?>">
                    <input type="hidden" name="membership_tier" value="<?php echo $application['membership_tier']; ?>">
                    <button type="submit" name="approve">Approve</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                    <button type="submit" name="deny">Deny</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
<?php include('adminfooter.php'); ?>
</html>
