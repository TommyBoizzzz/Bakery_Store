<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BaBBoB Bakery</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
    *{
        box-sizing:border-box;
        margin:0;
        padding:0;
    }

    body{
        font-family:'Poppins',sans-serif;
        background:#f7efe5;
        overflow-y:scroll;
    }

    /* ===== HEADER ===== */
    .site-header{
        background:linear-gradient(135deg,#4b2e2e,#c19a6b);
        padding:25px 15px;
        color:#fff;
    }

    .header-content{
        max-width:1200px;
        margin:auto;
        display:flex;
        align-items:center;
        justify-content:center;
        gap:15px;
        text-align:center;
        flex-wrap:wrap;
    }

    .logo{
        width:150px;
        height:auto;
    }

    .header-content h1{
        margin:0;
        font-size:28px;
    }

    .header-content p{
        margin-top:5px;
        font-size:14px;
        opacity:0.9;
    }

    /* ===== NAVIGATION ===== */
    .top-nav{
        max-width:1200px;
        margin:20px auto 10px auto;
        padding:0 15px;
        display:flex;
        gap:15px;
        justify-content:center;
        flex-wrap:wrap;
    }

    .nav-link{
        text-decoration:none;
        padding:8px 20px;
        border-radius:25px;
        background:#8b5e3c;
        color:#fff;
        font-size:14px;
        transition:0.3s;
        text-align:center;
    }

    .nav-link:hover,
    .nav-link.active{
        background:#4b2e2e;
    }

    /* ===== MOBILE ===== */
    @media (max-width:768px){

        .header-content{
            flex-direction:column;
        }

        .logo{
            width:120px;
        }

        .header-content h1{
            font-size:22px;
        }

        .top-nav{
            flex-wrap:nowrap;
            overflow-x:hidden;
            justify-content:space-between;
            padding:0 10px;
        }

        .nav-link{
            flex:1 1 25%;
            padding:10px 0;
            font-size:13px;
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
       View Cart
    </a>

    <a href="profile.php" 
       class="nav-link <?php if($current_page=='profile.php') echo 'active'; ?>">
       Profile
    </a>
</nav>
