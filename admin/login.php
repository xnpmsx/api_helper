<?php
session_start();
include('connect.php'); // เชื่อมต่อฐานข้อมูล

// ป้องกัน Clickjacking
header("X-Frame-Options: DENY");

// ตรวจสอบว่ามีการล็อกบัญชีชั่วคราวหรือไม่
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_SESSION['login_attempts'] >= 5) {
        $error_message = "⏳ บัญชีถูกล็อกชั่วคราว กรุณารอ 10 นาที";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // ตรวจสอบข้อมูลในฐานข้อมูล
        $query = "SELECT * FROM admin WHERE admin_username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_OBJ);

        if ($admin) {
            if (password_verify($password, $admin->admin_password)) {
                session_regenerate_id(true); // ป้องกัน Session Hijacking
                $_SESSION['admin_id'] = $admin->admin_id;
                $_SESSION['admin_username'] = $admin->admin_username;

                $_SESSION['login_attempts'] = 0; // รีเซ็ตจำนวนครั้งที่ล้มเหลว
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['login_attempts']++;
                $error_message = "❌ Username หรือ รหัสผ่านไม่ถูกต้อง!";
            }
        } else {
            $_SESSION['login_attempts']++;
            $error_message = "❌ Username หรือ รหัสผ่านไม่ถูกต้อง!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <style>
        .login-box {
            margin: 100px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            text-align: center;
        }
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
        .logo {
            width: 200px;
            height: auto;
            margin-bottom: 15px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0 10px 0;
        }
        button {
            width: 100%;
            padding: 8px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <!-- แสดงโลโก้ด้านบน -->
    <img src="assets/logo.png" alt="Logo" class="logo">

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
