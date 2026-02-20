<?php
session_start();
include 'config/db.php';

// ==================== GET ORDER ====================
$order_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order){
    die("Order not found!");
}

// ==================== GET ORDER ITEMS ====================
$item_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$item_stmt->execute([$order_id]);
$items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

// ==================== TELEGRAM LINK ====================
$ownerTelegram = "https://t.me/LUYNALIN"; // change to your telegram username
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order Details - #<?= $order_id ?></title>
<style>
body{
    font-family:Poppins,sans-serif;
    background:#f7efe5;
    padding:20px;
    margin:0;
}
.container{
    max-width:800px;
    margin:auto;
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 0 15px rgba(0,0,0,0.1);
}
h2,h3{
    color:#4b2e2e;
    text-align:center;
    margin:5px 0;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
th,td{
    padding:12px;
    border:1px solid #c19a6b;
    text-align:center;
}
th{
    background:#4b2e2e;
    color:white;
}
.total{
    font-weight:bold;
}

/* Buttons container: left & right */
.button-row{
    display:flex;
    justify-content:space-between;
    margin-top:20px;
    gap:10px;
}

.back-btn,
.btn-telegram{
    display:inline-block;
    padding:12px 25px;
    border-radius:12px;
    font-weight:600;
    text-decoration:none;
    flex:1;
    text-align:center;
}

.back-btn{
    background:#4b2e2e;
    color:white;
}

.btn-telegram{
    background:#0088cc;
    color:white;
}

.back-btn:hover,
.btn-telegram:hover{
    opacity:0.85;
}

/* ===== Responsive for screens <= 430px ===== */
@media screen and (max-width:430px){
    table, thead, tbody, th, td, tr {
        display:block;
        width:100%;
    }
    thead tr {display:none;} 
    tr {margin-bottom:15px; border:1px solid #c19a6b; border-radius:12px; padding:10px;}
    td{
        text-align:right;
        padding-left:50%;
        position:relative;
        border:none;
        border-bottom:1px solid #c19a6b;
    }
    td::before{
        content: attr(data-label);
        position:absolute;
        left:10px;
        width:45%;
        padding-left:10px;
        font-weight:bold;
        text-align:left;
    }
    td:last-child{border-bottom:none;}

    /* Keep buttons in one row even on mobile */
    .button-row{
        flex-direction:row;
    }
}
</style>
</head>
<body>

<div class="container">
    <h2>🎉 Order Placed Successfully!</h2>
    <h3>Order ID: #<?= htmlspecialchars($order['id']) ?></h3>

    <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
    <p><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($order['location']) ?></p>

    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $total = 0;
        foreach($items as $row):
            $subtotal = $row['price'] * $row['qty'];
            $total += $subtotal;
        ?>
        <tr>
            <td data-label="Product"><?= htmlspecialchars($row['product_name']) ?></td>
            <td data-label="Price">$<?= number_format($row['price'],2) ?></td>
            <td data-label="Qty"><?= $row['qty'] ?></td>
            <td data-label="Subtotal">$<?= number_format($subtotal,2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3" class="total">Total</td>
            <td class="total">$<?= number_format($total,2) ?></td>
        </tr>
        </tbody>
    </table>

    <div class="button-row">
        <a href="products.php" class="back-btn">← Back to Products</a>
        <a href="<?= $ownerTelegram ?>" class="btn-telegram" target="_blank">💬 Chat with Owner</a>
    </div>
</div>

</body>
</html>