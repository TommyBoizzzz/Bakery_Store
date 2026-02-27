<?php
include "../config/db.php";   // PDO connection (pgsql)
include "Authencation/auth.php";

// ==================== UPDATE STATUS ====================
if(isset($_POST['update_status'])){
    $id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $current_tab = $_POST['current_tab']; // track current tab

    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute([
        'status' => $status,
        'id'     => $id
    ]);

    // Redirect back to the same tab
    header("Location: ".$_SERVER['PHP_SELF']."?status=".urlencode($current_tab));
    exit();
}

// ==================== FILTER STATUS ====================
$statuses = ['Pending','Success','Delivery','Pick Up','Done','Cancel']; // added Done
$current_status = $_GET['status'] ?? 'Pending'; // default to Pending

if(in_array($current_status, $statuses)){
    $stmt = $conn->prepare("SELECT * FROM orders WHERE status = :status ORDER BY id DESC");
    $stmt->execute(['status' => $current_status]);
} else {
    $stmt = $conn->query("SELECT * FROM orders ORDER BY id DESC");
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    background:#f2f2f2;
}

header{
    background: linear-gradient(135deg,#4b2e2e,#c19a6b);
    color:#fff;
    padding:20px 40px;
    font-size:20px;
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
    margin:30px auto;
    padding:0 20px;
}

.status-tabs{
    margin-bottom: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.status-tabs .tab{
    padding: 8px 16px;
    border-radius: 8px;
    background: #fff;
    color: #4b2e2e;
    text-decoration: none;
    border:1px solid #c19a6b;
    font-weight:500;
}

.status-tabs .tab.active{
    background:#4b2e2e;
    color:#fff;
}

.table-scroll{
    width:100%;
    overflow-x:auto;
    background:#fff;
    border-radius:12px;
    padding:10px;
}

table{
    width:100%;
    min-width:600px;
    border-collapse:collapse;
    font-size:14px;
}

th{
    background:#4b2e2e;
    color:#fff;
    padding:16px;
    border:1px solid #c19a6b;
    text-align:center;
}

td{
    padding:14px;
    border:1px solid #c19a6b;
    text-align:center;
}

.action-row{
    display:flex;
    align-items:center;
    gap:10px;
}

.status-select{
    padding:6px 10px;
    border-radius:6px;
    border:1px solid #c19a6b;
}

.view-btn, .update-btn{
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    font-weight:500;
    border:none;
    cursor:pointer;
}

.view-btn{
    background:#0095ff;
    color:#fff;
}

.update-btn{
    background:#4b2e2e;
    color:#fff;
}
</style>
</head>

<body>

<header>
<button class="back-btn" onclick="location.href='home.php'">← BACK</button>
ORDER MANAGEMENT
</header>

<div class="container">

<!-- STATUS TABS -->
<div class="status-tabs">
<?php
foreach($statuses as $status_tab):
    $active = ($status_tab == $current_status) ? 'active' : '';
?>
<a href="?status=<?= urlencode($status_tab) ?>" class="tab <?= $active ?>">
<?= $status_tab ?>
</a>
<?php endforeach; ?>
</div>

<!-- ORDERS TABLE -->
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

<?php foreach($orders as $row): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['phone']) ?></td>
<td><?= htmlspecialchars($row['payment_method']) ?></td>
<td><?= htmlspecialchars($row['location']) ?></td>
<td>$<?= number_format($row['total'],2) ?></td>
<td><?= $row['created_at'] ?></td>

<td>
<form method="post">
<input type="hidden" name="order_id" value="<?= $row['id'] ?>">
<input type="hidden" name="current_tab" value="<?= htmlspecialchars($current_status) ?>">
<div class="action-row">

<?php
// Determine next status options, always include Cancel
$next_status_options = [];
switch($row['status']){
    case 'Pending':
        $next_status_options[] = 'Success';
        break;
    case 'Success':
        $next_status_options[] = 'Delivery';
        break;
    case 'Delivery':
        $next_status_options[] = 'Pick Up';
        break;
    case 'Pick Up':
        $next_status_options[] = 'Done';
        break;
    case 'Done':
        $next_status_options[] = 'Done';
        break;
    default:
        $next_status_options[] = $row['status'];
        break;
}

// Always allow Cancel for any status except Cancel itself
if($row['status'] != 'Cancel'){
    $next_status_options[] = 'Cancel';
}
?>

<select name="status" class="status-select" <?= ($row['status']=='Cancel')?'disabled':'' ?>>
<?php foreach($next_status_options as $option): ?>
<option value="<?= $option ?>"><?= $option ?></option>
<?php endforeach; ?>
</select>

<button type="submit" name="update_status" class="update-btn" <?= ($row['status']=='Cancel')?'disabled':'' ?>>Update</button>
</div>
</form>
</td>

<td>
<a class="view-btn" href="order_items.php?id=<?= $row['id'] ?>">View</a>
</td>
</tr>
<?php endforeach; ?>

</table>
</div>
</div>

</body>
</html>