<?php
session_start();
include __DIR__ . "/../../config/db.php";

$error = '';

if (isset($_POST['login'])) {

    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        header("Location: ../home.php");
        exit;
    } else {
        $error = "❌ Wrong username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bakery Admin Login</title>
<link rel="icon" type="image/png" href="../../assets/images_app/Link.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
}

body {
    min-height: 100vh;
    background: linear-gradient(135deg, #4b2e2e, #c19a6b);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* Main Container */
.login-wrapper {
    width: 100%;
    max-width: 1000px;
    display: flex;
    background: #fff;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

/* Left Side */
.login-left {
    flex: 1;
    background-color: #4b2e2e;   /* ✅ Solid background color */
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 50px 30px;
    text-align: center;
}

.login-left img {
    width: 120px;
    margin-bottom: 20px;
}

.login-left h1 {
    font-size: 26px;
    margin-bottom: 10px;
}

.login-left p {
    font-size: 14px;
    opacity: 0.9;
}

/* Right Side */
.login-right {
    flex: 1;
    padding: 60px 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-right h2 {
    color: #4b2e2e;
    margin-bottom: 25px;
    font-size: 22px;
}

.login-right input {
    width: 100%;
    padding: 14px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #ddd;
    font-size: 15px;
    transition: 0.3s;
}

.login-right input:focus {
    border-color: #c19a6b;
    box-shadow: 0 0 8px rgba(193,154,107,0.5);
    outline: none;
}

.login-right button {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: #4b2e2e;
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
}

.login-right button:hover {
    background: #c19a6b;
}

.error {
    margin-top: 10px;
    color: #e74c3c;
    font-size: 14px;
}

/* ================= MOBILE RESPONSIVE ================= */

@media(max-width: 768px){

    body {
        padding: 10px;
    }

    .login-wrapper {
        flex-direction: column;
        max-width: 420px;
    }

    .login-left {
        padding: 40px 20px;
    }

    .login-left img {
        width: 90px;
    }

    .login-left h1 {
        font-size: 20px;
    }

    .login-left p {
        font-size: 13px;
    }

    .login-right {
        padding: 35px 25px;
    }

    .login-right h2 {
        font-size: 20px;
        text-align: center;
    }

}
</style>
</head>

<body>

<div class="login-wrapper">

    <!-- Left Section -->
    <div class="login-left">
        <img src="../../assets/images_app/Logo.png" alt="Bakery Logo">
        <h1>Bakery Admin Panel</h1>
        <p>Manage products, orders, categories and reports easily.</p>
    </div>

    <!-- Right Section -->
    <div class="login-right">
        <h2>Admin Login</h2>

        <form method="POST">
            <input type="text" name="username" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit" name="login">Login</button>
        </form>

        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    </div>

</div>

</body>
</html>
