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

.search-area input,
.search-area button,
.history-btn {
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

.search-area button {
    flex: 1;
    border: none;
    background: #9C5F78;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
}

.search-area button:hover {
    opacity: 0.85;
}

.history-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #D8A8B8;
    color: white;
    font-weight: 500;
    text-decoration: none;
    border-radius: 8px;
}

.history-btn.disabled {
    background: #e0c4cf;
    cursor: not-allowed;
    opacity: 0.7;
}

/* Booking card */
.booking-card {
    background: #fff0f5;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    border-left: 6px solid #9C5F78;
    box-shadow: 0 4px 10px rgba(201,138,165,0.2);
    display: none;
}

.booking-card h4 {
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    cursor: pointer;
    color: #9C5F78;
}

.order-items {
    display: none;
    margin-top: 10px;
}

.table-responsive {
    overflow-x: auto;
}

.table-responsive table {
    width: 100%;
    min-width: 600px;
    border-collapse: collapse;
}

.table-responsive th,
.table-responsive td {
    padding: 8px;
    text-align: center;
    border-bottom: 1px solid #F3C6D3;
}

.no-result {
    text-align: center;
    color: red;
    margin-top: 20px;
}

/* Pagination */
.pagination {
    text-align: center;
    margin-top: 20px;
}

.pagination button {
    padding: 6px 12px;
    border: none;
    border-radius: 5px;
    background: #9C5F78;
    color: white;
    cursor: pointer;
}

/* ✅ MOBILE FIX 430px */
@media (max-width: 430px){

    .track-wrapper {
        padding: 15px;
        margin: 15px 8px;
    }

    .search-area {
        flex-direction: column;
    }

    .search-area input,
    .search-area button,
    .history-btn {
        width: 100%;
        height: 42px;
        font-size: 13px;
    }

    .booking-card {
        padding: 12px;
    }

    .booking-card h4 {
        flex-direction: column;
        align-items: flex-start;
        font-size: 15px;
    }

    .table-responsive table {
        min-width: 480px;
        font-size: 12px;
    }
}
</style>

<div class="track-wrapper">
    <h2>VIEW MY BOOKING</h2>

    <form method="POST">
        <div class="search-area">
            <input type="text" name="phone" placeholder="Enter Your Phone Number"
                   value="<?= htmlspecialchars($phone) ?>" required>

            <button type="submit" name="search">Search</button>

            <?php if($phone !== ''): ?>
                <a class="history-btn" href="history.php?phone=<?= urlencode($phone) ?>">
                    View History
                </a>
            <?php else: ?>
                <span class="history-btn disabled">View History</span>
            <?php endif; ?>
        </div>
    </form>

    <?php if($phone !== ''): ?>
        <?php if(empty($orders)): ?>
            <div class="no-result">No active orders found.</div>
        <?php else: ?>

            <?php foreach($orders as $order): ?>
                <div class="booking-card">
                    <h4 onclick="toggleItems(<?= $order['id'] ?>)">
                        Order #<?= $order['id'] ?>
                        <span id="toggle-<?= $order['id'] ?>">[Show Items]</span>
                    </h4>

                    <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
                    <p><strong>Total:</strong> $<?= number_format($order['total'],2) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>

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
                                                <td>$<?= number_format($item['price']*$item['qty'],2) ?></td>
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
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

if(cards.length > 0 && prevBtn && nextBtn){

    const cardsPerPage = 5;
    let currentPageIndex = 0;

    function showPage(index){
        cards.forEach((card,i)=>{
            card.style.display =
                (i >= index && i < index + cardsPerPage) ? 'block' : 'none';
        });

        prevBtn.disabled = (index === 0);
        nextBtn.disabled = (index + cardsPerPage >= cards.length);
    }

    showPage(0);

    prevBtn.addEventListener('click', ()=>{
        if(currentPageIndex >= cardsPerPage){
            currentPageIndex -= cardsPerPage;
            showPage(currentPageIndex);
        }
    });

    nextBtn.addEventListener('click', ()=>{
        if(currentPageIndex + cardsPerPage < cards.length){
            currentPageIndex += cardsPerPage;
            showPage(currentPageIndex);
        }
    });
}

function toggleItems(orderId){
    const div = document.getElementById('items-'+orderId);
    const btn = document.getElementById('toggle-'+orderId);

    if(div.style.display === 'block'){
        div.style.display = 'none';
        btn.innerText = '[Show Items]';
    }else{
        div.style.display = 'block';
        btn.innerText = '[Hide Items]';
    }
}
</script>

<?php include 'includes/footer.php'; ?>