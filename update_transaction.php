<?php
session_start();

// เช็ค login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ตรวจสอบว่ามี transactions ใน session
if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
}

// ตรวจสอบข้อมูล POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
    $type = $_POST['type'] ?? '';
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;

    // Validate type และ amount
    $validTypes = ['deposit', 'withdraw'];
    if ($index < 0 || !isset($_SESSION['transactions'][$index])) {
        die('ธุรกรรมไม่ถูกต้อง');
    }
    if (!in_array($type, $validTypes)) {
        die('ประเภทธุรกรรมไม่ถูกต้อง');
    }
    if ($amount <= 0) {
        die('จำนวนเงินต้องมากกว่า 0');
    }

    // อัพเดตข้อมูล
    $_SESSION['transactions'][$index]['type'] = $type;
    $_SESSION['transactions'][$index]['amount'] = $amount;

    // กลับไปที่หน้าประวัติ
    header("Location: history.php");
    exit;
} else {
    die('วิธีการส่งข้อมูลไม่ถูกต้อง');
}
?>
