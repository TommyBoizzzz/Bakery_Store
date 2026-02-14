<?php include 'includes/header.php'; ?>
<?php
include 'config/db.php';

// Fetch all slides
$slides_res = mysqli_query($conn, "SELECT * FROM images_slide ORDER BY id ASC");
$slides = [];
if($slides_res){
    while($row = mysqli_fetch_assoc($slides_res)){
        $slides[] = $row;
    }
}
?>

<!-- PROMOTION SLIDER -->
<div class="slider-container" style="overflow:hidden;position:relative;width:100%;max-width:1200px;margin:20px auto;border-radius:12px;">
    <div class="slides" id="slides" style="display:flex;transition:0.5s ease;">
        <?php if(!empty($slides)): ?>
            <?php foreach($slides as $slide): ?>
                <div class="slide" style="min-width:100%; min-height : 210px; position:relative;">
                    <img src="admin/assets/images_slide/<?= $slide['image'] ?>" style="width:100%;border-radius:12px;" alt="<?= htmlspecialchars($slide['title']) ?>">
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="min-width:100%;text-align:center;padding:40px;background:#f7efe5;">No slides available</div>
        <?php endif; ?>
    </div>
    
    <!-- Optional navigation buttons -->
    <button id="prev" style="position:absolute;top:50%;left:10px;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:#fff;padding:10px;border:none;border-radius:50%;cursor:pointer;">&#10094;</button>
    <button id="next" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:#fff;padding:10px;border:none;border-radius:50%;cursor:pointer;">&#10095;</button>
</div>

<div style="text-align:center;padding:40px;">
    <h2>Welcome to BaBBoB Bakery 🍰</h2>
    <p>Enjoy our fresh cakes and promotions every day!</p>
</div>

<script>
// Slider JS
let index = 0;
const slidesDiv = document.getElementById("slides");
const slidesCount = slidesDiv.children.length;

function showSlide(i){
    slidesDiv.style.transform = "translateX(-" + (i * 100) + "%)";
}

// Automatic sliding
let sliderInterval = setInterval(() => {
    index++;
    if(index >= slidesCount) index = 0;
    showSlide(index);
}, 3000);

// Navigation buttons
document.getElementById('prev').addEventListener('click', () => {
    index--;
    if(index < 0) index = slidesCount - 1;
    showSlide(index);
    resetInterval();
});

document.getElementById('next').addEventListener('click', () => {
    index++;
    if(index >= slidesCount) index = 0;
    showSlide(index);
    resetInterval();
});

// Reset interval after manual navigation
function resetInterval(){
    clearInterval(sliderInterval);
    sliderInterval = setInterval(() => {
        index++;
        if(index >= slidesCount) index = 0;
        showSlide(index);
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>
