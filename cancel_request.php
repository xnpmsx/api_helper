<?php
header("Content-Type: application/json; charset=UTF-8");
require "connect.php"; // เชื่อมต่อฐานข้อมูลด้วย PDO

$data = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบว่าได้รับ job_id และ cancel_detail หรือไม่
if (!isset($data["job_id"]) || !isset($data["cancel_detail"])) {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบถ้วน"]);
    exit();
}
//testy
$job_id = $data["job_id"];
$cancel_detail = $data["cancel_detail"];

try {
    // เพิ่มข้อมูลลงใน request_cancel
    $query = "INSERT INTO request_cancel (job_id, cancel_detail) VALUES (:job, :detail)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":job", $job_id);
    $stmt->bindParam(":detail", $cancel_detail);

    if ($stmt->execute()) {
        // ถ้า insert สำเร็จ ให้ update job_status เป็น 15
        $updateQuery = "UPDATE job SET job_status = 15 WHERE job_id = :job";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->bindParam(":job", $job_id);
        $updateStmt->execute();

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "บันทึกไม่สำเร็จ"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "ข้อผิดพลาด: " . $e->getMessage()]);
}
?>
