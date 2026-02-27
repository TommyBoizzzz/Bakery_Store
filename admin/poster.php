<?php
include "../config/db.php";   // PDO pgsql connection
include "Authencation/auth.php";
require "../vendor/autoload.php"; // AWS SDK for R2
ob_start();

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/* ================= R2 CONFIG ================= */
$accountId = "df47f2fe12698df10266daa2319178dd";
$accessKey = "e74d4d6993ca1199879f5789dc0569d2";
$secretKey = "a472345928d31e1017c3feba96a8897e302e7bae342e6572666093f78c8e01dd";
$bucket = "products-images";
$endpoint = "https://$accountId.r2.cloudflarestorage.com";
$r2PublicUrl = "https://pub-b0d591a0398c44d08c45a13006055165.r2.dev";

/* ================= AWS S3 CLIENT ================= */
$s3 = new S3Client([
    'version' => 'latest',
    'region'  => 'auto',
    'endpoint' => $endpoint,
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => $accessKey,
        'secret' => $secretKey,
    ],
]);

/* ================= UPLOAD FUNCTION ================= */
function uploadToR2($fileTmpPath, $fileName) {
    global $s3, $bucket, $r2PublicUrl;

    $key = "slides/" . $fileName;
    try {
        $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $fileTmpPath,
            'ACL' => 'public-read',
            'ContentType' => mime_content_type($fileTmpPath),
        ]);
        return $r2PublicUrl . "/" . $key;
    } catch (AwsException $e) {
        error_log("R2 Upload Error: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? intval($_POST['id']) : null;

    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $imageName = time() . "_" . uniqid() . "." . $ext;
            $publicUrl = uploadToR2($_FILES['image']['tmp_name'], $imageName);

            if ($publicUrl) {
                if ($id) {
                    // Fetch old image URL
                    $stmt = $conn->prepare("SELECT image FROM images_slide WHERE id=:id");
                    $stmt->execute(['id' => $id]);
                    $oldImage = $stmt->fetchColumn();

                    // Delete old image from R2
                    if ($oldImage) {
                        deleteFromR2($oldImage);
                    }

                    // Update with new image
                    $stmt = $conn->prepare("UPDATE images_slide SET image=:image WHERE id=:id");
                    $stmt->execute(['image' => $publicUrl, 'id' => $id]);
                } else {
                    // Insert new image
                    $stmt = $conn->prepare("INSERT INTO images_slide (image) VALUES (:image)");
                    $stmt->execute(['image' => $publicUrl]);
                }
            } else {
                die("Failed to upload to R2");
            }
        } else {
            die("Invalid file type. Allowed: jpg, jpeg, png, webp");
        }
    }

    header("Location: poster.php");
    exit();
}

/* ================= DELETE FROM R2 FUNCTION ================= */
function deleteFromR2($fileUrl) {
    global $s3, $bucket;

    // Extract key from URL
    $parsed = parse_url($fileUrl);
    $key = ltrim($parsed['path'], '/'); // removes leading '/'

    try {
        $s3->deleteObject([
            'Bucket' => $bucket,
            'Key'    => $key
        ]);
    } catch (AwsException $e) {
        error_log("R2 Delete Error: " . $e->getMessage());
    }
}

/* ================= DELETE SLIDE ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM images_slide WHERE id=:id");
    $stmt->execute(['id' => $id]);
    header("Location: poster.php");
    exit();
}

/* ================= FETCH SLIDES ================= */
$stmt = $conn->query("SELECT * FROM images_slide ORDER BY id DESC");
$slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
header{background:linear-gradient(135deg,#4b2e2e,#c19a6b);color:#fff;padding:20px 40px;font-size:24px;font-weight:600;display:flex;align-items:center;gap:15px;border-bottom:4px solid #c19a6b;}
.back-btn{background:#4b2e2e;color:#fff;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;}
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
.edit{background:#0095ff;color:#fff;padding:6px 10px;border-radius:6px;text-decoration:none;cursor:pointer;}
.delete{background:#b33939;color:#fff;padding:6px 10px;border-radius:6px;text-decoration:none;}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);justify-content:center;align-items:center;}
.modal-content{background:#fff;padding:25px;width:100%;max-width:400px;border-radius:12px;}
.modal-content input{width:100%;padding:12px;margin:8px 0;border-radius:8px;border:1px solid #c19a6b;}
.submit-btn{background:linear-gradient(135deg,#4b2e2e,#c19a6b);color:#fff;border:none;padding:12px;width:100%;border-radius:8px;cursor:pointer;}
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

<?php $i=1; foreach($slides as $row): ?>
<tr>
<td><?= $i++ ?></td>
<td>
<?php if(!empty($row['image'])): ?>
<img src="<?= htmlspecialchars($row['image']) ?>" alt="Poster Image">
<?php endif; ?>
</td>
<td>
<a class="edit" onclick="openModal('<?= $row['id'] ?>')">Edit</a>
<a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this poster?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>

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