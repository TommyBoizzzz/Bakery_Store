<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Fetch products with categories
$sql = "
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
";
$result = $conn->query($sql);

$products = [];
$categories = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
    if ($row['category_name']) $categories[$row['category_name']] = true;
}
$categories = array_keys($categories);
?>

<style>
/* same styles as your last product.php */
.filter-box{max-width:1200px;margin:20px auto;padding:0 15px;}
.search-input{width:100%;padding:10px 15px;border-radius:20px;border:1px solid #c19a6b;outline:none;font-size:14px;margin-bottom:10px;height:40px;}
.search-input:focus{ border-color:#8b5e3c; }
.category-tabs{margin-top:15px;display:flex;gap:10px;overflow-x:auto;white-space:nowrap;padding-bottom:10px;cursor:grab;}
.category-tabs::-webkit-scrollbar{ display:none; }
.category-tabs button{flex:0 0 auto;padding:10px 20px;border:none;border-radius:20px;background:#8b5e3c;color:#fff;font-size:13px;cursor:pointer;transition:0.3s;user-select:none;}
.category-tabs button.active,.category-tabs button:hover{ background:#4b2e2e; }

.products{max-width:1200px;margin:30px auto;padding:0 15px;display:grid;grid-template-columns:repeat(4,1fr);gap:18px;}
@media(max-width:768px){ .products{grid-template-columns:repeat(2,1fr);} }

.product{background:#f7efe5;border-radius:14px;box-shadow:0 6px 15px rgba(75,46,46,0.25);overflow:hidden;}
.product img{ width:100%; height:200px; object-fit:cover; }
.product-body{ padding:12px; }
.product h3{ margin:0;color:#4b2e2e; }
.category-text{ font-size:12px;color:#8b5e3c;font-weight:600; }
.desc{ font-size:13px;color:#555;margin:6px 0; }
.price{ font-weight:600;color:#6f4e37;margin-bottom:8px; }
.btn{ display:block; padding:8px; border-radius:30px; text-align:center; color:#fff; font-size:13px; text-decoration:none; margin-bottom:6px; }
.btn-view{background:#8b5e3c;}
.btn-cart{background:#4b2e2e;}
body{ overflow-y:scroll; }
</style>

<div class="filter-box">
    <input type="text" id="searchBox" class="search-input" placeholder="Search cakes...">
    <div class="category-tabs" id="categoryTabs">
        <button class="active" data-category="all">All</button>
        <?php foreach($categories as $cat): ?>
            <button data-category="<?php echo $cat; ?>"><?php echo $cat; ?></button>
        <?php endforeach; ?>
    </div>
</div>

<section class="products" id="productList">
<?php foreach($products as $row): ?>
    <div class="product"
         data-name="<?php echo strtolower($row['name']); ?>"
         data-category="<?php echo $row['category_name']; ?>">
        <img src="assets/images/<?php echo $row['image']; ?>">
        <div class="product-body">
            <h3><?php echo $row['name']; ?></h3>
            <div class="category-text"><?php echo $row['category_name']; ?></div>
            <div class="desc">
                <?php $desc=$row['description']; echo strlen($desc)>15 ? substr($desc,0,15).'...' : $desc; ?>
            </div>
            <div class="price">$<?php echo number_format($row['price'],2); ?></div>

            <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                <button type="submit" name="add_to_cart" class="btn btn-cart">Add to Cart</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
</section>

<script>
// SEARCH + CATEGORY FILTER
const searchBox = document.getElementById('searchBox');
const productsList = document.querySelectorAll('.product');
const categoryButtons = document.querySelectorAll('.category-tabs button');
let selectedCategory = "all";

function applyFilters(){
    const searchValue = searchBox.value.toLowerCase();
    productsList.forEach(p => {
        const nameMatch = p.dataset.name.includes(searchValue);
        const categoryMatch = selectedCategory==="all" || p.dataset.category===selectedCategory;
        p.style.display = (nameMatch && categoryMatch)?'':'none';
    });
}

searchBox.addEventListener('keyup',applyFilters);
categoryButtons.forEach(btn=>{
    btn.addEventListener('click',function(){
        categoryButtons.forEach(b=>b.classList.remove('active'));
        this.classList.add('active');
        selectedCategory=this.dataset.category;
        applyFilters();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
