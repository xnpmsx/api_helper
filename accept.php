<?php
header("Content-Type: application/json; charset=UTF-8");
require "connect.php"; // ใช้ไฟล์ connect.php ที่เป็น PDO

$data = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบว่ามี job_id หรือไม่
if (!isset($data["job_id"])) {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบถ้วน"]);
    exit();
}

$job_id = $data["job_id"];

try {
    // คำสั่ง SQL ดึงข้อมูล Giver ตาม job_id
    $query = "UPDATE job SET job_status = 2
WHERE job_id = :job_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่พบข้อมูล Giver"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "ข้อผิดพลาด: " . $e->getMessage()]);
}
?>
