<?php
include('connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
// ตรวจสอบว่า request_id ถูกส่งมาใน URL หรือไม่
if (isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
    // Update request_status เป็น 10 (สำหรับปฏิเสธคำร้อง)
    $sql = "UPDATE request SET request_status = 10 WHERE request_id = :request_id";

    try {
        // เตรียมคำสั่ง SQL
        $stmt = $pdo->prepare($sql);
        
        // ผูกค่าของ request_id กับคำสั่ง SQL
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        
        // Execute คำสั่ง SQL
        if ($stmt->execute()) {
            // หากการอัพเดทสำเร็จ ให้ redirect ไปที่หน้า report list
            header("Location: request.php");
            exit();
        } else {
            echo "เกิดข้อผิดพลาดในการอัพเดทสถานะคำร้อง";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "ไม่พบ request_id";
}
?>
