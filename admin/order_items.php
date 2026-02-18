<?php
include "../config/db.php";
include "Authencation/auth.php";

$id = intval($_GET['id']);
$order = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM orders WHERE id=$id"));
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

/* Back button */
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

/* Table styles */
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

/* ===== Responsive for small screens (≤430px) ===== */
@media(max-width:430px){
    table, thead, tbody, th, td, tr{
        display:block;
    }

    thead tr{
        display:none; /* hide table header */
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

<?php
$items = mysqli_query($conn,"SELECT * FROM order_items WHERE order_id=$id");
while($row=mysqli_fetch_assoc($items)):
?>
<tr>
<td><?= htmlspecialchars($row['product_name']) ?></td>
<td>$<?= number_format($row['price'],2) ?></td>
<td><?= $row['qty'] ?></td>
<td>$<?= number_format($row['price'] * $row['qty'],2) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

</body>
</html>
