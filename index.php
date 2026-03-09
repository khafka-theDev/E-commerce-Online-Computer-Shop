<?php
session_start();

include("include/header.php");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ah Chong pakar IT</title>
    <link rel="stylesheet" href="include/css/style.css" />
</head>

<body>

    <section id="hero">
        <h4>Trade-in-offer</h4>
        <h2>Super value deals</h2>
        <h1>On all products</h1>
        <p>Save more with membership with up to 10% off!</p>
        <a href="shop.php">
            <button>Shop Now</button>
        </a>
    </section>

    <section id="feature" class="section-p1">
        <div class="fe-box">
            <img src="img/features/f1.png" alt="" />
            <h6>Free Shipping</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f2.png" alt="" />
            <h6>Online Order</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f3.png" alt="" />
            <h6>Save Money</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f4.png" alt="" />
            <h6>Promotions</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f5.png" alt="" />
            <h6>Happy Customer</h6>
        </div>
        <div class="fe-box">
            <img src="img/features/f6.png" alt="" />
            <h6>24/7 online available</h6>
        </div>
    </section>

    <section id="banner" class="section-m1">
        <h4>Summer Sale</h4>
        <h2>Up to <span>10% Off</span> - For Platinum Membership!</h2>
        <a href="shop.php">
            <button class="normal">Explore More</button>
        </a>
    </section>

    <section id="sm-banner" class="section-p1">
        <div class="banner-box">
            <h4>Silver tier</h4>
            <h2>Apply for new membership, get 3% off</h2>
            <span>The best classic is on sale at Ah Chong IT Shop</span>
            <a href="membership.php">
                <button class="white">Learn More</button>
            </a>
        </div>
        <div class="banner-box banner-box2">
            <h4>Coming This Week</h4>
            <h2>Tiba-tiba Ramadan Sale</h2>
            <span>The best classic coming on sale at Ah Chong IT Shop</span>
            <a href="shop.php">
                <button class="white">Collection</button>
            </a>
        </div>
    </section>

    <section id="banner3">
        <div class="banner-box">
            <h2>Silver Tier</h2>
            <h3> 3% OFF</h3>
        </div>
        <div class="banner-box banner-box2">
            <h2>Gold Tier</h2>
            <h3>5% OFF</h3>
        </div>
        <div class="banner-box banner-box3">
            <h2>Platinum Tier</h2>
            <h3>10% OFF</h3>
        </div>
    </section>

    <?php include('include/footer.php'); ?>

    <script src="js/script.js"></script>
</body>

</html>
