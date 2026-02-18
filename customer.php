<?php
session_start();
include 'config/db.php';

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

// ==================== TELEGRAM FUNCTION ====================
$botToken = "8536317909:AAEMKbGDVib9yQYIEDOFuMdHU0YVpWqD1UE"; // bot token
$groupID  = -1003709157668; // supergroup ID
$topicID  = 2;               // MY ORDER topic ID

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
        die("Telegram message failed! Check bot token, group ID, and bot permissions.");
    }
}

// ==================== PLACE ORDER ====================
if(isset($_POST['place_order'])){

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'] ?? '';
    $location = $_POST['location'];

    if(empty($name) || empty($phone) || empty($payment_method) || empty($location)){
        die("All fields are required.");
    }

    // INSERT ORDER
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (name, phone, payment_method, location, total, created_at, status) 
        VALUES (?,?,?,?,?,NOW(),'Pending')
    ");
    if(!$stmt) die("Prepare failed: ".$conn->error);

    $stmt->bind_param("ssssd", $name, $phone, $payment_method, $location, $total);
    if(!$stmt->execute()) die("Execute failed: ".$stmt->error);

    $order_id = $stmt->insert_id;

    // INSERT ORDER ITEMS
    $item_stmt = $conn->prepare("
        INSERT INTO order_items 
        (order_id, product_id, product_name, price, qty, subtotal) 
        VALUES (?,?,?,?,?,?)
    ");
    if(!$item_stmt) die("Prepare failed for items: ".$conn->error);

    foreach($cart as $item){
        $product_id = $item['id'];
        $product_name = $item['name'];
        $price = $item['price'];
        $qty = $item['qty'];
        $subtotal = $price * $qty;

        $item_stmt->bind_param("iisdid", $order_id, $product_id, $product_name, $price, $qty, $subtotal);
        if(!$item_stmt->execute()) die("Execute failed for items: ".$item_stmt->error);
    }

    // SEND TELEGRAM NOTIFICATION
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

    // CLEAR CART
    unset($_SESSION['cart']);

    echo "<script>alert('Order placed successfully!'); window.location='products.php';</script>";
    exit;
}

include 'includes/header.php';
?>

<style>
body{background:#f7efe5;font-family:'Poppins',sans-serif;}
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
    color:#4b2e2e;
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
    padding:12px 25px;
    border-radius:20px;
    color:white;
    cursor:pointer;
    width:100%;
    font-size:16px;
    font-weight:600;
}
.btn-checkout:hover{opacity:0.85;}
</style>

<!-- Leaflet Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
            <option value="Cash">CASH</option>
            <option value="Bank Transfer" disabled>BANK</option>
        </select>
    </div>

<div class="form-group">
    <label>Location</label>
    <input type="text" 
           id="location" 
           name="location" 
           placeholder="Your current location will be detected" 
           required readonly>
    <div id="map"></div>
</div>

<script>
// Initialize map with default center
let map = L.map('map').setView([11.5564, 104.9282], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

let marker, circle;

// Try to get user location
if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition(function(position){
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        // Update input
        document.getElementById('location').value = lat.toFixed(6) + "," + lng.toFixed(6);

        // Set map view
        map.setView([lat, lng], 16);

        // Add marker and circle
        marker = L.marker([lat, lng]).addTo(map);
        circle = L.circle([lat, lng], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.3,
            radius: 50
        }).addTo(map);

    }, function(error){
        alert("Unable to retrieve your location. Please allow location access.");
    });
} else {
    alert("Geolocation is not supported by your browser.");
}
</script>

<?php include 'includes/footer.php'; ?>
