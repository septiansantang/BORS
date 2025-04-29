<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../CSS/Borsmenlanding.css">
    <title>BORSMENT</title>
</head>
<body>
    <?php
    session_start();
    ?>
    <nav>
        <div class="header">
            <div class="logo">
                <a href="#">BORS<span>MEN</span></a>
            </div>
            <div class="nav_button_menu" id="menu-bors">
                <span><i class="ri-menu-line"></i></span>
            </div>
        </div>
        <ul class="nav_link" id="nav_link">
            <li><a href="#">Blog</a></li> 
            <li><a href="#">How to</a></li>               
            <li><a href="#">About us</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <div class="nav_btn">
            <!-- Tampilkan tombol sesuai status login -->
            <
            <?php if: ?>
                <button class="btn sign-in"><a href="php/daftar.php">Daftar</a></button>
                <button class="btn sign-up"><a href="php/daftar.php">Masuk</a></button>
            <?php endif; ?>
        </div>
    </nav>
    <header class="header_container">
        <div class="header_image">
            <div class="header_image_card header_image_card-1">
                <span><i class="ri-video-line"></i></span>
                video
            </div>
            <div class="header_image_card header_image_card-2">
                <span><i class="ri-cash-line"></i></span>
                Money 
            </div>
            <div class="header_image_card header_image_card-3">
                <span><i class="ri-earth-line"></i></span>
                Iklan 
            </div>
            <div class="header_image_card header_image_card-4">
                <span><i class="ri-tiktok-line"></i></span>
                social media
            </div>
            <img src="assets/IMG-20211213-WA0011-removebg-preview__1_-removebg-preview.png" alt="header">
            <div class="header_content"></div>
        </div>
        <div class="header_content">
            <h1>LET'S GO! <br/> <span>BORSPEEP's</span> Kolaborasi Bisnis dan Influencer</h1>
            <p>
                Temukan influencer terbaik, bangun kemitraan strategis, dan perluas jangkauan bisnis Anda dengan cara yang mudah, cepat, dan efektif. Mari bersama-sama menciptakan peluang tanpa batas!
            </p>
            <form action="search.php" method="GET">
                <div class="input_row">
                    <div class="input_group">
                        <h5>INFLUENCER</h5>
                        <div>
                            <span><i class="ri-creative-commons-by-fill"></i></span>
                            <input type="text" name="influencer" placeholder="Nama Influencer">
                        </div>
                    </div>
                    <div class="input_group">
                        <h5>BISNIS</h5>
                        <div>
                            <span><i class="ri-building-4-line"></i></span>
                            <input type="text" name="business" placeholder="Nama Bisnis">
                        </div>
                    </div>
                </div>
                <button type="submit" class="submit">Search</button>
            </form>
            <div class="bar">
                Copyright &copy; 2023 Borsmen. All rights reserved
            </div>
        </div>
    </header>
    <section class="about_section">
    <div class="container">
        <h2>Tentang Kami</h2>
        <p>Ini adalah teks tentang kami.</p>
    </div>
    </section>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="../js/Borsmenlanding.js"></script>
</body>
</html>
