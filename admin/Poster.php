<?php
include "../config/db.php";
include "Authencation/auth.php";

// Folder for storing uploaded images
$uploadFolder = "assets/images_slide/";

// Make sure the folder exists
if(!is_dir($uploadFolder)){
    mkdir($uploadFolder, 0755, true);
}

/* ===== ADD SLIDE ===== */
if(isset($_POST['add'])){
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $image = "";

    if(!empty($_FILES['image']['name'])){
        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadFolder.$image);
    }

    $res = mysqli_query($conn, "INSERT INTO images_slide (image, title, subtitle) VALUES ('$image', '$title', '$subtitle')");
    if(!$res) die("Insert failed: ".mysqli_error($conn));

    header("Location: Poster.php");
    exit();
}

/* ===== UPDATE SLIDE ===== */
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];

    if(!empty($_FILES['image']['name'])){
        // delete old image
        $res = mysqli_query($conn, "SELECT image FROM images_slide WHERE id=$id");
        if(!$res) die("Query failed: ".mysqli_error($conn));
        $old = mysqli_fetch_assoc($res);
        if($old && !empty($old['image'])){
            $imgPath = $uploadFolder.$old['image'];
            if(file_exists($imgPath)) unlink($imgPath);
        }

        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadFolder.$image);

        $res2 = mysqli_query($conn, "UPDATE images_slide SET title='$title', subtitle='$subtitle', image='$image' WHERE id=$id");
        if(!$res2) die("Update failed: ".mysqli_error($conn));
    } else {
        $res2 = mysqli_query($conn, "UPDATE images_slide SET title='$title', subtitle='$subtitle' WHERE id=$id");
        if(!$res2) die("Update failed: ".mysqli_error($conn));
    }

    header("Location: Poster.php");
    exit();
}

/* ===== DELETE SLIDE ===== */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];

    $res = mysqli_query($conn, "SELECT image FROM images_slide WHERE id=$id");
    if(!$res) die("Query failed: ".mysqli_error($conn));
    $row = mysqli_fetch_assoc($res);
    if($row && !empty($row['image'])){
        $imgPath = $uploadFolder.$row['image'];
        if(file_exists($imgPath)) unlink($imgPath);
    }

    $res2 = mysqli_query($conn, "DELETE FROM images_slide WHERE id=$id");
    if(!$res2) die("Delete failed: ".mysqli_error($conn));

    header("Location: Poster.php");
    exit();
}

/* ===== FETCH SLIDES ===== */
$slides = mysqli_query($conn, "SELECT * FROM images_slide ORDER BY id DESC");
if(!$slides) die("Fetch failed: ".mysqli_error($conn));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Slider</title>
<style>
body{font-family:sans-serif;background:#f7efe5;padding:20px;}
h2{margin-bottom:20px;}
table{width:100%;border-collapse:collapse;margin-bottom:30px;}
th, td{padding:12px;border:1px solid #c19a6b;text-align:center;}
th{background:#4b2e2e;color:#fff;}
img{width:120px;border-radius:6px;}
button, input[type="submit"]{padding:10px 20px;border:none;border-radius:6px;background:#4b2e2e;color:#fff;cursor:pointer;}
button.delete{background:#b33939;}
button.edit{background:#0095ff;}
form{margin-bottom:40px;background:#fff;padding:20px;border-radius:12px;}
input, textarea{width:100%;padding:10px;margin:8px 0;border:1px solid #c19a6b;border-radius:6px;}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);justify-content:center;align-items:center;}
.modal-content{background:#fff;padding:25px;width:100%;max-width:500px;border-radius:12px;}
</style>
</head>
<body>

<h2>Manage Slider</h2>

<!-- MODAL FORM -->
<div class="modal" id="sliderModal">
    <div class="modal-content">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="sid">
            <h3 id="modalTitle">Add Slide</h3>
            <input type="text" name="title" id="stitle" placeholder="Title" required>
            <textarea name="subtitle" id="ssubtitle" placeholder="Subtitle"></textarea>
            <input type="file" name="image" id="simage">
            <br><br>
            <button type="submit" name="add" id="submitBtn">Add Slide</button>
            <button type="button" onclick="closeModal()" style="background:#b33939;">Cancel</button>
        </form>
    </div>
</div>

<!-- ADD NEW BUTTON -->
<button onclick="openModal()">+ Add New Slide</button>

<!-- SLIDES TABLE -->
<table>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Subtitle</th>
        <th>Image</th>
        <th>Action</th>
    </tr>
    <?php while($row=mysqli_fetch_assoc($slides)): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['title'] ?></td>
        <td><?= $row['subtitle'] ?></td>
        <td><img src="<?= $uploadFolder.$row['image'] ?>" alt=""></td>
        <td>
            <button class="edit" onclick="openModal(<?= $row['id'] ?>,'<?= addslashes($row['title']) ?>','<?= addslashes($row['subtitle']) ?>')">Edit</button>
            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this slide?')"><button class="delete">Delete</button></a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<script>
const modal = document.getElementById('sliderModal');
const sid = document.getElementById('sid');
const stitle = document.getElementById('stitle');
const ssubtitle = document.getElementById('ssubtitle');
const submitBtn = document.getElementById('submitBtn');
const modalTitle = document.getElementById('modalTitle');

function openModal(id='', title='', subtitle=''){
    modal.style.display='flex';
    sid.value = id;
    stitle.value = title;
    ssubtitle.value = subtitle;

    if(id){
        submitBtn.name='update';
        submitBtn.innerText='Update Slide';
        modalTitle.innerText='Edit Slide';
    } else {
        submitBtn.name='add';
        submitBtn.innerText='Add Slide';
        modalTitle.innerText='Add Slide';
    }
}

function closeModal(){
    modal.style.display='none';
    sid.value=''; stitle.value=''; ssubtitle.value=''; submitBtn.name='add'; submitBtn.innerText='Add Slide';
}

window.onclick = e => { if(e.target==modal) closeModal(); }
</script>

</body>
</html>
