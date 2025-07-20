<?php
session_start();

// เช็ค login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
}

// คำนวณยอดคงเหลือ
$balance = 0;
foreach ($_SESSION['transactions'] as $t) {
    if ($t['type'] === 'deposit') {
        $balance += $t['amount'];
    } elseif ($t['type'] === 'withdraw') {
        $balance -= $t['amount'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date('Y-m-d');
    $type = $_POST['type'] ?? 'deposit';
    $amount = (int) ($_POST['amount'] ?? 0);

    if ($amount > 0) {
        $_SESSION['transactions'][] = [
            'date' => $date,
            'type' => $type,
            'amount' => $amount,
        ];

        // อัพเดตยอดคงเหลือใหม่
        if ($type === 'deposit') {
            $balance += $amount;
        } else {
            $balance -= $amount;
        }
    }

    header("Location: transaction.php");
    exit;
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
    <button
      class="btn btn-outline-light d-md-none"
      type="button"
      data-bs-toggle="offcanvas"
      data-bs-target="#sidebar"
      aria-label="Toggle menu"
    >
      ☰
    </button>
    <span class="navbar-brand mb-0 h1">MyWallet</span>
  </div>
  <a href="logout.php" class="btn btn-danger">ออกจากระบบ</a>
</nav>

<div class="container-fluid">
  <div class="row">
    <nav
      class="col-md-2 bg-secondary text-white d-none d-md-block min-vh-100 p-3"
      aria-label="Sidebar navigation"
    >
      <ul class="nav flex-column">
        <li class="nav-item">
          <a href="transaction.php" class="nav-link active bg-dark rounded text-white">ธุรกรรม</a>
        </li>
        <li class="nav-item">
          <a href="history.php" class="nav-link text-white">ประวัติ</a>
        </li>
      </ul>
    </nav>

    <div
      class="offcanvas offcanvas-start d-md-none bg-secondary text-white"
      id="sidebar"
      tabindex="-1"
      aria-labelledby="sidebarLabel"
    >
      <div class="offcanvas-header">
        <h5 id="sidebarLabel" class="offcanvas-title">เมนู</h5>
        <button
          type="button"
          class="btn-close btn-close-white text-reset"
          data-bs-dismiss="offcanvas"
          aria-label="Close"
        ></button>
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

    <main class="col-md-10 p-4" role="main">
      <h2 class="mb-4">ฝากถอนเงิน</h2>

      <div class="mb-4">
        <h4>ยอดคงเหลือ: <span class="text-success"><?= number_format($balance) ?> บาท</span></h4>
      </div>

      <form method="POST" action="">
        <div class="mb-3">
          <label class="form-label">จำนวนเงิน</label>
         <input
  type="number"
  min="0"
  max="100000"
  name="amount"
  id="amount"
  class="form-control"
  required
/>

        </div>

        <div class="d-flex gap-3">
          <button type="submit" name="type" value="deposit" class="btn btn-success flex-fill">
            ฝาก
          </button>
          <button type="submit" name="type" value="withdraw" class="btn btn-danger flex-fill">
            ถอน
          </button>
        </div>
      </form>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
