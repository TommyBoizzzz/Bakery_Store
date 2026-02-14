<?php
$conn = new mysqli("localhost", "root", "", "bakery_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
