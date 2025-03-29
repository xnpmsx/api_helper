<?php
include('connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'];
    $action = $_POST['action'];

    // กำหนดค่า job_status ตาม action ที่ได้รับ
    switch ($action) {
        case 'cancel':
            $new_status = 0;
            $sql = "UPDATE job SET job_status = ?, giver_id = 0 WHERE job_id = ?"; // รอผู้ให้บริการยืนยัน
            break;
        case 'confirm':
            $new_status = 2;
            $sql = "UPDATE job SET job_status = ? WHERE job_id = ?"; // กำลังดำเนินการ
            break;
        default:
            header("Location: job_management.php?error=invalid_action");
            exit();
    }

    // อัปเดตสถานะในฐานข้อมูล
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$new_status, $job_id])) {
        header("Location: job_management.php?success=updated");
    } else {
        header("Location: job_management.php?error=update_failed");
    }
    exit();
}
?>
