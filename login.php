<?php
session_start();

$error = '';

if (isset($_SESSION['user_id'])) {
    header("Location: transaction.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "กรุณากรอกอีเมลให้ถูกต้อง ตัวอย่างที่ถูกต้อง user@gmail.com, user@pnu.ac.th";
    } else {
        try {
            $db = new PDO('sqlite:database.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // ไม่มีอีเมลนี้ในฐานข้อมูล
                $error = "อีเมลไม่ถูกต้อง";
            } elseif ($password !== $user['password']) {
                // รหัสผ่านไม่ถูกต้อง
                $error = "รหัสผ่านไม่ถูกต้อง";
            } else {
                // เข้าสู่ระบบสำเร็จ
                $_SESSION['user_id'] = $user['id'];
                header("Location: transaction.php");
                exit;
            }
        } catch (Exception $e) {
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body
  class="bg-light d-flex align-items-center justify-content-center"
  style="height: 100vh"
>
    <div class="card shadow p-4" style="max-width: 400px; width: 100%">
        <h3 class="text-center mb-4">เข้าสู่ระบบ</h3>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">อีเมล<span style="color: red;">*</span></label>
                <input
                    type="email"
                    name="email"
                    class="form-control"
                    id="email"
                    required
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน<span style="color: red;">*</span></label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    id="password"
                    required
                />
            </div>
<button type="submit" class="btn w-100 btn-dark">
  เข้าสู่ระบบ
</button>


        </form>
    </div>
</body>
</html>
