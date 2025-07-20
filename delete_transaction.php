<?php
session_start();

// เช็ค login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = $_POST['index'] ?? null;

    if ($index !== null && isset($_SESSION['transactions'][$index])) {
        // ลบธุรกรรมที่ตำแหน่ง index
        array_splice($_SESSION['transactions'], $index, 1);
    }
}

// กลับไปหน้าประวัติธุรกรรม
header("Location: history.php");
exit;
