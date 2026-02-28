<?php
// footer.php
?>
<style>
footer {
    text-align: center;
    padding: 20px;
    color: #9c5f78; 
    background: #fdf4f8; 
    font-family: 'Poppins', sans-serif;
}

.footer-icons {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 10px;
}

.footer-icons a {
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    color: #c98aa5; 
    font-weight: 600;
    transition: 0.3s;
}

.footer-icons a:hover {
    color: #b76e8a; 
}

.footer-icons img {
    width: 24px;
    height: 24px;
    filter: hue-rotate(-10deg) saturate(1.1);
}

/* MOBILE */
@media (max-width: 768px) {
    footer {
        padding: 25px 10px;
    }
}
</style>

<footer>
    © <?php echo date('Y'); ?> BaBBoB Bakery
    <div class="footer-icons">
        <a href="https://t.me/LUYNALIN" target="_blank">
            <img src="assets/images_app/telegram-icon.png" alt="Telegram"> Chat Owner
        </a>
        <a href="admin/Authencation/login.php" target="_blank">
            <img src="assets/images_app/admin-icon.png" alt="Admin"> Admin Home
        </a>
    </div>
</footer>