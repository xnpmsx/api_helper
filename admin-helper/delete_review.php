<?php
session_start();
include('connect.php');

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีค่าที่ส่งมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['review_id'], $_POST['csrf_token'])) {
    
    // ตรวจสอบ CSRF Token
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token ไม่ถูกต้อง!");
    }

    $review_id = $_POST['review_id'];

    // ลบข้อมูลจากฐานข้อมูล
    $sql = "DELETE FROM review WHERE review_id = :review_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':review_id', $review_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['message'] = "ลบรีวิวสำเร็จ!";
    } else {
        $_SESSION['message'] = "เกิดข้อผิดพลาดในการลบรีวิว!";
    }
}

// กลับไปที่หน้า report.php
header("Location: report.php");
exit();
?>
