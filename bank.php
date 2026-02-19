<?php
session_start();
include 'config/db.php';

if(!isset($_GET['id'])){
    header("Location: products.php");
    exit;
}

$order_id = intval($_GET['id']);

// ================= HANDLE SUBMIT =================
if(isset($_POST['confirm_payment'])){

    // Update order status
    $update = $conn->prepare("UPDATE orders SET status='Waiting Verification' WHERE id=?");
    $update->bind_param("i", $order_id);
    $update->execute();

    // Redirect to recipe page
    header("Location: recipe.php?id=".$order_id);
    exit;
}

// ================= GET ORDER =================
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if(!$order){
    die("Order not found.");
}

$total = $order['total'];
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KHQR Payment - BaBBoB Bakery</title>
<link rel="icon" type="image/png" href="assets/images_app/Link.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
/* ===== GLOBAL ===== */
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',sans-serif;background:#f7efe5;overflow-y:scroll;}

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
.logo{width:150px;height:auto;}
.header-content h1{margin:0;font-size:28px;}
.header-content p{margin-top:5px;font-size:14px;opacity:0.9;}

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
.nav-link.active{background:#4b2e2e;}

/* ===== BANK PAGE ===== */
.bank-container{
    background:white;
    width:90%;
    max-width:450px;
    padding:30px 25px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
    text-align:center;
    margin:40px auto;
}
.bank-container h2{
    color:#4b2e2e;
    margin-bottom:20px;
    font-size:22px;
}
/* FROM → TO Row */
.amount-row{
    display:flex;
    justify-content:center;
    align-items:center;
    background:#fffaf0;
    padding:15px 20px;
    border-radius:20px;
    margin-bottom:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}
.from-to{
    display:flex;
    flex-direction:column;
    align-items:center;
    margin: 0 10px;
}
.from-to .label{
    font-size:12px;
    color:#4b2e2e;
    font-weight:600;
    margin-bottom:3px;
}
.from-to .value{
    font-size:16px;
    font-weight:700;
    color:#333;
}
.arrow{
    font-size:20px;
    font-weight:700;
    color:#c19a6b;
    margin: 0 10px;
}
/* Total Amount */
.total-amount{
    font-size:20px;
    font-weight:700;
    color:#4b2e2e;
    margin-bottom:15px;
}
/* QR Box */
.qr-box{
    padding:15px;
    border-radius:15px;
    border:2px dashed #c19a6b;
    margin-bottom:15px;
    background:#fffaf0;
}
.qr-box img{
    width:220px;
    height:220px;
}
/* Button */
.btn-submit{
    background:#4b2e2e;
    color:white;
    border:none;
    padding:12px;
    width:100%;
    border-radius:25px;
    cursor:pointer;
    font-weight:600;
    font-size:16px;
    margin-top:15px;
}
.btn-submit:hover{opacity:0.85;}
/* Note */
.note{
    font-size:14px;
    color:#555;
    margin-top:10px;
}

/* ===== MOBILE ===== */
@media (max-width:768px){
    .header-content{flex-direction:column;}
    .logo{width:120px;}
    .header-content h1{font-size:22px;}
    .top-nav{flex-wrap:nowrap;overflow-x:hidden;justify-content:space-between;padding:0 10px;}
    .nav-link{flex:1 1 25%;padding:10px 0;font-size:13px;}
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
    <a href="index.php" class="nav-link <?php if($current_page=='index.php') echo 'active'; ?>">Home</a>
    <a href="products.php" class="nav-link <?php if($current_page=='products.php') echo 'active'; ?>">Products</a>
    <a href="cart.php" class="nav-link <?php if($current_page=='cart.php') echo 'active'; ?>">View Cart</a>
    <a href="booking.php" class="nav-link <?php if($current_page=='booking.php') echo 'active'; ?>">My Booking</a>
</nav>

<!-- BANK PAYMENT -->
<div class="bank-container">

    <h2>🏦 KHQR Payment</h2>

    <!-- FROM → TO Row -->
    <div class="amount-row">
        <div class="from-to">
            <span class="label">FROM:</span>
            <span class="value"><?php echo htmlspecialchars($order['name']); ?></span>
        </div>
        <div class="arrow">→</div>
        <div class="from-to">
            <span class="label">TO:</span>
            <span class="value">NALIN LUY</span>
        </div>
    </div>

    <!-- Total -->
    <div class="total-amount">
        Total: $<?php echo number_format($total,2); ?>
    </div>

    <!-- QR Code -->
    <div class="qr-box">
        <img src="assets/images_app/khqr.png" alt="KHQR Code">
    </div>

    <div class="note">
        Scan this QR using your banking app and complete the payment.
    </div>

    <form method="POST">
        <button type="submit" name="confirm_payment" class="btn-submit">
            I Have Paid
        </button>
    </form>

</div>

</body>
</html>
