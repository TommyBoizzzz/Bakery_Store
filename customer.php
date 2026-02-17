<?php
session_start();
include 'config/db.php';

$cart = $_SESSION['cart'] ?? [];

if(empty($cart)){
    echo "<script>alert('Your cart is empty!'); window.location='products.php';</script>";
    exit;
}

// Calculate total
$total = 0;
foreach($cart as $item){
    $total += $item['price'] * $item['qty'];
}

// Handle order submission
if(isset($_POST['place_order'])){

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'] ?? '';
    $location = $_POST['location'];

    if(empty($payment_method)){
        die("Payment method is required.");
    }

    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (name, phone, payment_method, location, total, created_at, status) 
        VALUES (?,?,?,?,?,NOW(),'Pending')
    ");

    if(!$stmt){
        die("Prepare failed: ".$conn->error);
    }

    $stmt->bind_param("ssssd", 
        $name,
        $phone,
        $payment_method,   // ✅ FIXED
        $location,
        $total
    );

    if(!$stmt->execute()){
        die("Execute failed: ".$stmt->error);
    }

    $order_id = $stmt->insert_id;

    // Insert order items
    $item_stmt = $conn->prepare("
        INSERT INTO order_items 
        (order_id, product_id, price, qty) 
        VALUES (?,?,?,?)
    ");

    if(!$item_stmt){
        die("Prepare failed for items: ".$conn->error);
    }

    foreach($cart as $item){

        $product_id = $item['id'];
        $price = $item['price'];
        $qty = $item['qty'];

        $item_stmt->bind_param("iidi", 
            $order_id,
            $product_id,
            $price,
            $qty
        );

        if(!$item_stmt->execute()){
            die("Execute failed for items: ".$item_stmt->error);
        }
    }

    unset($_SESSION['cart']);

    echo "<script>alert('Order placed successfully!'); window.location='products.php';</script>";
    exit;
}

include 'includes/header.php';
?>

<style>
body{background:#f7efe5;}
.cart-container{
    max-width:700px;
    margin:50px auto;
    padding:20px;
    background:white;
    border-radius:12px;
    box-shadow:0 0 15px rgba(0,0,0,0.1);
}
.cart-container h2{
    margin-bottom:25px;
    text-align:center;
}
.form-group{margin-bottom:15px;}
.form-group label{
    display:block;
    margin-bottom:6px;
    font-weight:bold;
}
.form-group input, 
.form-group select{
    width:100%;
    padding:8px;
    border-radius:6px;
    border:1px solid #ccc;
}
#map{
    height:300px;
    width:100%;
    margin-top:10px;
    border-radius:12px;
}
.btn-checkout{
    background:#4b2e2e;
    border:none;
    padding:10px 25px;
    border-radius:20px;
    color:white;
    cursor:pointer;
    width:100%;
}
.btn-checkout:hover{opacity:0.8;}
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<div class="cart-container">
<h2>Customer Information</h2>

<form method="POST">

    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" required>
    </div>

    <div class="form-group">
        <label>Phone Number (Telegram)</label>
        <input type="text" name="phone" required>
    </div>

    <div class="form-group">
        <label>Payment Method</label>
        <select name="payment_method" required>
            <option value="">-- Select Payment --</option>
            <option value="Cash">Cash</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Mobile Payment">Mobile Payment</option>
        </select>
    </div>

    <div class="form-group">
        <label>Location</label>
        <input type="text" 
               id="location" 
               name="location" 
               placeholder="Click on the map to select your location" 
               required readonly>

        <div id="map"></div>
    </div>

    <div class="form-group">
        <strong>Total: $<?php echo number_format($total,2); ?></strong>
    </div>

    <button type="submit" name="place_order" class="btn-checkout">
        Place Order
    </button>

</form>
</div>

<script>
// Leaflet Map (No Google API needed)
let map = L.map('map').setView([11.5564, 104.9282], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

let marker;

map.on('click', function(e){

    if(marker){
        map.removeLayer(marker);
    }

    marker = L.marker(e.latlng).addTo(map);

    document.getElementById('location').value =
        e.latlng.lat.toFixed(6) + "," +
        e.latlng.lng.toFixed(6);
});
</script>

<?php include 'includes/footer.php'; ?>
