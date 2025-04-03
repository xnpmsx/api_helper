<?php
// update_status.php

include('connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
// ตรวจสอบว่าได้ส่งข้อมูลจากฟอร์มหรือไม่
if (isset($_POST['giver_id']) && isset($_POST['action'])) {
    $giver_id = $_POST['giver_id'];
    $action = $_POST['action'];
    
    // กำหนดค่าของ giver_status ตามที่ได้รับจากการกด Accept หรือ Reject
    if ($action == 'accept') {
        $giver_status = 1;
    } elseif ($action == 'reject') {
        $giver_status = 5;
    }

    // อัปเดตสถานะของ caregiver ในฐานข้อมูล
    $sql = "UPDATE giver_profile SET giver_status = :giver_status WHERE giver_id = :giver_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':giver_status', $giver_status, PDO::PARAM_INT);
    $stmt->bindParam(':giver_id', $giver_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // หากการอัปเดตสำเร็จ, redirect กลับไปที่หน้า dashboard
        header("Location: dashboard.php"); // เปลี่ยนไปที่หน้า dashboard ของคุณ
        exit();
    } else {
        echo "Error updating status.";
    }
} else {
    echo "Invalid request.";
}
?>
