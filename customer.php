<?php
ob_start();
session_start();
require 'config/db.php'; // PDO PostgreSQL connection
include 'includes/header.php';

// ==================== CART CHECK ====================
$cart = $_SESSION['cart'] ?? [];
if(empty($cart)){
    echo "<script>alert('Your cart is empty!'); window.location='products.php';</script>";
    exit;
}

// ==================== CALCULATE TOTAL ====================
$total = 0;
foreach($cart as $item){
    $total += $item['price'] * $item['qty'];
}

// ==================== TELEGRAM CONFIG ====================
function sendTelegramMessage($message){
    global $botToken, $groupID, $topicID;

    $url = "https://api.telegram.org/bot".$botToken."/sendMessage";
    $data = [
        'chat_id' => $groupID,
        'message_thread_id' => $topicID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data),
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if(!$result){
        die("Telegram failed. Check bot permission.");
    }
}

// ==================== PLACE ORDER ====================
if(isset($_POST['place_order'])){

    $name = trim($_POST['name']) ?: "Guest";
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'] ?? '';
    $location = $_POST['location'];

    // ================= SERVER VALIDATION =================
    if(empty($phone) || empty($payment_method) || empty($location)){
        die("Phone, payment and location are required.");
    }

    if($payment_method === "Bank Transfer" && ($name === "" || $name === "Guest")){
        die("Please enter your real name for Bank Transfer.");
    }

    try {
        // ================= START TRANSACTION =================
        $conn->beginTransaction();

        // ================= INSERT ORDER =================
        $stmt = $conn->prepare("
            INSERT INTO orders
            (name, phone, payment_method, location, total, status, created_at)
            VALUES (:name, :phone, :payment_method, :location, :total, :status, NOW())
        ");

        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':payment_method' => $payment_method,
            ':location' => $location,
            ':total' => $total,
            ':status' => 'Pending'
        ]);

        $order_id = $conn->lastInsertId();

        // ================= INSERT ORDER ITEMS =================
        $item_stmt = $conn->prepare("
            INSERT INTO order_items 
            (order_id, product_id, product_name, price, qty, subtotal) 
            VALUES (:order_id, :product_id, :product_name, :price, :qty, :subtotal)
        ");

        foreach($cart as $item){
            $item_stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $item['id'],
                ':product_name' => $item['name'],
                ':price' => $item['price'],
                ':qty' => $item['qty'],
                ':subtotal' => $item['price'] * $item['qty']
            ]);
        }

        // ================= SEND TELEGRAM =================
        $map_link = "https://www.google.com/maps/search/?api=1&query=" . $location;
        $message  = "🍰 <b>New Order - BaBBoB Bakery</b>\n\n";
        $message .= "🆔 Order ID: #".$order_id."\n";
        $message .= "👤 Name: ".$name."\n";
        $message .= "📞 Phone: ".$phone."\n";
        $message .= "💳 Payment: ".$payment_method."\n";
        $message .= "📍 Location: <a href='".$map_link."'>View Map</a>\n";
        $message .= "💰 Total: $".number_format($total,2)."\n";
        $message .= "📦 Status: Pending";
        sendTelegramMessage($message);

        // ================= COMMIT TRANSACTION =================
        $conn->commit();

        // ================= CLEAR CART =================
        unset($_SESSION['cart']);

        // ================= REDIRECT =================
        if($payment_method === "Bank Transfer"){
            header("Location: bank.php?id=".$order_id);
        } else {
            header("Location: recipe.php?id=".$order_id);
        }
        exit;

    } catch(PDOException $e){
        $conn->rollBack();
        die("Order failed: ".$e->getMessage());
    }
}
?>

<style>
body{
    background:#fdf4f8; 
    font-family:'Poppins',sans-serif;
}

.cart-container{
    max-width:700px;
    margin:30px auto;
    padding:20px;
    background:white;
    border-radius:12px;
    box-shadow:0 0 15px rgba(201,138,165,0.25); /* pink shadow */
}

