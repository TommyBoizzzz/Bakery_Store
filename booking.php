<?php
include 'config/db.php';
include 'includes/header.php';

$orders = [];

if(isset($_POST['search'])){
    $phone = trim($_POST['phone']);

    $stmt = $conn->prepare("SELECT * FROM orders WHERE phone = ? ORDER BY id DESC");
    $stmt->bind_param("s",$phone);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
        $orders[] = $row;
    }
}
?>

<style>
body{
    background:#f7efe5;
    font-family:'Poppins',sans-serif;
}

.track-wrapper{
    max-width:800px;
    margin:70px auto;
    background:white;
    padding:40px;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.track-wrapper h2{
    text-align:center;
    margin-bottom:25px;
    color:#4b2e2e;
}

.search-area{
    display:flex;
    gap:10px;
    justify-content:center;
    margin-bottom:30px;
}

.search-area input{
    padding:12px;
    width:260px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:14px;
}

.search-area button{
    padding:12px 25px;
    background:#4b2e2e;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
}

.search-area button:hover{
    opacity:0.85;
}

.booking-card{
    background:#fdf8f4;
    padding:20px;
    border-radius:12px;
    margin-bottom:15px;
    border-left:6px solid #4b2e2e;
}

.booking-card h4{
    margin:0 0 10px 0;
}

.status{
    font-weight:bold;
}

.no-result{
    text-align:center;
    color:red;
    margin-top:20px;
}
</style>

<div class="track-wrapper">

    <h2>Track Your Booking</h2>

    <form method="POST">
        <div class="search-area">
            <input type="text" name="phone" placeholder="Enter Your Phone Number" required>
            <button type="submit" name="search">Search</button>
        </div>
    </form>

    <?php if(isset($_POST['search'])): ?>

        <?php if(empty($orders)): ?>
            <div class="no-result">
                No booking found with this phone number.
            </div>
        <?php else: ?>

            <?php foreach($orders as $order): ?>
                <div class="booking-card">
                    <h4>Order #<?php echo $order['id']; ?></h4>
                    <p><strong>Name:</strong> <?php echo $order['name']; ?></p>
                    <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
                    <p><strong>Total:</strong> $<?php echo number_format($order['total'],2); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status"><?php echo $order['status']; ?></span>
                    </p>
                    <p><strong>Date:</strong> <?php echo $order['created_at']; ?></p>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
