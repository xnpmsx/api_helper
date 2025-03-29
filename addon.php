<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // ตรวจสอบว่ามี type_id หรือไม่
    if (!empty($data['type_id'])) {
        try {
            // ดึงข้อมูล Addon ที่ตรงกับ type_id
            $stmt = $pdo->prepare("SELECT * FROM addon WHERE type_id = :type_id");
            $stmt->bindParam(':type_id', $data['type_id'], PDO::PARAM_INT);
            $stmt->execute();
            $addons = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($addons)) {
                // ส่งข้อมูลกลับไป
                echo json_encode([
                    "status" => "success",
                    "addons" => $addons
                ]);
            } else {
                // ไม่พบข้อมูล
                echo json_encode(["status" => "error", "message" => "No addons found for this type_id"]);
            }
        } catch (PDOException $e) {
            // แจ้ง error จากฐานข้อมูล
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        // ไม่มี type_id ในคำขอ
        echo json_encode(["status" => "error", "message" => "type_id is required"]);
    }
} else {
    // รับเฉพาะ POST เท่านั้น
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
