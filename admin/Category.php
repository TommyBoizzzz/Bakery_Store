<?php
include "../config/db.php";
include "Authencation/auth.php";

// ================= INSERT =================
if(isset($_POST['add'])){
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (:name)");
    $stmt->execute(['name' => $name]);

    header("Location: ".$_SERVER['PHP_SELF']); 
    exit();
}

// ================= UPDATE =================
if(isset($_POST['update'])){
    $id   = $_POST['id'];
    $name = $_POST['name'];

    $stmt = $conn->prepare("UPDATE categories SET name = :name WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'id'   => $id
    ]);

    header("Location: ".$_SERVER['PHP_SELF']); 
    exit();
}

// ================= DELETE =================
if(isset($_GET['delete'])){
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header("Location: ".$_SERVER['PHP_SELF']); 
    exit();
}

// ================= SELECT =================
$stmt = $conn->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Category Management</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
*{box-sizing:border-box;}
body{margin:0;font-family:'Poppins',sans-serif;background:#f7efe5;}
header{background:linear-gradient(135deg,#4b2e2e,#c19a6b);color:#fff;padding:20px 40px;font-size:24px;font-weight:600;display:flex;align-items:center;gap:15px;border-bottom:4px solid #c19a6b;}
.back-btn{background:#4b2e2e;color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:600;padding:8px 14px;cursor:pointer;transition:0.3s;}
.back-btn:hover{opacity:0.85;}
.back-btn span{font-weight:600;}
.container{max-width:1200px;margin:20px auto;padding:0 20px;}
.top-bar{display:flex;gap:10px;margin-bottom:20px;}
.search-box{flex:75%;}
.search-box input{width:100%;padding:12px;border-radius:8px;border:1px solid #c19a6b;}
.add-btn{flex:25%;background:#4b2e2e;color:#fff;border:none;border-radius:8px;font-size:16px;cursor:pointer;transition:0.3s;}
.add-btn:hover{opacity:0.85;}
table{width:100%;border-collapse:collapse;background:#f7efe5;}
th{background:#4b2e2e;color:#fff;padding:14px;border:1px solid #c19a6b;}
td{padding:12px;border:1px solid #c19a6b;text-align:center;}
td:nth-child(2){text-align:left;}
.edit{background:#0095ff;}
.delete{background:#b33939;}
.edit,.delete{color:#fff;padding:6px 10px;border-radius:6px;text-decoration:none;font-size:14px;margin-right: 5px;}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);justify-content:center;align-items:center;}
.modal-content{background:#fff;padding:25px;width:100%;max-width:500px;border-radius:12px;}
.modal-content h2{text-align:center;margin-bottom:20px;}
.modal-content input{width:100%;padding:12px;margin:8px 0;border-radius:8px;border:1px solid #c19a6b;}
.submit-btn{background:linear-gradient(135deg,#4b2e2e,#c19a6b);color:#fff;border:none;padding:12px;width:100%;border-radius:8px;cursor:pointer;transition:0.3s;}
.submit-btn:hover{opacity:0.85;}
</style>
</head>

<body>

<header>
    <button class="back-btn" onclick="window.location.href='Home.php'">
        ← <span>BACK</span>
    </button>
    CATEGORY MANAGEMENT
</header>

<div class="container">

    <div class="top-bar">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search category..." onkeyup="searchTable()">
        </div>
        <button class="add-btn" onclick="openModal()">+ Add Category</button>
    </div>

    <table id="categoryTable">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Action</th>
        </tr>

        <?php 
        $counter = 1;
        foreach($categories as $row): 
        ?>
        <tr>
            <td><?= $counter ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>
                <a class="edit" 
                   onclick="openModal(<?= $row['id'] ?>,'<?= htmlspecialchars($row['name'],ENT_QUOTES) ?>')">
                   Edit
                </a>

                <a class="delete" 
                   href="?delete=<?= $row['id'] ?>" 
                   onclick="return confirm('Delete this category?')">
                   Delete
                </a>
            </td>
        </tr>
        <?php 
        $counter++;
        endforeach; 
        ?>
    </table>

</div>

<!-- Modal -->
<div class="modal" id="categoryModal">
    <div class="modal-content">
        <h2 id="modalTitle">Add Category</h2>

        <form method="POST">
            <input type="hidden" name="id" id="categoryId">
            <input type="text" name="name" id="categoryName" placeholder="Category Name" maxlength="15" required>

            <button type="submit" name="add" class="submit-btn" id="submitBtn">
                Add Category
            </button>
        </form>
    </div>
</div>

<script>
function openModal(id = '', name = '') {
    document.getElementById('categoryModal').style.display = 'flex';

    if(id){
        document.getElementById('modalTitle').innerText = 'Edit Category';
        document.getElementById('categoryId').value = id;
        document.getElementById('categoryName').value = name;
        document.getElementById('submitBtn').name = 'update';
        document.getElementById('submitBtn').innerText = 'Update Category';
    } else {
        document.getElementById('modalTitle').innerText = 'Add Category';
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryName').value = '';
        document.getElementById('submitBtn').name = 'add';
        document.getElementById('submitBtn').innerText = 'Add Category';
    }
}

function closeModal(){
    document.getElementById('categoryModal').style.display = 'none';
}

window.onclick = function(event){
    if(event.target == document.getElementById('categoryModal')){
        closeModal();
    }
}

function searchTable(){
    let input = document.getElementById("searchInput").value.toLowerCase();
    let table = document.getElementById("categoryTable");
    let tr = table.getElementsByTagName("tr");

    for(let i = 1; i < tr.length; i++){
        let td = tr[i].getElementsByTagName("td")[1];
        if(td){
            let txtValue = td.textContent || td.innerText;
            tr[i].style.display = txtValue.toLowerCase().includes(input) ? "" : "none";
        }
    }
}
</script>

</body>
</html>