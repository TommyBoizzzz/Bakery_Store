<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BaBBoB Bakery</title>
<link rel="icon" type="image/png" href="assets/images_app/Link2.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ===== GLOBAL ===== */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: #fff0f5;
        color: #333;
        overflow-y: scroll;
    }

    a {
        text-decoration: none;
    }

    /* ===== HEADER ===== */
    .site-header {
        background: linear-gradient(135deg, #f7c8d8, #f0a6c0);
        padding: 30px 15px;
        color: #fff;
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        position: relative;
        z-index: 10;
        border-bottom-left-radius: 25px;
        border-bottom-right-radius: 25px;
    }

    .header-content {
        max-width: 1200px;
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 25px;
        flex-wrap: wrap;
        text-align: center;
    }

    .logo {
        width: 160px;
        transition: transform 0.3s;
    }

    .logo:hover {
        transform: scale(1.05);
    }

    .header-content h1 {
        margin: 0;
        font-size: 32px;
        font-weight: 700;
        letter-spacing: 1px;
        color: #ffffff;
        text-shadow: 1px 1px 4px rgba(0,0,0,0.2);
    }

    .header-content p {
        margin-top: 6px;
        font-size: 16px;
        font-weight: 500;
        opacity: 0.9;
        color: #fff;
    }

    /* ===== NAVIGATION (SINGLE ROW) ===== */
    .top-nav {
        max-width: 1200px;
        margin: 20px auto 15px auto;
        padding: 0 15px;
        display: flex;
        justify-content: space-between; /* evenly spread tabs in one row */
        flex-wrap: nowrap; /* no wrap */
    }

    .nav-link {
        flex: 1; /* all tabs same width */
        text-align: center;
        padding: 12px 0;
        margin: 0 5px;
        border-radius: 30px;
        background: #f08fb4;
        color: #fff;
        font-size: 15px;
        font-weight: 500;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 100%;
        top: 0;
        left: 0;
        background: rgba(255,255,255,0.2);
        transition: 0.3s;
        z-index: 1;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .nav-link:hover {
        background: #e870a0;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }

    .nav-link.active {
        background: #d8548a;
        box-shadow: 0 6px 15px rgba(0,0,0,0.3);
    }

    /* ===== MOBILE ===== */
    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
        }

        .logo {
            width: 130px;
        }

        .header-content h1 {
            font-size: 24px;
        }

        .top-nav {
            justify-content: space-between; /* keep one row */
            padding: 0 10px;
        }

        .nav-link {
            font-size: 14px;
            margin: 0 3px;
            padding: 10px 0;
        }
    }
</style>
</head>
<body>

<!-- HEADER -->
<header class="site-header">
    <div class="header-content">
        <img src="assets/images_app/Logo.png" alt="BaBBoB Bakery Logo" class="logo">
        <div>
            <h1>BaBBoB Bakery</h1>
            <p>Fresh cakes & bakery every day</p>
        </div>
    </div>
</header>

<!-- NAVIGATION -->
<nav class="top-nav">
    <a href="index.php" 
       class="nav-link <?php if($current_page=='index.php') echo 'active'; ?>">
       Home
    </a>

    <a href="products.php" 
       class="nav-link <?php if($current_page=='products.php') echo 'active'; ?>">
       Products
    </a>

    <a href="cart.php" 
       class="nav-link <?php if($current_page=='cart.php') echo 'active'; ?>">
       Cart
    </a>

    <a href="booking.php" 
       class="nav-link <?php if($current_page=='booking.php') echo 'active'; ?>">
       Booking
    </a>
</nav>

</body>
</html>