<?php
session_start();

// เช็ค login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ตรวจสอบว่า $_SESSION['transactions'] มีหรือยัง ถ้ายังสร้างเปล่า
if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
}

$userEmail = $_SESSION['user_email']; // อ่านอีเมลผู้ใช้จาก session
$balance = 0;
foreach ($_SESSION['transactions'] as $t) {
    if ($t['email'] === $userEmail) {  // คำนวณเฉพาะรายการของ user นี้
        if ($t['type'] === 'deposit') {
            $balance += $t['amount'];
        } elseif ($t['type'] === 'withdraw') {
            $balance -= $t['amount'];
        }
    }
}

$error = '';
$amount = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date('Y-m-d H:i:s');
    $type = $_POST['type'] ?? 'deposit';
    $amount = (int) ($_POST['amount'] ?? 0);

    if ($amount <= 0) {
        $error = "กรุณากรอกจำนวนเงินที่ถูกต้องมากกว่า 0";
    } elseif ($type === 'withdraw' && $amount > $balance) {
        $error = "จำนวนเงินถอนมากกว่ายอดคงเหลือ กรุณากรอกจำนวนใหม่";
    } else {
        $_SESSION['transactions'][] = [
            'date' => $date,
            'type' => $type,
            'amount' => $amount,
            'email' => $userEmail,
        ];

        // เคลียร์ input
        $amount = '';

        // ป้องกันการส่งซ้ำ
        header("Location: transaction.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ฝากถอนเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark px-3">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-light d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Toggle menu">☰</button>
        <span class="navbar-brand mb-0 h1">MyWallet</span>
    </div>
    <a href="logout.php" class="btn btn-danger">ออกจากระบบ</a>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 bg-secondary text-white d-none d-md-block min-vh-100 p-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="transaction.php" class="nav-link active bg-dark rounded text-white">ธุรกรรม</a>
                </li>
                <li class="nav-item">
                    <a href="history.php" class="nav-link text-white">ประวัติ</a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar (mobile) -->
        <div class="offcanvas offcanvas-start d-md-none bg-secondary text-white" id="sidebar" tabindex="-1" aria-labelledby="sidebarLabel">
            <div class="offcanvas-header">
                <h5 id="sidebarLabel" class="offcanvas-title">เมนู</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="transaction.php" class="nav-link active bg-dark rounded text-white">ธุรกรรม</a>
                    </li>
                    <li class="nav-item">
                        <a href="history.php" class="nav-link text-white">ประวัติ</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-10 p-4">
            <h2 class="mb-4">ฝากถอนเงิน</h2>

            <div class="mb-4">
                <h4>จำนวนเงินคงเหลือ: <span class="text-success"><?= number_format($balance) ?> บาท</span></h4>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="transactionForm" method="POST" action="">
                <div class="mb-3">
                    <label for="amount" class="form-label">จำนวนเงิน</label>
                    <input type="number" min="1" max="100000" name="amount" id="amount" class="form-control" required value="<?= htmlspecialchars($amount) ?>" />
                </div>

                <div class="d-flex gap-3">
                    <!-- เปลี่ยนจาก submit เป็น button เพื่อให้ JS ควบคุม -->
                    <button type="button" class="btn btn-success flex-fill" id="btnDeposit" data-type="deposit">ฝาก</button>
                    <button type="button" class="btn btn-danger flex-fill" id="btnWithdraw" data-type="withdraw">ถอน</button>
                </div>
            </form>

            <!-- Modal Confirm -->
            <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <form id="confirmForm" method="POST" action="">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="confirmModalLabel">ยืนยันธุรกรรม</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <p id="confirmMessage"></p>
                      <input type="hidden" name="type" id="confirmType" />
                      <input type="hidden" name="amount" id="confirmAmount" />
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                      <button type="submit" class="btn btn-primary">ยืนยัน</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'))
  const confirmMessage = document.getElementById('confirmMessage')
  const confirmType = document.getElementById('confirmType')
  const confirmAmount = document.getElementById('confirmAmount')
  const confirmForm = document.getElementById('confirmForm')

  const btnDeposit = document.getElementById('btnDeposit')
  const btnWithdraw = document.getElementById('btnWithdraw')
  const amountInput = document.getElementById('amount')
  

  function openConfirm(type) {
    const amount = amountInput.value.trim()
     const amountNum = Number(amount)

    if (!amount || isNaN(amount) || Number(amount) <= 0) {
      alert('กรุณากรอกจำนวนเงินที่ถูกต้องมากกว่า 0')
      return
    }
  if (amountNum > 100000) {
    alert('จำนวนเงินต้องไม่เกิน 100,000 บาท')
    return
  }
    const typeText = type === 'deposit' ? 'ฝาก' : 'ถอน'
    confirmMessage.textContent = `คุณต้องการ${typeText}เงินจำนวน ${Number(amount).toLocaleString()} บาท ใช่หรือไม่?`
    confirmType.value = type
    confirmAmount.value = amount

    confirmModal.show()
  }

  btnDeposit.addEventListener('click', () => openConfirm('deposit'))
  btnWithdraw.addEventListener('click', () => openConfirm('withdraw'))
  confirmForm.addEventListener('submit', function(event) {
    confirmModal.hide()
  })
</script>
</body>
</html>
