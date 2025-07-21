<?php
session_start();

if (isset($_SESSION['user_id'])) {
    // ถ้ามี session user_id ให้ไปหน้า transaction.php
    header("Location: transaction.php");
    exit();
} else {
    // ถ้าไม่มี session user_id ให้ไปหน้า login.php
    header("Location: login.php");
    exit();
}
?>
