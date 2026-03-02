<?php
ob_start();
session_start();
require 'config/db.php'; // PDO PostgreSQL connection
include 'includes/header.php';

// ================= CHECK ORDER ID =================
if(!isset($_GET['id'])){
    header("Location: products.php");
    exit;
}

$order_id = intval($_GET['id']);

// ================= HANDLE PAYMENT CONFIRM =================
if(isset($_POST['confirm_payment'])){
    // Redirect to recipe page after confirming payment
    header("Location: recipe.php?id=".$order_id);
    exit;
}

// ================= GET ORDER =================
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute([':id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

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
<link rel="icon" type="image/png" href="assets/images_app/Link2.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #FDF4F8;
    overflow-y: scroll;
}

/* Header */
.site-header {
    background: linear-gradient(135deg, #9C5F78, #C98AA5);
    padding: 25px 15px;
    color: #fff;
}

.header-content {
    max-width: 1200px;
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    text-align: center;
    flex-wrap: wrap;
}

.logo {
    width: 150px;
    height: auto;
}

.header-content h1 {
    margin: 0;
    font-size: 28px;
}

.header-content p {
    margin-top: 5px;
    font-size: 14px;
    opacity: 0.9;
}

/* Navigation */
.top-nav {
    max-width: 1200px;
    margin: 20px auto 10px auto;
    padding: 0 15px;
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.nav-link {
    text-decoration: none;
    padding: 8px 20px;
    border-radius: 25px;
    background: #D8A8B8;
    color: #fff;
    font-size: 14px;
    transition: 0.3s;
    text-align: center;
}

.nav-link:hover,
.nav-link.active {
    background: #9C5F78;
}

/* Bank container/card */
.bank-container {
    background: #fff0f5;
    width: 90%;
    max-width: 450px;
    padding: 30px 25px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(201,138,165,0.2);
    text-align: center;
    margin: 40px auto;
    transition: transform 0.2s, box-shadow 0.2s;
}

.bank-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(201,138,165,0.25);
}

.bank-container h2 {
    color: #9C5F78;
    margin-bottom: 20px;
    font-size: 22px;
}

/* Amount display */
.amount-row {
    display: flex;
    justify-content: center;
    align-items: center;
    background: #fffaf0;
    padding: 15px 20px;
    border-radius: 20px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(201,138,165,0.1);
}

.from-to {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 10px;
}

.from-to .label {
    font-size: 12px;
    color: #9C5F78;
    font-weight: 600;
    margin-bottom: 3px;
}

.from-to .value {
    font-size: 16px;
    font-weight: 700;
    color: #4b2e2e;
}

.arrow {
    font-size: 20px;
    font-weight: 700;
    color: #C98AA5;
    margin: 0 10px;
}

.total-amount {
    font-size: 20px;
    font-weight: 700;
    color: #9C5F78;
    margin-bottom: 15px;
}

/* QR Box */
.qr-box {
    padding: 15px;
    border-radius: 15px;
    border: 2px dashed #C98AA5;
    margin-bottom: 15px;
    background: #fffaf0;
}

.qr-box img {
    width: 220px;
    height: 220px;
    object-fit: cover;
}

/* Button */
.btn-submit {
    background: #9C5F78;
    color: white;
    border: none;
    padding: 12px;
    width: 100%;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    font-size: 16px;
    margin-top: 15px;
    transition: 0.3s;
}

.btn-submit:hover {
    opacity: 0.85;
}

/* Note */
.note {
    font-size: 14px;
    color: #555;
    margin-top: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
    }

    .logo {
        width: 120px;
    }

    .header-content h1 {
        font-size: 22px;
    }

    .top-nav {
        flex-wrap: nowrap;
        overflow-x: auto;
        justify-content: space-between;
        padding: 0 10px;
    }

    .nav-link {
        flex: 1 1 25%;
        padding: 10px 0;
        font-size: 13px;
    }

    .bank-container {
        padding: 25px 15px;
    }

    .amount-row {
        flex-direction: column;
        gap: 10px;
    }

    .qr-box img {
        width: 180px;
        height: 180px;
    }
}
</style>
</head>
<body>



<div class="bank-container">
    <h2>🏦 KHQR Payment</h2>

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

    <div class="total-amount">
        Total: $<?php echo number_format($total,2); ?>
    </div>

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
<?php include 'includes/footer.php'; ?>