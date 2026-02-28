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
body {
    background: #FDF4F8;
    font-family: 'Poppins', sans-serif;
}

.track-wrapper {
    max-width: 1100px;
    margin: 50px auto;
    padding: 40px;
    background: #FFF0F5;
    border-radius: 16px;
    box-shadow: 0 6px 15px rgba(201,138,165,0.25);
}

.track-wrapper h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #9C5F78;
}

/* Search area */
.search-area {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.search-area input, .search-area button, .search-area .history-btn {
    height: 45px;
    font-size: 14px;
    border-radius: 8px;
    text-align: center;
    box-sizing: border-box;
}

.search-area input {
    flex: 2;
    border: 1px solid #D8A8B8;
    padding: 0 10px;
}

.search-area button, .search-area .history-btn {
    flex: 1;
    border: none;
}

.search-area button {
    background: #9C5F78;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
}

.search-area button:hover {
    opacity: 0.85;
}

.history-btn {
    display: inline-block;
    background: #D8A8B8;
    color: white;
    font-weight: 500;
    line-height: 45px;
    text-align: center;
    text-decoration: none;
}

.history-btn:hover {
    opacity: 0.85;
}

/* Booking card styling */
.booking-card {
    background: #fff0f5;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    border-left: 6px solid #9C5F78;
    box-shadow: 0 4px 10px rgba(201,138,165,0.2);
    display: none; /* hidden initially */
    transition: transform 0.2s, box-shadow 0.2s;
}

.booking-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(201,138,165,0.3);
}

.booking-card h4 {
    margin: 0 0 10px 0;
    display: flex;
    justify-content: space-between;
    cursor: pointer;
    font-weight: 600;
    color: #9C5F78;
}

.booking-card p {
    margin: 4px 0;
}

/* Status colors */
.booking-card.pending { border-left-color: #FFA500; }
.booking-card.success { border-left-color: #28a745; }
.booking-card.delivery { border-left-color: #007bff; }
.booking-card.pickup { border-left-color: #6f42c1; }
.booking-card.cancel { border-left-color: #dc3545; }

.booking-card .status.pending { color: #FFA500; }
.booking-card .status.success { color: #28a745; }
.booking-card .status.delivery { color: #007bff; }
.booking-card .status.pickup { color: #6f42c1; }
.booking-card .status.cancel { color: #dc3545; }

.order-items {
    display: none;
    margin-top: 10px;
    border-top: 1px solid #F3C6D3;
    padding-top: 10px;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-responsive table {
    width: 100%;
    min-width: 600px;
    border-collapse: collapse;
}

.table-responsive th {
    background: #C98AA5;
    color: #fff;
    padding: 10px;
    text-align: center;
}

.table-responsive td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #F3C6D3;
}

.toggle-btn {
    font-size: 14px;
    color: #9C5F78;
    font-weight: 500;
    cursor: pointer;
}

.no-result {
    text-align: center;
    color: #dc3545;
    margin-top: 20px;
}

/* Pagination */
.pagination {
    text-align: right;
    margin-top: 20px;
}

.pagination button {
    margin-left: 5px;
    padding: 6px 12px;
    border: none;
    border-radius: 5px;
    background: #9C5F78;
    color: #fff;
    cursor: pointer;
    font-size: 16px;
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: default;
}

/* Responsive */
@media screen and (max-width: 450px){
    .track-wrapper { padding: 20px; margin: 20px 10px; }
    .search-area { flex-direction: column; gap: 10px; }
    .search-area input, .search-area button, .search-area { width: 100%; }
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