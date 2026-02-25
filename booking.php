<?php
ob_start();
session_start();
include 'config/db.php';
include 'includes/header.php';

$orders = [];
$historyOrders = [];
$phone = '';

// Check if phone is from POST (form) or GET (from history back link)
if(isset($_POST['search'])){
    $phone = trim($_POST['phone']);
} elseif(isset($_GET['phone'])){
    $phone = trim($_GET['phone']);
}

// If a phone is provided, perform search
if($phone !== ''){
    // ================= ACTIVE ORDERS =================
    $stmt = $conn->prepare("SELECT * FROM orders WHERE phone = :phone AND status NOT IN ('Cancel', 'Pick Up') ORDER BY id DESC");
    $stmt->execute([':phone' => $phone]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get items for each order
    foreach($orders as &$order){
        $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $items_stmt->execute([':order_id' => $order['id']]);
        $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ================= HISTORY ORDERS =================
    $history_stmt = $conn->prepare("SELECT * FROM orders WHERE phone = :phone AND status IN ('Cancel', 'Pick Up') ORDER BY id DESC");
    $history_stmt->execute([':phone' => $phone]);
    $historyOrders = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
    /* ================= BODY & WRAPPER ================= */
    body { 
        background: #f7efe5; 
        font-family: 'Poppins', sans-serif; 
    }

    .track-wrapper { 
        max-width: 900px; 
        margin: 70px auto; 
        background: white; 
        padding: 40px; 
        border-radius: 16px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
    }

    .track-wrapper h2 { 
        text-align: center; 
        margin-bottom: 25px; 
        color: #4b2e2e; 
    }

    /* ================= SEARCH AREA ================= */
    .search-area { 
        display: flex; 
        gap: 10px; 
        justify-content: center; 
        margin-bottom: 20px; 
        flex-wrap: nowrap; 
    }

    .search-area input, 
    .search-area button, 
    .search-area .history-btn { 
        height: 45px;
        font-size: 14px;
        border-radius: 8px;
        text-align: center;
        box-sizing: border-box;
    }

    .search-area input { 
        flex: 2;              
        border: 1px solid #ccc;
        padding: 0 10px;
    }

    .search-area button, 
    .search-area .history-btn { 
        flex: 1;              
        border: none;
    }

    .search-area button { 
        background: #4b2e2e; 
        color: #fff; 
        font-weight: 600; 
        cursor: pointer;
    }

    .search-area button:hover { opacity: 0.85; }

    .history-btn { 
        display: inline-block;
        background: gray; 
        color: white; 
        text-decoration: none; 
        font-weight: 500; 
        line-height: 45px;
        text-align: center;
    }

    .history-btn:hover { opacity: 0.85; }

    /* ================= BOOKING CARD ================= */
    .booking-card { 
        background: #fdf8f4; 
        padding: 20px; 
        border-radius: 12px; 
        margin-bottom: 20px; 
        border-left: 6px solid #4b2e2e; 
    }

    .booking-card h4 { 
        margin: 0 0 10px 0; 
        display: flex; 
        justify-content: space-between; 
        cursor: pointer; 
    }

    .booking-card p { margin: 4px 0; }

    .status { font-weight: bold; }

    .no-result { text-align: center; color: red; margin-top: 20px; }

    /* ================= ORDER ITEMS ================= */
    .order-items { display: none; margin-top: 10px; border-top: 1px solid #ccc; padding-top: 10px; }

    /* Scrollable table wrapper */
    .table-responsive {
        overflow-x: auto; 
        -webkit-overflow-scrolling: touch; /* smooth scroll on mobile */
    }

    .table-responsive table {
        width: 100%;
        min-width: 600px; /* ensures table columns don’t shrink too much */
        border-collapse: collapse;
    }

    .table-responsive th, 
    .table-responsive td {
        text-align: left;
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }

    .toggle-btn { 
        font-size: 14px; 
        color: #4b2e2e; 
        font-weight: 500; 
    }

    /* ================= RESPONSIVE ================= */
    @media screen and (max-width: 430px) {
        .track-wrapper { 
            padding: 20px; 
            margin: 20px 10px; 
        }
        .search-area { 
            flex-direction: column; 
            gap: 10px; 
        }
        .search-area input, .search-area button, .search-area { 
            width: 100%; 
            padding: 10px; 
        }
        .history-btn { width: 100%; }
        .booking-card { padding: 15px; border-left-width: 4px; }
        .booking-card h4 { font-size: 16px; flex-direction: column; gap: 5px; }
        .booking-card p { font-size: 14px; }
        .toggle-btn { font-size: 13px; }
        .table-responsive table { min-width: 500px; } /* smaller min width for small screens */
    }
</style>

<div class="track-wrapper">
    <h2>VIEW MY BOOKING</h2>

    <!-- SEARCH FORM -->
    <form method="POST">
        <div class="search-area">
            <input type="text" name="phone" placeholder="Enter Your Phone Number" value="<?= htmlspecialchars($phone) ?>" required>
            <button type="submit" name="search">Search</button>
            <a class="history-btn" 
               href="history.php?phone=<?= urlencode($phone) ?>" 
               <?= $phone === '' ? 'style="pointer-events:none;opacity:0.5;"' : '' ?>>
               View History
            </a>
        </div>
    </form>

    <!-- ACTIVE ORDERS -->
    <?php if($phone !== ''): ?>
        <?php if(empty($orders)): ?>
            <div class="no-result">No active orders found with this phone number.</div>
        <?php else: ?>
            <?php foreach($orders as $order): ?>
                <div class="booking-card">
                    <h4 onclick="toggleItems(<?= $order['id'] ?>)">
                        Order #<?= $order['id'] ?>
                        <span class="toggle-btn" id="toggle-<?= $order['id'] ?>">[Show Items]</span>
                    </h4>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                    <p><strong>Total:</strong> $<?= number_format($order['total'],2) ?></p>
                    <p><strong>Status:</strong> <span class="status"><?= htmlspecialchars($order['status']) ?></span></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($order['created_at']) ?></p>

                    <?php if(!empty($order['items'])): ?>
                        <div class="order-items" id="items-<?= $order['id'] ?>">
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($order['items'] as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                <td><?= intval($item['qty']) ?></td>
                                                <td>$<?= number_format($item['price'],2) ?></td>
                                                <td>$<?= number_format($item['price'] * $item['qty'],2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function toggleItems(orderId){
    const div = document.getElementById('items-' + orderId);
    const btn = document.getElementById('toggle-' + orderId);
    if(div.style.display === 'none' || div.style.display === ''){
        div.style.display = 'block';
        btn.textContent = '[Hide Items]';
    } else {
        div.style.display = 'none';
        btn.textContent = '[Show Items]';
    }
}
</script>

<?php include 'includes/footer.php'; ?>