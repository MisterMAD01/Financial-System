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

$transactions = $_SESSION['transactions'];

function translateType($type) {
    switch($type) {
        case 'deposit': return 'ฝาก';
        case 'withdraw': return 'ถอน';
        default: return $type;
    }
}

function translateDateTimeToBuddhistEra($dateTimeStr) {
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeStr);
    if (!$date) return $dateTimeStr;

    $day = $date->format('d');
    $month = $date->format('m');
    $year = $date->format('Y') + 543;
    $time = $date->format('H:i:s');

    return "$day-$month-$year $time";
}
?>

<!DOCTYPE html>
<html lang="th">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ประวัติธุรกรรม</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
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
              <a href="transaction.php" class="nav-link text-white">ธุรกรรม</a>
            </li>
            <li class="nav-item">
              <a
                href="history.php"
                class="nav-link active bg-dark rounded text-white"
                aria-current="page"
                >ประวัติ</a
              >
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
                <a href="transaction.php" class="nav-link text-white"
                  >ธุรกรรม</a
                >
              </li>
              <li class="nav-item">
                <a
                  href="history.php"
                  class="nav-link active bg-dark rounded text-white"
                  aria-current="page"
                  >ประวัติ</a
                >
              </li>
            </ul>
          </div>
        </div>

        <main class="col-md-10 p-4" role="main">
          <h2 class="mb-4">ประวัติรายการฝากถอน</h2>
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead class="table-primary">
                <tr>
                  <th>วันที่และเวลา</th>
                       <th>จำนวนเงิน</th>
                  <th>ประเภท</th>
             <th>อีเมล</th>
                  <th>จัดการ</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($transactions) === 0): ?>
                <tr>
                  <td colspan="4" class="text-center">ยังไม่มีธุรกรรม</td>
                </tr>
                <?php else: ?>
                <?php foreach ($transactions as $index => $t): ?>
                <tr>
                 <td><?= htmlspecialchars(translateDateTimeToBuddhistEra($t['date'])) ?></td>
                  <td><?= number_format($t['amount']) ?> บาท</td>
        <td class="<?= $t['type'] === 'deposit' ? 'text-success' : 'text-danger' ?>">
  <?= translateType($t['type']) ?>
</td>
             <td><?= htmlspecialchars($t['email'] ) ?></td>
    
                  <td>
                    <!-- ปุ่มแก้ไข -->
                    <button 
                      class="btn btn-sm btn-warning me-2"
                      data-bs-toggle="modal"
                      data-bs-target="#editModal"
                      data-index="<?= $index ?>"
                      data-date="<?= htmlspecialchars($t['date']) ?>"
                      data-type="<?= $t['type'] ?>"
                      data-amount="<?= $t['amount'] ?>"
                    >
                      แก้ไข
                    </button>

                    <!-- ปุ่มลบ -->
                    <button
                      class="btn btn-sm btn-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteModal"
                      data-index="<?= $index ?>"
                    >
                      ลบ
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </main>
      </div>
    </div>

    <!-- Modal แก้ไข -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editForm" method="POST" action="update_transaction.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">แก้ไขธุรกรรม</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="index" id="transactionIndex" />
          
          <!-- เพิ่มวันที่แสดงแบบอ่านอย่างเดียว -->
          <div class="mb-3">
            <label for="transactionDate" class="form-label">วันที่ทำธุรกรรม</label>
            <input type="text" id="transactionDate" class="form-control" readonly />
          </div>

          <div class="mb-3">
            <label for="transactionType" class="form-label">ประเภท</label>
            <select class="form-select" id="transactionType" name="type" required>
              <option value="deposit">ฝาก</option>
              <option value="withdraw">ถอน</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="transactionAmount" class="form-label">จำนวนเงิน</label>
            <input
              type="number"
              class="form-control"
              id="transactionAmount"
              name="amount"
              min="1"
              max="100000"
              required
            />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            ยกเลิก
          </button>
          <button type="submit" class="btn btn-primary">บันทึก</button>
        </div>
      </div>
    </form>
  </div>
</div>


    <!-- Modal Confirm ลบ -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form id="deleteForm" method="POST" action="delete_transaction.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deleteModalLabel">ยืนยันการลบ</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              คุณต้องการลบธุรกรรมนี้หรือไม่?
              <input type="hidden" name="index" id="deleteIndex" />
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                ยกเลิก
              </button>
              <button type="submit" class="btn btn-danger">ลบ</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>
    <script>
// Modal แก้ไข
const editModal = document.getElementById('editModal')
editModal.addEventListener('show.bs.modal', event => {
  const button = event.relatedTarget
  const index = button.getAttribute('data-index')
  const type = button.getAttribute('data-type')
  const amount = button.getAttribute('data-amount')
  const date = button.getAttribute('data-date')  

  document.getElementById('transactionIndex').value = index
  document.getElementById('transactionType').value = type
  document.getElementById('transactionAmount').value = amount
  document.getElementById('transactionDate').value = date  
})

      // Modal ลบ
      const deleteModal = document.getElementById('deleteModal')
      deleteModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget
        const index = button.getAttribute('data-index')

        document.getElementById('deleteIndex').value = index
      })
    </script>
  </body>
</html>
