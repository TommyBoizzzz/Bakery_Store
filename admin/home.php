<?php
session_start();
include 'Authencation/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>BaBBoB Bakery ADMIN</title>
<link rel="icon" type="image/png" href="/assets/images_app/Link.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box;margin:0;padding:0;}

html,body{
    height:100%;
    font-family:'Poppins',sans-serif;
    background:#f7efe5;
}

/* Header */
.header{
    background:rgba(75,46,46,0.95);
    color:#fff;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:4px solid #c19a6b;
    box-shadow:0 4px 10px rgba(0,0,0,0.15);
}

.header h2{
    font-size:24px;
}

.logout{
    background:#ff4b4b;
    color:#fff;
    border:none;
    padding:8px 16px;
    border-radius:8px;
    cursor:pointer;
    font-size:14px;
    transition:0.3s;
}

.logout:hover{
    opacity:0.85;
}

/* Container */
.container{
    width:100%;
    max-width:1400px;
    margin:0 auto;
    padding:30px;
    min-height:calc(100vh - 70px);
}

/* Welcome */
.welcome h3{
    color:#4b2e2e;
    margin-bottom:5px;
    font-size:24px;
}

.welcome p{
    color:#4b2e2e;
    margin-bottom:30px;
    font-size:16px;
}

/* Dashboard */
.dashboard{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:25px;
}

.card{
    background:#f7efe5;
    padding:30px 20px;
    border-radius:20px;
    box-shadow:0 15px 35px rgba(0,0,0,0.15);
    text-align:center;
    transition:0.3s;
}

.card:hover{
    transform:translateY(-8px);
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
}

.card h3{
    font-size:20px;
    color:#4b2e2e;
    margin-bottom:12px;
}

.card p{
    color:#777;
    font-size:14px;
    margin-bottom:20px;
}

.card a{
    text-decoration:none;
    background:#4b2e2e;
    color:#fff;
    padding:12px 20px;
    border-radius:10px;
    font-size:14px;
    display:inline-block;
    transition:0.3s;
}

.card a:hover{
    opacity:0.9;
}

/* Responsive */
@media(min-width:600px){
    .dashboard{
        grid-template-columns:repeat(2,1fr);
    }
}

@media(min-width:992px){
    .dashboard{
        grid-template-columns:repeat(4,1fr);
    }
}
</style>
</head>

<body>

<div class="header">
    <h2>BaBBoB Bakery ADMIN</h2>
    <form method="POST" action="Authencation/logout.php">
        <button class="logout">Logout</button>
    </form>
</div>

<div class="container">

    <div class="welcome">
        <h3>
            Welcome, 
            <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?> 👋
        </h3>
        <p>Manage your bakery easily from here</p>
    </div>

    <div class="dashboard">
        <div class="card">
            <h3>👤 Users</h3>
            <p>Manage admin & users</p>
            <a href="#">Manage</a>
        </div>

        <div class="card">
            <h3>💾 Poster</h3>
            <p>Manage Poster Sliders</p>
            <a href="poster.php">Manage</a>
        </div>

        <div class="card">
            <h3>🍞 Category</h3>
            <p>Manage Bakery Categories</p>
            <a href="category.php">Manage</a>
        </div>

        <div class="card">
            <h3>🎂 Products</h3>
            <p>Manage Bakery Products</p>
            <a href="product.php">Manage</a>
        </div>

        <div class="card">
            <h3>🛒 Orders</h3>
            <p>View customer orders</p>
            <a href="orders.php">View</a>
        </div>

        <div class="card">
            <h3>📊 Reports</h3>
            <p>Sales & performance reports</p>
            <a href="#">View</a>
        </div>
    </div>

</div>

</body>
</html>