<?php
session_start();
include("include/header.php");
include("include/connect.php");

// Ensure the user is logged in
if (!isset($_SESSION['aid']) || $_SESSION['aid'] <= 0) {
    header("Location: login.php");
    exit();
}

$aid = $_SESSION['aid'];

// Fetch user points
$query = "SELECT membership_points FROM accounts WHERE aid = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $aid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userPoints = $user['membership_points'];

// Handle reward claim
if (isset($_POST['claim_reward'])) {
    $rewardId = intval($_POST['reward_id']);

    // Fetch reward details
    $query = "SELECT points, stock FROM reward WHERE reward_id = ? AND stock > 0";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $rewardId);
    $stmt->execute();
    $rewardResult = $stmt->get_result();
    $reward = $rewardResult->fetch_assoc();

    if ($reward) {
        if ($userPoints >= $reward['points']) {
            $con->begin_transaction(); // Start a transaction

            try {
                // Deduct points and update stock
                $newPoints = $userPoints - $reward['points'];
                $updatePointsQuery = "UPDATE accounts SET membership_points = ? WHERE aid = ?";
                $updatePointsStmt = $con->prepare($updatePointsQuery);
                $updatePointsStmt->bind_param("ii", $newPoints, $aid);
                $updatePointsStmt->execute();

                $newStock = $reward['stock'] - 1;
                $updateStockQuery = "UPDATE reward SET stock = ? WHERE reward_id = ?";
                $updateStockStmt = $con->prepare($updateStockQuery);
                $updateStockStmt->bind_param("ii", $newStock, $rewardId);
                $updateStockStmt->execute();

                // Log reward claim
                $logClaimQuery = "INSERT INTO reward_claim_history (aid, reward_id, claim_date) VALUES (?, ?, NOW())";
                $logClaimStmt = $con->prepare($logClaimQuery);
                $logClaimStmt->bind_param("ii", $aid, $rewardId);
                $logClaimStmt->execute();

                $con->commit(); // Commit the transaction

                echo "<script>
                    alert('Reward claimed successfully!');
                    window.location.href = 'reward.php';
                </script>";
            } catch (Exception $e) {
                $con->rollback(); // Roll back the transaction
                echo "<script>alert('Error claiming reward: {$e->getMessage()}');</script>";
            }
        } else {
            echo "<script>alert('You do not have enough points to claim this reward.');</script>";
        }
    } else {
        echo "<script>alert('Reward is out of stock or does not exist.');</script>";
    }
}

// Fetch available rewards
$query = "SELECT reward_id, reward_name, description, points, stock, image_path FROM reward WHERE stock > 0";
$rewardsStmt = $con->prepare($query);
$rewardsStmt->execute();
$rewards = $rewardsStmt->get_result();

// Fetch reward claim history
$historyQuery = "
    SELECT r.reward_name, r.description, h.claim_date 
    FROM reward_claim_history h
    JOIN reward r ON h.reward_id = r.reward_id
    WHERE h.aid = ?
    ORDER BY h.claim_date DESC";
$historyStmt = $con->prepare($historyQuery);
$historyStmt->bind_param("i", $aid);
$historyStmt->execute();
$history = $historyStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Rewards</title>
    <link rel="stylesheet" href="include/css/style.css">
</head>
<body>
<div class="center-container">
    <h1>Claim Your Rewards</h1>
    <p>Your Points: <?php echo htmlspecialchars($userPoints); ?></p>
</div>

<div class="rewards-container">
    <?php while ($reward = $rewards->fetch_assoc()): ?>
        <div class="reward-item">
            <img src="<?php echo htmlspecialchars($reward['image_path']); ?>" alt="Reward Image" onerror="this.src='fallback.jpg';">
            <h3><?php echo htmlspecialchars($reward['reward_name']); ?></h3>
            <p><?php echo htmlspecialchars($reward['description'] ?? 'No description available.'); ?></p>
            <p>Points Required: <?php echo htmlspecialchars($reward['points']); ?></p>
            <p>Stock Available: <?php echo htmlspecialchars($reward['stock']); ?></p>
            <form method="post">
                <input type="hidden" name="reward_id" value="<?php echo $reward['reward_id']; ?>">
                <button type="submit" name="claim_reward">Claim Reward</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

<div class="order-history">
    <h2>Reward Claim History</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Reward Name</th>
                <th>Description</th>
                <th>Date Claimed</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($history && $history->num_rows > 0): ?>
                <?php while ($entry = $history->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['reward_name']); ?></td>
                        <td><?php echo htmlspecialchars($entry['description']); ?></td>
                        <td><?php echo htmlspecialchars($entry['claim_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No reward claims found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>