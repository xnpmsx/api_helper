<?php
header("Content-Type: application/json; charset=UTF-8");
require "connect.php"; // ใช้ไฟล์ connect.php ที่เป็น PDO

$data = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบว่าข้อมูลครบถ้วน
if (!isset($data["job_id"], $data["rating"], $data["review"])) {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบถ้วน"]);
    exit();
}

$job_id = $data["job_id"];
$rating = $data["rating"];
$review = $data["review"];

try {
    $query = "INSERT INTO review (job_id, review_rating, review_date, review_detail) VALUES (:job_id, :rating, NOW(), :review)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindParam(":rating", $rating, PDO::PARAM_INT);
    $stmt->bindParam(":review", $review, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $stmt2 = $pdo->prepare("UPDATE job SET job_status = 5 WHERE job_id=:job_id");
        $stmt2->bindParam(":job_id", $job_id, PDO::PARAM_INT);
        $stmt2->execute();
        echo json_encode(["status" => "success", "message" => "บันทึกรีวิวสำเร็จ"]);
    } else {
        echo json_encode(["status" => "error", "message" => "บันทึกรีวิวไม่สำเร็จ"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "ข้อผิดพลาด: " . $e->getMessage()]);
}

?>
