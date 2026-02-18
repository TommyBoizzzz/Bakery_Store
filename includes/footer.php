<!-- FOOTER -->
<style>
footer {
    text-align: center;
    padding: 20px;
    color: #4b2e2e;
    background: #f7efe5;
    font-family: 'Poppins', sans-serif;
}

footer a {
    text-decoration: none;
    color: #4b2e2e;
    font-weight: 600;
    margin: 0 10px;
    transition: 0.3s;
}

footer a:hover {
    color: #8b5e3c;
}

.footer-icons img {
    width: 24px;
    height: 24px;
    vertical-align: middle;
    margin-right: 6px;
}
</style>

<footer>
    © <?php echo date('Y'); ?> BaBBoB Bakery
    <br><br>
    <div class="footer-icons">
        <!-- Telegram link -->
        <a href="https://t.me/" target="_blank">
            <img src="assets/images_app/telegram-icon.png" alt="Telegram"> Chat Owner
        </a>

        <!-- Admin home link -->
        <a href="admin/Authencation/login.php" target="_blank">
            <img src="assets/images_app/admin-icon.png" alt="Admin"> Admin Home
        </a>
    </div>
</footer>
