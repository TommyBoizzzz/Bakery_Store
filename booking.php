<?php
include 'config/db.php';
include 'includes/header.php';

$orders = [];

if(isset($_POST['search'])){
    $phone = trim($_POST['phone']);

    // Get orders by phone, exclude canceled and pick up orders
    $stmt = $conn->prepare("SELECT * FROM orders WHERE phone = ? AND status NOT IN ('cancel', 'pick up') ORDER BY id DESC");
    $stmt->bind_param("s",$phone);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
        $order_id = $row['id'];

        // Get order items
        $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();

        $items = [];
        while($item = $items_result->fetch_assoc()){
            $items[] = $item;
        }

        $row['items'] = $items;
        $orders[] = $row;
    }
}

?>

<style>
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

    .search-area {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-bottom: 30px;
    }

    .search-area input {
        padding: 12px;
        width: 300px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    .search-area button {
        padding: 12px 25px;
        background: #4b2e2e;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }

    .search-area button:hover {
        opacity: 0.85;
    }

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

    .booking-card p {
        margin: 4px 0;
    }

    .status {
        font-weight: bold;
    }

    .no-result {
        text-align: center;
        color: red;
        margin-top: 20px;
    }

    /* Order items */
    .order-items {
        display: none;
        margin-top: 10px;
        border-top: 1px solid #ccc;
        padding-top: 10px;
    }

    .order-items table {
        width: 100%;
        border-collapse: collapse;
    }

    .order-items th, .order-items td {
        text-align: left;
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }

    .toggle-btn {
        font-size: 14px;
        color: #4b2e2e;
        font-weight: 500;
    }

    /* Base styles remain the same */

    /* Responsive for small screens (like 430px width) */
    @media screen and (max-width: 450px) {
        .track-wrapper {
            padding: 20px;
            margin: 20px 10px;
        }

        .search-area {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .search-area input {
            width: 100%;
            font-size: 14px;
            padding: 10px;
        }

        .search-area button {
            width: 100%;
            padding: 10px;
            font-size: 14px;
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

        .order-items table, 
        .order-items th, 
        .order-items td {
            font-size: 12px;
        }

        .order-items table {
            display: block;
            overflow-x: auto;
            width: 100%;
        }

        .order-items th, .order-items td {
            white-space: nowrap;
        }

        .toggle-btn {
            font-size: 13px;
        }
    }

</style>

<div class="track-wrapper">

    <h2>VIEW MY BOOKING</h2>

    <form method="POST">
        <div class="search-area">
            <input type="text" name="phone" placeholder="Enter Your Phone Number" 
                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
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
                    <h4 onclick="toggleItems(<?php echo $order['id']; ?>)">
                        Order #<?php echo $order['id']; ?>
                        <span class="toggle-btn" id="toggle-<?php echo $order['id']; ?>">[Show Items]</span>
                    </h4>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Total:</strong> $<?php echo number_format($order['total'],2); ?></p>
                    <p><strong>Status:</strong> <span class="status"><?php echo htmlspecialchars($order['status']); ?></span></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>

                    <?php if(!empty($order['items'])): ?>
                        <div class="order-items" id="items-<?php echo $order['id']; ?>">
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
            <td><?= htmlspecialchars($item['product_name']); ?></td>
            <td><?= intval($item['qty']); ?></td>
            <td>$<?= number_format($item['price'],2); ?></td>
            <td>$<?= number_format($item['price'] * $item['qty'],2); ?></td>
        </tr>
    <?php endforeach; ?>
</tbody>

                            </table>
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