.cart-container h2{
    text-align:center;
    color:#9c5f78; /* elegant rose */
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    margin-bottom:6px;
    font-weight:bold;
    color:#c98aa5;
}

.form-group input,
.form-group select{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #d8a8b8;
    outline:none;
    transition:0.3s;
}

.form-group input:focus,
.form-group select:focus{
    border-color:#c98aa5;
    box-shadow:0 0 6px rgba(201,138,165,0.4);
}

#map{
    height:300px;
    margin-top:10px;
    border-radius:12px;
    border:2px solid #f3c6d3;
}

/* Checkout Button */
.btn-checkout{
    background:#c98aa5;
    border:none;
    padding:12px 25px;
    border-radius:20px;
    color:white;
    cursor:pointer;
    width:100%;
    font-weight:600;
    margin-top:10px;
    transition:0.3s;
}

.btn-checkout:hover{
    background:#b76e8a;
}
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<div class="cart-container">
<h2>Customer Information</h2>

<form method="POST" id="checkoutForm" onsubmit="return validateForm();">

    <div class="form-group">
        <label>Name</label>
        <input type="text" 
            name="name" 
            id="nameInput"
            value="Guest"
            placeholder="Enter your name"
            onclick="clearGuest()"
        >
    </div>

    <div class="form-group">
        <label>Phone Number (Telegram)</label>
        <input 
            type="text" 
            name="phone" 
            placeholder="Enter your phone for Telegram" 
            pattern="\d{8,}" 
            title="Please enter at least 8 digits" 
            maxlength="15" 
            inputmode="numeric"
            required
        >
    </div>

    <div class="form-group">
        <label>Payment Method</label>
        <select name="payment_method" id="paymentSelect" required onchange="paymentChanged()">
            <option value="">-- Select Payment --</option>
            <option value="Cash">CASH</option>
            <option value="Bank Transfer">BANK</option>
        </select>
    </div>

    <div class="form-group">
        <label>Location</label>
        <input type="text" id="location" name="location" placeholder="Your current location will be detected" required readonly>
        <div id="map"></div>
    </div>

    <button type="button" onclick="refreshMap()" class="btn-checkout">Refresh Map</button>
    <button type="submit" name="place_order" class="btn-checkout">Place Order</button>

</form>
</div>

<script>
let map = L.map('map').setView([11.5564, 104.9282], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
let marker, circle;

function clearGuest(){
    const input = document.getElementById("nameInput");
    if(input.value === "Guest"){ input.value = ""; }
}

function loadLocation(){
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(function(position){
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            document.getElementById('location').value = lat.toFixed(6) + "," + lng.toFixed(6);
            map.setView([lat, lng], 16);

            if(marker) map.removeLayer(marker);
            if(circle) map.removeLayer(circle);

            marker = L.marker([lat, lng]).addTo(map);
            circle = L.circle([lat, lng], {color:'red', fillColor:'#f03', fillOpacity:0.3, radius:50}).addTo(map);
        });
    }
}

function refreshMap(){ loadLocation(); }

function paymentChanged(){
    const payment = document.getElementById("paymentSelect").value;
    const nameInput = document.getElementById("nameInput");

    if(payment === "Bank Transfer"){
        if(nameInput.value === "Guest" || nameInput.value.trim() === ""){ nameInput.value = ""; }
        nameInput.placeholder = "Enter your real name for Bank Transfer";
    } else {
        if(nameInput.value.trim() === ""){ nameInput.value = "Guest"; }
        nameInput.placeholder = "Guest";
    }
}

function validateForm(){
    const payment = document.getElementById("paymentSelect").value;
    const nameInput = document.getElementById("nameInput");

    if(payment === "Bank Transfer" && (nameInput.value.trim() === "" || nameInput.value === "Guest")){
        nameInput.placeholder = "Please enter your real name for Bank Transfer";
        nameInput.focus();
        return false;
    }
    return true;
}

loadLocation();
</script>

<?php include 'includes/footer.php'; ?>