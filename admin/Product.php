<?php
include "../config/db.php";   // PDO PostgreSQL connection
include "Authencation/auth.php";

$uploadDir = "../assets/images/";
if(!is_dir($uploadDir)){
    mkdir($uploadDir,0755,true);
}

/* ================= ADD PRODUCT ================= */
if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);

    if (strlen($name) > 24) {
        echo "<script>alert('Product name cannot exceed 24 characters');window.history.back();</script>";
        exit();
    }

    $image = "";
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if(in_array($ext,$allowed)){
            $image = time()."_".uniqid().".".$ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$image);
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO products (name, category_id, price, description, image)
        VALUES (:name, :category_id, :price, :description, :image)
    ");
    $stmt->execute([
        'name'=>$name,
        'category_id'=>$category_id,
        'price'=>$price,
        'description'=>$description,
        'image'=>$image
    ]);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* ================= UPDATE PRODUCT ================= */
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);

    if (strlen($name) > 24) {
        echo "<script>alert('Product name cannot exceed 24 characters');window.history.back();</script>";
        exit();
    }

    if (!empty($_FILES['image']['name'])) {
        $stmt = $conn->prepare("SELECT image FROM products WHERE id=:id");
        $stmt->execute(['id'=>$id]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);

        if($old && !empty($old['image'])){
            $oldPath = $uploadDir.$old['image'];
            if(file_exists($oldPath)) unlink($oldPath);
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image = time()."_".uniqid().".".$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$image);

        $stmt = $conn->prepare("
            UPDATE products
            SET name=:name, category_id=:category_id, price=:price, description=:description, image=:image
            WHERE id=:id
        ");
        $stmt->execute([
            'name'=>$name,
            'category_id'=>$category_id,
            'price'=>$price,
            'description'=>$description,
            'image'=>$image,
            'id'=>$id
        ]);

    } else {
        $stmt = $conn->prepare("
            UPDATE products
            SET name=:name, category_id=:category_id, price=:price, description=:description
            WHERE id=:id
        ");
        $stmt->execute([
            'name'=>$name,
            'category_id'=>$category_id,
            'price'=>$price,
            'description'=>$description,
            'id'=>$id
        ]);
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* ================= DELETE PRODUCT ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT image FROM products WHERE id=:id");
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row && !empty($row['image'])){
        $imagePath = $uploadDir.$row['image'];
        if(file_exists($imagePath)) unlink($imagePath);
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id=:id");
    $stmt->execute(['id'=>$id]);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* ================= FETCH DATA ================= */
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$products = $conn->query("
    SELECT p.*, c.name AS category
    FROM products p
    LEFT JOIN categories c ON p.category_id=c.id
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Product Management</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
*{box-sizing:border-box;}
body{margin:0;font-family:'Poppins',sans-serif;background:#f7efe5;}
header{background:linear-gradient(135deg,#4b2e2e,#c19a6b);color:#fff;padding:20px 40px;font-size:24px;font-weight:600;display:flex;align-items:center;gap:15px;border-bottom:4px solid #c19a6b;}
.back-btn{background:#4b2e2e;color:#fff;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;}
.container{max-width:1200px;margin:20px auto;padding:0 20px;}
.top-bar{display:flex;gap:10px;margin-bottom:20px;}
.search-box{flex:75%;}
.search-box input{width:100%;padding:12px;border-radius:8px;border:1px solid #c19a6b;}
.add-btn{flex:25%;background:#4b2e2e;color:#fff;border:none;border-radius:8px;font-size:16px;cursor:pointer;}
.table-scroll{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;}
table{width:100%;border-collapse:collapse;background:#f7efe5;min-width:800px;}
th{background:#4b2e2e;color:#fff;padding:14px;border:1px solid #c19a6b;}
td{padding:12px;border:1px solid #c19a6b;text-align:center;}
td:nth-child(2){text-align:left;}
img{width:60px;border-radius:6px;}
.edit{background:#0095ff;color:#fff;padding:6px 10px;border-radius:6px;text-decoration:none;}
.delete{background:#b33939;color:#fff;padding:6px 10px;border-radius:6px;text-decoration:none;}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);justify-content:center;align-items:center;}
.modal-content{background:#fff;padding:25px;width:100%;max-width:500px;border-radius:12px;}
.modal-content input,.modal-content select,.modal-content textarea{width:100%;padding:12px;margin:8px 0;border-radius:8px;border:1px solid #c19a6b;}
.submit-btn{background:linear-gradient(135deg,#4b2e2e,#c19a6b);color:#fff;border:none;padding:12px;width:100%;border-radius:8px;cursor:pointer;}
@media (max-width:768px){
    .top-bar{flex-direction:row;}
    .search-box{flex:1 1 50%;}
    .add-btn{flex:1 1 50%;}
    table, th, td{font-size:14px;padding:12px;}
    .modal-content{padding:20px;}
}
</style>
</head>
<body>

<header>
<button class="back-btn" onclick="location.href='home.php'">← BACK</button>
PRODUCT MANAGEMENT
</header>

<div class="container">
<div class="top-bar">
<div class="search-box">
<input type="text" id="searchInput" placeholder="Search product..." onkeyup="searchTable()">
</div>
<button class="add-btn" onclick="openModal()">+ Add Product</button>
</div>

<div class="table-scroll">
<table id="productTable">
<tr>
<th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Image</th><th>Action</th>
</tr>

<?php $i=1; foreach($products as $row): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['category']) ?></td>
<td>$<?= number_format($row['price'],2) ?></td>
<td><?php if(!empty($row['image'])): ?><img src="../assets/images/<?= htmlspecialchars($row['image']) ?>"><?php endif; ?></td>
<td>
<a class="edit" onclick="openModal(
<?= $row['id'] ?>,
'<?= htmlspecialchars($row['name'],ENT_QUOTES) ?>',
<?= $row['category_id'] ?>,
<?= $row['price'] ?>,
'<?= htmlspecialchars($row['description'],ENT_QUOTES) ?>'
)">Edit</a>
<a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete product?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>

<div class="modal" id="productModal">
  <div class="modal-content">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" id="pid">
      <input type="text" name="name" id="pname" placeholder="Product name" maxlength="24" required>
      <select name="category_id" id="pcategory" required>
        <option value="">Select category</option>
        <?php foreach($categories as $c): ?>
          <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="number" name="price" id="pprice" placeholder="Price" required>
      <textarea name="description" id="pdesc" placeholder="Description"></textarea>
      <input type="file" name="image">
      <button type="submit" class="submit-btn" id="submitBtn" name="add">Add Product</button>
    </form>
  </div>
</div>

<script>
const productModal = document.getElementById('productModal');
const pid = document.getElementById('pid');
const pname = document.getElementById('pname');
const pcategory = document.getElementById('pcategory');
const pprice = document.getElementById('pprice');
const pdesc = document.getElementById('pdesc');
const submitBtn = document.getElementById('submitBtn');

function openModal(id='', name='', cat='', price='', desc='') {
    productModal.style.display = 'flex';
    if(id){
        pid.value = id; pname.value = name; pcategory.value = cat; pprice.value = price; pdesc.value = desc;
        submitBtn.name = 'update'; submitBtn.innerText = 'Update Product';
    } else {
        pid.value=''; pname.value=''; pcategory.value=''; pprice.value=''; pdesc.value='';
        submitBtn.name='add'; submitBtn.innerText='Add Product';
    }
}

window.onclick = e => { if(e.target==productModal) productModal.style.display='none'; }

function searchTable() {
    let v=document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll("#productTable tr").forEach((r,i)=>{ if(i===0) return; r.style.display = r.innerText.toLowerCase().includes(v)?'':'none'; });
}
</script>

</body>
</html>