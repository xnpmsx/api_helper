<?php
session_start();

// ล้างค่า session ทั้งหมด
$_SESSION = [];
session_unset();
session_destroy();

// ป้องกัน Back Button หลัง Logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Location: login.php"); // กลับไปหน้า login
exit();
?>
