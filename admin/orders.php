<?php
include "../config/db.php";
include "Authencation/auth.php";

/* UPDATE STATUS */
if(isset($_GET['complete'])){
    $id = intval($_GET['complete']);
    mysqli_query($conn,"UPDATE orders SET status='Completed' WHERE id=$id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Management</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
*{box-sizing:border-box;}

body{
    margin:0;
    font-family:'Poppins',sans-serif;
    background:#f7efe5;
}

header{
    background:linear-gradient(135deg,#4b2e2e,#c19a6b);
    color:#fff;
    padding:20px 40px;
    font-size:24px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:15px;
    border-bottom:4px solid #c19a6b;
}

.back-btn{
    background:#4b2e2e;
    color:#fff;
    border:none;
    border-radius:8px;
    padding:8px 14px;
    cursor:pointer;
}

.container{
    max-width:1200px;
    margin:20px auto;
    padding:0 20px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
}

th{
    background:#4b2e2e;
    color:#fff;
    padding:14px;
    border:1px solid #c19a6b;
}

td{
    padding:12px;
    border:1px solid #c19a6b;
    text-align:center;
}

.status-pending{
    background:#ffc107;
    color:#000;
    padding:6px 12px;
    border-radius:6px;
}

.status-completed{
    background:#28a745;
    color:#fff;
    padding:6px 12px;
    border-radius:6px;
}

.view-btn{
    background:#0095ff;
    color:#fff;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
}

.complete-btn{
    background:#4b2e2e;
    color:#fff;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
}

.table-scroll{
    overflow-x:auto;
}
</style>
</head>

<body>

<header>
<button class="back-btn" onclick="location.href='home.php'">← BACK</button>
ORDER MANAGEMENT
</header>

<div class="container">
<div class="table-scroll">
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Phone</th>
<th>Payment</th>
<th>Location</th>
<th>Total</th>
<th>Date</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php
$i=1;
$q = mysqli_query($conn,"SELECT * FROM orders ORDER BY id DESC");
while($row=mysqli_fetch_assoc($q)):
?>
<tr>
<td><?= $i++ ?></td>
<td><?= $row['name'] ?></td>
<td><?= $row['phone'] ?></td>
<td><?= $row['payment_method'] ?></td>
<td><?= $row['location'] ?></td>
<td>$<?= $row['total'] ?></td>
<td><?= $row['created_at'] ?></td>
<td>
<?php if($row['status']=='Pending'): ?>
<span class="status-pending">Pending</span>
<?php else: ?>
<span class="status-completed">Completed</span>
<?php endif; ?>
</td>
<td>
<a class="view-btn" href="order_items.php?id=<?= $row['id'] ?>">View</a>

<?php if($row['status']=='Pending'): ?>
<a class="complete-btn" href="?complete=<?= $row['id'] ?>">Complete</a>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>
</div>

</body>
</html>
