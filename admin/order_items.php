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
<style>
body{font-family:Poppins;background:#f7efe5;padding:30px;}
table{width:100%;border-collapse:collapse;background:#fff;}
th,td{padding:12px;border:1px solid #c19a6b;text-align:center;}
th{background:#4b2e2e;color:#fff;}
.back{background:#4b2e2e;color:#fff;padding:8px 12px;border-radius:6px;text-decoration:none;}
</style>
</head>
<body>

<h2>Order Detail - <?= $order['name'] ?></h2>
<a class="back" href="orders.php">← Back</a>
<br><br>

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
<td><?= $row['product_name'] ?></td>
<td>$<?= $row['price'] ?></td>
<td><?= $row['qty'] ?></td>
<td>$<?= $row['price'] * $row['qty'] ?></td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
