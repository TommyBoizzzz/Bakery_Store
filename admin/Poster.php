<?php
include "../config/db.php";
include "Authencation/auth.php";

// Folder for storing uploaded images
$uploadFolder = "assets/images_slide/";
if(!is_dir($uploadFolder)) mkdir($uploadFolder,0755,true);

// ADD SLIDE
if(isset($_POST['add'])){
    $image = "";

    if(!empty($_FILES['image']['name'])){
        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadFolder.$image);
    }

    mysqli_query($conn, "INSERT INTO images_slide (image) VALUES ('$image')");
    header("Location: Poster.php"); exit();
}

// UPDATE SLIDE
if(isset($_POST['update'])){
    $id = $_POST['id'];

    if(!empty($_FILES['image']['name'])){
        $res = mysqli_query($conn, "SELECT image FROM images_slide WHERE id=$id");
        $old = mysqli_fetch_assoc($res);
        if($old && !empty($old['image'])){
            $imgPath = $uploadFolder.$old['image'];
            if(file_exists($imgPath)) unlink($imgPath);
        }
        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadFolder.$image);
        mysqli_query($conn,"UPDATE images_slide SET image='$image' WHERE id=$id");
    }
    header("Location: Poster.php"); exit();
}

// DELETE SLIDE
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $res = mysqli_query($conn,"SELECT image FROM images_slide WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    if($row && !empty($row['image'])){
        $imgPath = $uploadFolder.$row['image'];
        if(file_exists($imgPath)) unlink($imgPath);
    }
    mysqli_query($conn,"DELETE FROM images_slide WHERE id=$id");
    header("Location: Poster.php"); exit();
}

// FETCH SLIDES
$slides = mysqli_query($conn,"SELECT * FROM images_slide ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Poster Management</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
*{box-sizing:border-box;}
body{margin:0;font-family:'Poppins',sans-serif;background:#f7efe5;}
header{
    background:linear-gradient(135deg,#4b2e2e,#c19a6b);
    color:#fff;
    padding:20px 40px;
    font-size:24px;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:15px;
    border-bottom:4px solid #c19a6b;
}
.back-btn{
    background:#4b2e2e;
    color:#fff;
    border:none;
    border-radius:8px;
    padding:8px 14px;
    cursor:pointer;
}
.container{max-width:1200px;margin:20px auto;padding:0 20px;}
.top-bar{display:flex;gap:10px;margin-bottom:20px;}
.search-box{flex:75%;}
.search-box input{width:100%;padding:12px;border-radius:8px;border:1px solid #c19a6b;}
.add-btn{flex:25%;background:#4b2e2e;color:#fff;border:none;border-radius:8px;font-size:16px;cursor:pointer;}
.table-scroll{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;}
.table-scroll table{width:100%;border-collapse:collapse;background:#f7efe5;min-width:500px;}
th{background:#4b2e2e;color:#fff;padding:14px;border:1px solid #c19a6b;}
td{padding:12px;border:1px solid #c19a6b;text-align:center;}
img{width:120px;border-radius:6px;}
.edit{background:#0095ff;color:#fff;padding:6px 10px;border-radius:6px;text-decoration:none;}
.delete{background:#b33939;color:#fff;padding:6px 10px;border-radius:6px;text-decoration:none;}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);justify-content:center;align-items:center;}
.modal-content{background:#fff;padding:25px;width:100%;max-width:400px;border-radius:12px;}
.modal-content input{width:100%;padding:12px;margin:8px 0;border-radius:8px;border:1px solid #c19a6b;}
.submit-btn{background:linear-gradient(135deg,#4b2e2e,#c19a6b);color:#fff;border:none;padding:12px;width:100%;border-radius:8px;cursor:pointer;}
@media (max-width:768px){
    header{font-size:20px;padding:20px 20px;}
    .table-scroll table{min-width:400px;}
    .search-box{flex:60%;}
    .add-btn{flex:40%;}
}
</style>
</head>
<body>

<header>
<button class="back-btn" onclick="location.href='home.php'">← BACK</button>
POSTER MANAGEMENT
</header>

<div class="container">

<div class="top-bar">
<div class="search-box">
<input type="text" id="searchInput" placeholder="Search poster..." onkeyup="searchTable()">
</div>
<button class="add-btn" onclick="openModal()">+ Add Poster</button>
</div>

<div class="table-scroll">
<table id="posterTable">
<tr>
<th>No</th>
<th>Image</th>
<th>Action</th>
</tr>
<?php $i=1; while($row=mysqli_fetch_assoc($slides)): ?>
<tr>
<td><?= $i++ ?></td>
<td><img src="<?= $uploadFolder.$row['image'] ?>" alt=""></td>
<td>
<a class="edit" onclick="openModal('<?= $row['id'] ?>')">Edit</a>
<a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this poster?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>
</div>

<!-- MODAL -->
<div class="modal" id="posterModal">
<div class="modal-content">
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="id" id="pid">
<input type="file" name="image" required>
<button type="submit" class="submit-btn" id="submitBtn" name="add">Add Poster</button>
</form>
</div>
</div>

<script>
const posterModal = document.getElementById('posterModal');
const pid = document.getElementById('pid');
const submitBtn = document.getElementById('submitBtn');

function openModal(id=''){
    posterModal.style.display='flex';
    pid.value = id;
    if(id){
        submitBtn.name='update';
        submitBtn.innerText='Update Poster';
    } else {
        submitBtn.name='add';
        submitBtn.innerText='Add Poster';
    }
}

window.onclick = e => { 
    if(e.target==posterModal) posterModal.style.display='none';
}

function searchTable(){
    let input = document.getElementById("searchInput").value.toLowerCase();
    let tr = document.querySelectorAll("#posterTable tr");
    for(let i=1;i<tr.length;i++){
        tr[i].style.display = tr[i].innerText.toLowerCase().includes(input) ? '' : 'none';
    }
}
</script>

</body>
</html>
