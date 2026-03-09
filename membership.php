<?php
session_start();
include('include/header.php');
include("include/connect.php");

// Ensure the user is logged in
if (!isset($_SESSION['aid']) || $_SESSION['aid'] <= 0) {
    echo "<script>
        alert('Please log in to apply for membership.');
        window.location.href = 'login.php';
    </script>";
    exit();
}

$aid = $_SESSION['aid'];

// Fetch user's current membership tier
$currentMembership = 'bronze'; // Default membership tier
$query = "SELECT membership_tier FROM accounts WHERE aid = $aid";
$result = mysqli_query($con, $query);
if ($row = mysqli_fetch_assoc($result)) {
    $currentMembership = $row['membership_tier'];
}

// Check if the user has a pending application
$pendingQuery = "SELECT COUNT(*) as pending_count FROM membership_applications WHERE aid = $aid AND status = 'Pending'";
$pendingResult = mysqli_query($con, $pendingQuery);
$hasPendingApplication = false;
if ($pendingRow = mysqli_fetch_assoc($pendingResult)) {
    $hasPendingApplication = $pendingRow['pending_count'] > 0;
}

// Map membership tiers to numeric values for comparison
$membershipLevels = [
    'bronze' => 1,
    'silver' => 2,
    'gold' => 3,
    'platinum' => 4,
];

// Discounts for each membership tier
$discounts = [
    'silver' => '10%',
    'gold' => '15%',
    'platinum' => '20%'
];

// Handle membership application submission
if (isset($_POST['apply'])) {
    if ($hasPendingApplication) {
        echo "<script>
            alert('You already have a pending membership application. Please wait for it to be approved.');
            window.location.href = 'membership.php';
        </script>";
        exit();
    }

    $newMembership = $_POST['membership'];
    $price = 0;

    switch ($newMembership) {
        case 'bronze':
            $price = 0.00;
            break;
        case 'silver':
            $price = 5.00;
            break;
        case 'gold':
            $price = 15.00;
            break;
        case 'platinum':
            $price = 30.00;
            break;
    }

    // Insert membership application into the database
    $query = "INSERT INTO membership_applications (aid, membership_tier, price, status) VALUES ($aid, '$newMembership', $price, 'Pending')";
    $result = mysqli_query($con, $query);

    if ($result) {
        echo "<script>
            alert('Your membership application has been submitted and is pending approval.');
            window.location.href = 'profile.php';
        </script>";
    } else {
        echo "<script>
            alert('Error submitting membership application. Please try again.');
            window.location.href = 'membership.php';
        </script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Membership</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="include/css/style.css" />
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tiers = document.querySelectorAll('.membership-tier');
            const membershipLevels = {
                'bronze': 1,
                'silver': 2,
                'gold': 3,
                'platinum': 4
            };
            const currentMembership = '<?php echo $currentMembership; ?>';
            const currentMembershipLevel = membershipLevels[currentMembership];

            tiers.forEach(tier => {
                const input = tier.querySelector('input');
                const message = tier.querySelector('.message');
                const tierValue = input.value;
                const tierLevel = membershipLevels[tierValue];

                // Listen for change events on each radio button
                input.addEventListener('change', () => {
                    // Clear all messages first
                    document.querySelectorAll('.message').forEach(msg => {
                        msg.textContent = '';
                    });

                    // Determine the message for the selected option
                    if (tierLevel === currentMembershipLevel) {
                        message.textContent = 'Add +1 month?';
                    } else if (tierLevel < currentMembershipLevel) {
                        message.textContent = 'Downgrading? :(';
                    } else if (tierLevel > currentMembershipLevel) {
                        message.textContent = 'Yeay, new VIP!';
                    }
                });
            });
        });
    </script>
</head>

<body>
    <div class="membership-container">
        <h2>Choose Your Membership Tier</h2>

        <form method="post">
            <div class="membership-options">
                <div class="membership-tier">
                    <input type="radio" id="bronze" name="membership" value="bronze" 
                        <?php if ($currentMembership === 'bronze') echo 'disabled'; ?> required>
                    <label for="bronze">Bronze (Free/no discount)</label>
                    <div class="message"></div>
                </div>
                <div class="membership-tier">
                    <input type="radio" id="silver" name="membership" value="silver" required>
                    <label for="silver">Silver (RM5.00/month - Discount: <?= $discounts['silver']; ?>)</label>
                    <div class="message"></div>
                </div>
                <div class="membership-tier">
                    <input type="radio" id="gold" name="membership" value="gold" required>
                    <label for="gold">Gold (RM15.00/month - Discount: <?= $discounts['gold']; ?>)</label>
                    <div class="message"></div>
                </div>
                <div class="membership-tier">
                    <input type="radio" id="platinum" name="membership" value="platinum" required>
                    <label for="platinum">Platinum (RM30.00/month - Discount: <?= $discounts['platinum']; ?>)</label>
                    <div class="message"></div>
                </div>
            </div>
            <button type="submit" name="apply" class="btnmember" 
                <?php if ($hasPendingApplication): ?> disabled <?php endif; ?>>
                Apply
            </button>
        </form>

        <?php if ($hasPendingApplication): ?>
            <p style="color: red; text-align: center; margin-top: 20px;">
                You already have a pending membership application. Please wait for it to be approved.
            </p>
        <?php endif; ?>
    </div>

    <?php include('include/footer.php'); ?>
</body>

</html>
