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
    $query = "SELECT gp.giver_id,gp.giver_name, u.phone,u.email,gp.giver_img FROM job j
INNER JOIN giver_profile gp ON gp.giver_id=j.giver_id
INNER JOIN user u ON u.user_id = gp.user_id
WHERE j.job_id = :job_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(["status" => "success", "giver" => $result]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่พบข้อมูล Giver"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "ข้อผิดพลาด: " . $e->getMessage()]);
}
?>
