<?php
include "../config/db.php";   // PDO connection
include "Authencation/auth.php";

$id = intval($_GET['id'] ?? 0);

// ================= GET ORDER =================
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute(['id' => $id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order){
    die("Order not found.");
}

// ================= GET ORDER ITEMS =================
$stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = :id");
$stmtItems->execute(['id' => $id]);
$orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Items</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{
    font-family:Poppins,sans-serif;
    background:#f7efe5;
    padding:20px;
    margin:0;
}

.back{
    background:#4b2e2e;
    color:#fff;
    padding:8px 12px;
    border-radius:6px;
    text-decoration:none;
    display:inline-block;
    margin-bottom:20px;
    transition:0.3s;
}
.back:hover{
    background:#3a1f1f;
}

.table-scroll{
    width:100%;
    overflow-x:auto;
    border-radius:12px;
    box-shadow:0 6px 12px rgba(0,0,0,0.1);
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:400px;
    background:#fff;
}

th, td{
    padding:12px;
    border:1px solid #c19a6b;
    text-align:center;
}

th{
    background:#4b2e2e;
    color:#fff;
}

@media(max-width:430px){
    table, thead, tbody, th, td, tr{
        display:block;
    }

    thead tr{
        display:none;
    }

    td{
        text-align:left;
        border:none;
        border-bottom:1px solid #c19a6b;
        padding:10px;
        position: relative;
        padding-left:50%;
        margin-bottom:15px;
    }

    td::before{
        position:absolute;
        left:10px;
        width:45%;
        white-space:nowrap;
        font-weight:bold;
        color:#4b2e2e;
    }

    td:nth-of-type(1)::before{content:"Product";}
    td:nth-of-type(2)::before{content:"Price";}
    td:nth-of-type(3)::before{content:"Qty";}
    td:nth-of-type(4)::before{content:"Total";}
}
</style>
</head>
<body>

<h2>Order Detail - <?= htmlspecialchars($order['name']) ?></h2>
<a class="back" href="orders.php">← Back</a>

<div class="table-scroll">
<table>
<tr>
<th>Product</th>
<th>Price</th>
<th>Qty</th>
<th>Total</th>
</tr>

<?php foreach($orderItems as $row): ?>
<tr>
<td><?= htmlspecialchars($row['product_name']) ?></td>
<td>$<?= number_format($row['price'],2) ?></td>
<td><?= $row['qty'] ?></td>
<td>$<?= number_format($row['price'] * $row['qty'],2) ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

</body>
</html>