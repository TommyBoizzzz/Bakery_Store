<?php
session_start();
include 'config/db.php'; // PDO
include 'includes/header.php';

// ==================== CART LOGIC ====================

// Add to cart
if(isset($_POST['add_to_cart'])){
    $id = intval($_POST['product_id']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=:id");
    $stmt->execute(['id'=>$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if($product){
        if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if(isset($_SESSION['cart'][$id])){
            $_SESSION['cart'][$id]['qty'] += 1;
        } else {
            $_SESSION['cart'][$id] = [
                'id'=>$product['id'],
                'name'=>$product['name'],
                'price'=>$product['price'],
                'image'=>$product['image'],
                'qty'=>1
            ];
        }
    }
    header("Location: cart.php");
    exit;
}

// Remove item
if(isset($_GET['remove'])){
    $remove_id = intval($_GET['remove']);
    if(isset($_SESSION['cart'][$remove_id])) unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit;
}

// Checkout (update qty + go to customer page)
if(isset($_POST['checkout'])){
    if(isset($_POST['qty'])){
        foreach($_POST['qty'] as $id=>$qty){
            $_SESSION['cart'][$id]['qty'] = max(1,intval($qty));
        }
    }
    header("Location: customer.php");
    exit;
}

// Calculate total
$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach($cart as $item) $total += $item['price']*$item['qty'];
?>

<style>
body{background:#f7efe5;font-family:'Poppins',sans-serif;}
.cart-container{max-width:1100px;margin:50px auto;padding:0 15px;}
.cart-container h2{text-align:center;margin-bottom:25px;}
.cart-table{width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;}
.cart-table th{background:#4b2e2e;color:white;padding:14px;text-align:center;}
.cart-table td{padding:14px;text-align:center;border-bottom:1px solid #eee;}
.cart-img{width:90px;height:70px;object-fit:cover;border-radius:8px;}
.qty-input{width:60px;padding:6px;text-align:center;}
.remove-btn{background:red;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;cursor:pointer;}
.cart-footer{margin-top:25px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;}
.total{font-size:22px;font-weight:bold;margin-bottom:10px;}
.btn-checkout{background:#4b2e2e;border:none;padding:10px 25px;border-radius:20px;color:white;cursor:pointer;text-decoration:none;display:inline-block;}
.btn-checkout:hover,.remove-btn:hover{opacity:0.8;}
.update-btn{background:#8b5e3c;border:none;padding:8px 20px;border-radius:20px;color:white;cursor:pointer;}
.update-btn:hover{opacity:0.8;}
@media screen and (max-width: 450px){
    .cart-container{padding:0 10px;margin:20px auto;}
    .cart-table{display:block;overflow-x:auto;font-size:12px;}
    .cart-table th,.cart-table td{padding:8px;white-space:nowrap;}
    .cart-img{width:70px;height:50px;}
    .qty-input{width:50px;padding:4px;font-size:12px;}
    .cart-footer{flex-direction:column;align-items:flex-start;gap:10px;}
    .total{font-size:18px;}
    .btn-checkout{width:100%;padding:12px;text-align:center;font-size:14px;}
}
</style>

<div class="cart-container">
<h2>Your Cart</h2>

<?php if(empty($cart)): ?>
<p style="text-align:center;">Cart is empty. <a href="products.php">Go Shopping</a></p>
<?php else: ?>
<form method="POST" action="">
<table class="cart-table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Product</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($cart as $id=>$item):
            $subtotal = $item['price']*$item['qty'];
        ?>
        <tr>
            <td><img src="assets/images/<?= htmlspecialchars($item['image']) ?>" class="cart-img"></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price'],2) ?></td>
            <td><input type="number" name="qty[<?= $id ?>]" class="qty-input" value="<?= $item['qty'] ?>" min="1"></td>
            <td class="subtotal"><?= number_format($subtotal,2) ?></td>
            <td><a href="?remove=<?= $id ?>" class="remove-btn">Remove</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="cart-footer">
    <div class="total" id="cart-total">
        Total: $<?= number_format($total,2) ?>
    </div>
    <button type="submit" name="checkout" class="btn-checkout">Checkout</button>
</div>

</form>
<?php endif; ?>
</div>

<script>
// Update subtotal & total dynamically
const qtyInputs = document.querySelectorAll('.qty-input');
qtyInputs.forEach(input=>{
    input.addEventListener('input', ()=>{
        const row = input.closest('tr');
        let qty = parseInt(input.value);
        if(qty < 1) qty = 1;
        input.value = qty;
        const price = parseFloat(row.querySelector('td:nth-child(3)').innerText);
        const subtotal = price*qty;
        row.querySelector('.subtotal').innerText = subtotal.toFixed(2);

        let total=0;
        document.querySelectorAll('.subtotal').forEach(st=>total+=parseFloat(st.innerText));
        document.getElementById('cart-total').innerText='Total: $'+total.toFixed(2);
    });
});
</script>

<?php include 'includes/footer.php'; ?>