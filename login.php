<?php
session_start();

$error = '';

if (isset($_SESSION['user_id'])) {
    header("Location: transaction.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $error = "กรุณากรอกอีเมลให้ถูกต้อง";
    } else {
        try {
            $db = new PDO('sqlite:database.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // ตรวจสอบรหัสผ่านแบบไม่เข้ารหัส
            if ($user && $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: transaction.php");
                exit;
            } else {
                $error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
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
        <h3 class="text-center mb-4">เข้าสู่ระบบ MyWallet</h3>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">ชื่อผู้ใช้ (อีเมล)</label>
                <input
                    type="email"
                    name="username"
                    class="form-control"
                    id="username"
                    required
                    value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    id="password"
                    required
                />
            </div>
            <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
        </form>
    </div>
</body>
</html>
