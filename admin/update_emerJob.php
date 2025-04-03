<?php
// เชื่อมต่อกับฐานข้อมูล
include('connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
// ตรวจสอบว่าได้รับค่า job_id และ giver_id จาก URL หรือไม่
if (isset($_GET['job_id']) && isset($_GET['giver_id'])) {
    $job_id = $_GET['job_id'];
    $giver_id = $_GET['giver_id'];

    // ฟังก์ชันในการอัปเดตข้อมูล
    function updateJobStatus($job_id, $giver_id) {
        global $pdo;

        // คำสั่ง SQL ที่จะอัปเดต job_status และ giver_id
        $sql = "UPDATE job 
                SET job_status = 2, giver_id = :giver_id 
                WHERE job_id = :job_id";

        // เตรียมคำสั่ง SQL
        $stmt = $pdo->prepare($sql);

        // ผูกค่ากับคำสั่ง SQL
        $stmt->bindParam(':giver_id', $giver_id, PDO::PARAM_INT);
        $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);

        // เรียกใช้คำสั่ง SQL และตรวจสอบผล
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // เรียกใช้ฟังก์ชันเพื่ออัปเดตข้อมูล
    $result = updateJobStatus($job_id, $giver_id);

    if ($result) {
        // รีไดเร็กไปยังหน้าที่คุณต้องการหลังจากอัปเดต
        echo "<script>alert('Provider selected successfully'); window.location.href='emer_job.php';</script>";
    } else {
        echo "<script>alert('Error updating provider'); window.location.href='select_service_provider.php?job_id=" . $job_id . "';</script>";
    }
} else {
    echo "<script>alert('Invalid request'); window.location.href='select_service_provider.php';</script>";
}
?>

