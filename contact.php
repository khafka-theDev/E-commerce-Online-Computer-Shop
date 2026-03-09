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
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

    <link rel="stylesheet" href="include/css/style.css"/>

</head>

<body>

    <section id="contact-details" class="section-p1">
        <div class="details">
            <span>GET IN TOUCH</span>
            <h2>Visit shop or contact us today</h2>
            <h3>Ah Chong Enterprise</h3>
            <div>
                <li>
                    <i class="fal fa-map"></i>
                    <p>Jalan Panglima Senyum, Atas Gunung Jerai, Kedah.</p>
                </li>
                <li>
                    <i class="fal fa-envelope"></i>
                    <p>AhChong@gmail.com.my</p>
                </li>
                <li>
                    <i class="fal fa-phone-alt"></i>
                    <p>+0123456789</p>
                </li>
                <li>
                    <i class="fal fa-clock"></i>
                    <p>Open everyday except Friday: 9am to 5pm</p>

                </li>
            </div>
        </div>
        <div class="map">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4295.031841021603!2d100.43285961086644!3d5.79681173115635!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304b2546f9b0ea3b%3A0xcf4ee67b71891e67!2sGunung%20Jerai!5e1!3m2!1sen!2sus!4v1737474975492!5m2!1sen!2sus"
                width="600" height="450" style="border: 0" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

    <section id="form-details">
        <div class="people">
            <div>
                <img src="img/khafi.gif" alt="" />
                <p>
                    <span>Khafi Buloh</span> Founder and CEO <br />
                    Phone: +010-1010101<br />
                    Email:Buloh@gmail.com
                </p>
            </div>
            <div>
                <img src="img/aiman.gif" alt="" />
                <p>
                    <span>Aiman Rawang</span> Executive Marketing Manager <br />
                    Phone: +011-1111111<br />
                    Email:Rawangfly@gmail.com
                </p>
            </div>
            <div>
                <img src="img/ilham.gif" alt="" />
                <p>
                    <span>Ilham Gurun</span> Executive Human Resource Manager<br />
                    Phone: +012-2222222 <br />
                    Email:Gurun@gmail.com
                </p>
            </div>
            <div>
                <img src="img/muazzam.gif" alt="" />
                <p>
                    <span>Azzam Kacang</span> Executive Finance Manager<br />
                    Phone: +013-3333333 <br />
                    Email:Kacang@gmail.com
                </p>
            </div>
        </div>
    </section>
    <?php include('include/footer.php'); ?>
    <script src="js/script.js"></script>
</body>

</html>

<script>
window.addEventListener("unload", function() {
  // Call a PHP script to log out the user
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "logout.php", false);
  xhr.send();
});
</script>