<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

$historyOrders = [];
$phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';

if($phone !== ''){
    $history_stmt = $conn->prepare("SELECT * FROM orders WHERE phone = :phone AND status IN ('Cancel', 'Pick Up') ORDER BY id DESC");
    $history_stmt->execute([':phone' => $phone]);
    $historyOrders = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($historyOrders as &$order){
        $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $items_stmt->execute([':order_id' => $order['id']]);
        $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<style>
body { background: #f7efe5; font-family: 'Poppins', sans-serif; }
.track-wrapper { max-width: 900px; margin: 70px auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }

.header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; }
.header-row h2 { margin: 0; color: #4b2e2e; }
.back-btn { padding: 10px 20px; background: #4b2e2e; color: white; border-radius: 8px; text-decoration: none; font-weight: 500; }
.back-btn:hover { opacity: 0.85; }

.booking-card { background: #fdf8f4; padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 6px solid gray; }
.booking-card h4 { margin: 0 0 10px 0; display: flex; justify-content: space-between; cursor: pointer; }
.booking-card p { margin: 4px 0; }
.order-items { display: none; margin-top: 10px; border-top: 1px solid #ccc; padding-top: 10px; }
.order-items table { width: 100%; border-collapse: collapse; }
.order-items th, .order-items td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
.toggle-btn { font-size: 14px; color: #4b2e2e; font-weight: 500; }

/* Responsive */
@media screen and (max-width: 450px) {
    .track-wrapper { padding: 20px; margin: 20px 10px; }
    .header-row { flex-direction: column; gap: 10px; align-items: flex-start; }
    .back-btn { width: 100%; text-align: center; }
    .booking-card { padding: 15px; border-left-width: 4px; }
    .booking-card h4 { font-size: 16px; flex-direction: column; gap: 5px; }
    .booking-card p { font-size: 14px; }
    .order-items table, .order-items th, .order-items td { font-size: 12px; display: block; overflow-x: auto; width: 100%; white-space: nowrap; }
    .toggle-btn { font-size: 13px; }
}
</style>

<div class="track-wrapper">
    <div class="header-row">
        <h2>Order History</h2>
        <a class="back-btn" href="booking.php?phone=<?= urlencode($phone) ?>">← Back to Booking</a>
    </div>

    <?php if(empty($historyOrders)): ?>
        <div class="no-result">No history found for this phone number.</div>
    <?php else: ?>
        <?php foreach($historyOrders as $order): ?>
            <div class="booking-card">
                <h4 onclick="toggleItems(<?= $order['id'] ?>)">
                    Order #<?= $order['id'] ?> (<?= htmlspecialchars($order['status']) ?>)
                    <span class="toggle-btn" id="toggle-<?= $order['id'] ?>">[Show Items]</span>
                </h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                <p><strong>Total:</strong> $<?= number_format($order['total'],2) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($order['created_at']) ?></p>

                <?php if(!empty($order['items'])): ?>
                    <div class="order-items" id="items-<?= $order['id'] ?>">
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
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
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