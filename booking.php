<?php
ob_start();
session_start();
include 'config/db.php';
include 'includes/header.php';

$orders = [];
$phone = '';

if(isset($_POST['search'])){
    $phone = trim($_POST['phone']);
} elseif(isset($_GET['phone'])){
    $phone = trim($_GET['phone']);
}

if($phone !== ''){
    $stmt = $conn->prepare("
        SELECT o.*, oi.id as item_id, oi.product_name, oi.qty, oi.price
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.phone = :phone 
          AND o.status NOT IN ('Cancel','Done')
        ORDER BY o.id ASC, oi.id ASC
    ");
    $stmt->execute([':phone' => $phone]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ordersMap = [];
    foreach($rows as $row){
        $orderId = $row['id'];
        if(!isset($ordersMap[$orderId])){
            $ordersMap[$orderId] = $row;
            $ordersMap[$orderId]['items'] = [];
        }
        if($row['item_id']){
            $ordersMap[$orderId]['items'][] = [
                'product_name' => $row['product_name'],
                'qty' => $row['qty'],
                'price' => $row['price']
            ];
        }
    }
    $orders = array_values($ordersMap);
}
?>

<style>
body { background: #f7efe5; font-family: 'Poppins', sans-serif; }
.track-wrapper { max-width: 900px; margin: 70px auto; background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
.track-wrapper h2 { text-align: center; margin-bottom: 25px; color: #4b2e2e; }
.search-area { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; flex-wrap: nowrap; }
.search-area input, .search-area button, .search-area .history-btn { height: 45px; font-size: 14px; border-radius: 8px; text-align: center; box-sizing: border-box; }
.search-area input { flex: 2; border: 1px solid #ccc; padding: 0 10px; }
.search-area button, .search-area .history-btn { flex: 1; border: none; }
.search-area button { background: #4b2e2e; color: #fff; font-weight: 600; cursor: pointer; }
.search-area button:hover { opacity: 0.85; }
.history-btn { display: inline-block; background: gray; color: white; text-decoration: none; font-weight: 500; line-height: 45px; text-align: center; }
.history-btn:hover { opacity: 0.85; }

/* Booking card and status colors */
.booking-card { background: #fdf8f4; padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 6px solid #4b2e2e; display: none; }
.booking-card.pending { border-left-color: #FFA500; }    /* orange */
.booking-card.success { border-left-color: #28a745; }    /* green */
.booking-card.delivery { border-left-color: #007bff; }   /* blue */
.booking-card.pickup { border-left-color: #6f42c1; }     /* purple */
.booking-card.cancel { border-left-color: #dc3545; }     /* red */

.booking-card h4 { margin: 0 0 10px 0; display: flex; justify-content: space-between; cursor: pointer; }
.booking-card p { margin: 4px 0; }
.booking-card .status.pending { color: #FFA500; }
.booking-card .status.success { color: #28a745; }
.booking-card .status.delivery { color: #007bff; }
.booking-card .status.pickup { color: #6f42c1; }
.booking-card .status.cancel { color: #dc3545; }

.no-result { text-align: center; color: red; margin-top: 20px; }
.order-items { display: none; margin-top: 10px; border-top: 1px solid #ccc; padding-top: 10px; }
.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.table-responsive table { width: 100%; min-width: 600px; border-collapse: collapse; }
.table-responsive th, .table-responsive td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
.toggle-btn { font-size: 14px; color: #4b2e2e; font-weight: 500; }

.pagination { text-align: right; margin-top: 20px; }
.pagination button { margin-left: 5px; padding: 6px 12px; border: none; border-radius: 5px; background: #4b2e2e; color: #fff; cursor: pointer; font-size: 16px; }
.pagination button:disabled { opacity: 0.5; cursor: default; }

@media screen and (max-width: 430px){
    .track-wrapper { padding: 20px; margin: 20px 10px; }
    .search-area { flex-direction: column; gap: 10px; }
    .search-area input, .search-area button, .search-area { width: 100%; padding: 10px; }
    .history-btn { width: 100%; }
    .booking-card { padding: 15px; border-left-width: 4px; }
    .booking-card h4 { font-size: 16px; flex-direction: column; gap: 5px; }
    .booking-card p { font-size: 14px; }
    .toggle-btn { font-size: 13px; }
    .table-responsive table { min-width: 500px; }
}
</style>

<div class="track-wrapper">
    <h2>VIEW MY BOOKING</h2>

    <form method="POST">
        <div class="search-area">
            <input type="text" name="phone" placeholder="Enter Your Phone Number" value="<?= htmlspecialchars($phone) ?>" required>
            <button type="submit" name="search">Search</button>
            <a class="history-btn" href="history.php?phone=<?= urlencode($phone) ?>" <?= $phone === '' ? 'style="pointer-events:none;opacity:0.5;"' : '' ?>>View History</a>
        </div>
    </form>

    <?php if($phone !== ''): ?>
        <?php if(empty($orders)): ?>
            <div class="no-result">No active orders found with this phone number.</div>
        <?php else: ?>
            <?php foreach($orders as $order): ?>
                <?php 
                    $statusClass = '';
                    $statusLower = strtolower($order['status']);
                    if($statusLower === 'pending') $statusClass = 'pending';
                    elseif($statusLower === 'success') $statusClass = 'success';
                    elseif($statusLower === 'delivery') $statusClass = 'delivery';
                    elseif($statusLower === 'pick up') $statusClass = 'pickup';
                    elseif($statusLower === 'cancel') $statusClass = 'cancel';
                ?>
                <div class="booking-card <?= $statusClass ?>">
                    <h4 onclick="toggleItems(<?= $order['id'] ?>)">
                        Order #<?= $order['id'] ?>
                        <span class="toggle-btn" id="toggle-<?= $order['id'] ?>">[Show Items]</span>
                    </h4>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                    <p><strong>Total:</strong> $<?= number_format($order['total'],2) ?></p>
                    <p><strong>Status:</strong> <span class="status <?= $statusClass ?>"><?= htmlspecialchars($order['status']) ?></span></p>
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

            <div class="pagination">
                <button id="prevBtn">&lt;</button>
                <button id="nextBtn">&gt;</button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
const cards = document.querySelectorAll('.booking-card');
const cardsPerPage = 5;
let currentPageIndex = 0;

function showPage(index){
    cards.forEach((card,i) => {
        card.style.display = (i >= index && i < index + cardsPerPage) ? 'block' : 'none';
    });
    document.getElementById('prevBtn').disabled = (index === 0);
    document.getElementById('nextBtn').disabled = (index + cardsPerPage >= cards.length);
}

showPage(0);

document.getElementById('prevBtn').addEventListener('click', () => {
    if(currentPageIndex >= cardsPerPage){
        currentPageIndex -= cardsPerPage;
        showPage(currentPageIndex);
    }
});

document.getElementById('nextBtn').addEventListener('click', () => {
    if(currentPageIndex + cardsPerPage < cards.length){
        currentPageIndex += cardsPerPage;
        showPage(currentPageIndex);
    }
});

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