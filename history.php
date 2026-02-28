<?php
ob_start();
session_start();
include 'config/db.php';
include 'includes/header.php';

$historyOrders = [];
$phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';

if($phone !== ''){
    // Fetch history orders (Cancel, Done)
    $stmt = $conn->prepare("
        SELECT o.*, oi.id as item_id, oi.product_name, oi.qty, oi.price
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.phone = :phone AND o.status IN ('Cancel','Done')
        ORDER BY o.id ASC, oi.id ASC
    ");
    $stmt->execute([':phone' => $phone]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ordersMap = [];
    foreach($rows as $row){
        $orderId = $row['id'];
        if(!isset($ordersMap[$orderId])) {
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
    $historyOrders = array_values($ordersMap);
}
?>

<style>
body {
    background: #fdf4f8; /* soft blush background */
    font-family: 'Poppins', sans-serif;
}

.track-wrapper {
    max-width: 900px;
    margin: 70px auto;
    background: #fff;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(201,138,165,0.25); /* pink shadow */
}

.header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.header-row h2 {
    margin: 0;
    color: #9c5f78; /* elegant rose */
}

.back-btn {
    padding: 10px 20px;
    background: #c98aa5;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
}

.back-btn:hover {
    background: #b76e8a;
}

/* Booking card */
.booking-card {
    background: #fff0f5; /* light pink card */
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    border-left: 6px solid #d8a8b8;
    display: none;
}

/* Status border colors (kept clear for meaning) */
.booking-card.pending { border-left-color: #f4a261; }
.booking-card.success { border-left-color: #e76f51; }
.booking-card.delivery { border-left-color: #ff8fab; }
.booking-card.done { border-left-color: #b76e8a; }
.booking-card.cancel { border-left-color: #d00000; }

.booking-card h4 {
    margin: 0 0 10px 0;
    display: flex;
    justify-content: space-between;
    cursor: pointer;
    color: #9c5f78;
}

.booking-card p {
    margin: 4px 0;
}

/* Status text colors */
.booking-card .status.pending { color: #f4a261; }
.booking-card .status.success { color: #e76f51; }
.booking-card .status.delivery { color: #ff8fab; }
.booking-card .status.done { color: #b76e8a; }
.booking-card .status.cancel { color: #d00000; }

.no-result {
    text-align: center;
    color: #b76e8a;
    margin-top: 20px;
}

.order-items {
    display: none;
    margin-top: 10px;
    border-top: 1px solid #f3c6d3;
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

.table-responsive th,
.table-responsive td {
    text-align: left;
    padding: 8px;
    border-bottom: 1px solid #f3c6d3;
}

.toggle-btn {
    font-size: 14px;
    color: #c98aa5;
    font-weight: 500;
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
    background: #c98aa5;
    color: #fff;
    cursor: pointer;
    font-size: 16px;
}

.pagination button:hover {
    background: #b76e8a;
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: default;
}

/* Mobile */
@media screen and (max-width: 450px){

    .track-wrapper {
        padding: 20px;
        margin: 20px 10px;
    }

    .header-row {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }

    .back-btn {
        width: 100%;
        text-align: center;
    }

    .booking-card {
        padding: 15px;
        border-left-width: 4px;
    }

    .booking-card h4 {
        font-size: 16px;
        flex-direction: column;
        gap: 5px;
    }

    .booking-card p {
        font-size: 14px;
    }

    .toggle-btn {
        font-size: 13px;
    }

    .table-responsive table {
        min-width: 500px;
    }
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
            <?php 
                // Use lowercase class names for CSS
                $statusClass = strtolower($order['status']);
            ?>
            <div class="booking-card <?= $statusClass ?>">
                <h4 onclick="toggleItems(<?= $order['id'] ?>)">
                    Order #<?= $order['id'] ?> 
                    <span class="status <?= $statusClass ?>"><?= htmlspecialchars($order['status']) ?></span>
                    <span class="toggle-btn" id="toggle-<?= $order['id'] ?>">[Show Items]</span>
                </h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                <p><strong>Total:</strong> $<?= number_format($order['total'],2) ?></p>
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

        <!-- Pagination -->
        <div class="pagination">
            <button id="prevBtn">&lt;</button>
            <button id="nextBtn">&gt;</button>
        </div>
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