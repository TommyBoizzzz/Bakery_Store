<?php
include "../config/db.php";
include "Authencation/auth.php";

// ==================== UPDATE STATUS ====================
if(isset($_POST['update_status'])){
    $id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id=$id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// ==================== FILTER STATUS ====================
$status_filter = '';
$current_status = isset($_GET['status']) ? $_GET['status'] : 'All';
if($current_status != 'All'){
    $status_safe = mysqli_real_escape_string($conn, $current_status);
    $status_filter = "WHERE status='$status_safe'";
}

$q = mysqli_query($conn,"SELECT * FROM orders $status_filter ORDER BY id DESC");
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

/* Header */
header{
    background: linear-gradient(135deg,#4b2e2e,#c19a6b);
    color:#fff;
    padding:20px 40px;
    font-size:24px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:15px;
    border-bottom:4px solid #c19a6b;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.back-btn{
    background:#4b2e2e;
    color:#fff;
    border:none;
    border-radius:8px;
    padding:8px 14px;
    cursor:pointer;
    transition: 0.3s;
}
.back-btn:hover{
    background:#3a1f1f;
}

/* Container */
.container{
    max-width:1200px;
    margin:30px auto;
    padding:0 20px;
}

/* Status Tabs */
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
    transition:0.3s;
}
.status-tabs .tab:hover{
    background:#c19a6b;
    color:#fff;
}
.status-tabs .tab.active{
    background:#4b2e2e;
    color:#fff;
}

/* Table Scroll Wrapper */
.table-scroll{
    width:100%;
    overflow-x:auto;
    background:#fff;
    border-radius:12px;
    padding:10px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.1);
}

/* Custom horizontal scrollbar */
.table-scroll::-webkit-scrollbar{
    height:10px;
}
.table-scroll::-webkit-scrollbar-thumb{
    background:#c19a6b;
    border-radius:6px;
}
.table-scroll::-webkit-scrollbar-track{
    background:#f0f0f0;
}

/* Table */
.table-scroll table {
    width: 100%;
    min-width: 600px; /* minimum width for small screens */
    border-collapse: collapse;
    font-size: 14px;
}

th{
    background:#4b2e2e;
    color:#fff;
    padding:16px;
    border:1px solid #c19a6b;
    text-align:center;
    position: sticky;
    top:0;
    z-index:1;
}

td{
    padding:14px;
    border:1px solid #c19a6b;
    text-align:center;
}

/* Container for the row */
.action-row {
    display: flex;        /* make children align in a row */
    align-items: center;  /* vertically center items */
    gap: 10px;            /* space between dropdown and buttons */
}

/* Status Dropdown */
.status-select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #c19a6b;
    background: #fff;
    color: #000;
    cursor: pointer;
    font-weight: 500;
}

/* Buttons */
.view-btn, .update-btn {
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}

.view-btn{
    background:#0095ff;
    color:#fff;
}
.view-btn:hover{
    background:#007acc;
}

.update-btn{
    background:#4b2e2e;
    color:#fff;
}
.update-btn:hover{
    background:#3a1f1f;
}

/* Responsive: small screens */
@media(max-width:768px){
    header{
        flex-direction:column;
        gap:10px;
        font-size:20px;
    }
    .container{
        padding:0 10px;
    }
}
</style>
</head>

<body>

<header>
<button class="back-btn" onclick="location.href='home.php'">← BACK</button>
ORDER MANAGEMENT
</header>

<div class="container">

    <!-- Status Tabs -->
    <div class="status-tabs">
        <?php
        $statuses = ['All','Pending','Success','Delivery','Pick Up','Cancel'];
        foreach($statuses as $status_tab):
            $active = ($status_tab == $current_status) ? 'active' : '';
        ?>
            <a href="?status=<?= urlencode($status_tab) ?>" class="tab <?= $active ?>"><?= $status_tab ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
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

        <?php while($row=mysqli_fetch_assoc($q)): ?>
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
                    <div class="action-row">
                        <select name="status" class="status-select">
                            <option value="Pending" <?= $row['status']=='Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Success" <?= $row['status']=='Success' ? 'selected' : '' ?>>Success</option>
                            <option value="Delivery" <?= $row['status']=='Delivery' ? 'selected' : '' ?>>Delivery</option>
                            <option value="Pick Up" <?= $row['status']=='Pick Up' ? 'selected' : '' ?>>Pick Up</option>
                            <option value="Cancel" <?= $row['status']=='Cancel' ? 'selected' : '' ?>>Cancel</option>
                        </select>
                        <button type="submit" name="update_status" class="update-btn">Update</button>
                    </div>
                </form>
            </td>

            <td>
                <a class="view-btn" href="order_items.php?id=<?= $row['id'] ?>">View</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
