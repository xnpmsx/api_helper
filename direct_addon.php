<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // ตรวจสอบว่ามี type_id และ giver_id หรือไม่
    if (!empty($data['type_id']) && !empty($data['giver_id'])) {
        try {
            // ดึงข้อมูล Addon ที่ตรงกับ type_id และ giver_id
            $stmt = $pdo->prepare("SELECT a.*
FROM addon a
JOIN giver_addon ga ON a.addon_id = ga.addon_id
WHERE a.type_id = :type_id
AND ga.giver_id = :giver_id");

            // Binding parameter สำหรับ type_id และ giver_id
            $stmt->bindParam(':type_id', $data['type_id'], PDO::PARAM_INT);
            $stmt->bindParam(':giver_id', $data['giver_id'], PDO::PARAM_INT);
            $stmt->execute();

            // ดึงข้อมูล Addon ทั้งหมดที่ตรงเงื่อนไข
            $addons = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($addons)) {
                // ส่งข้อมูลกลับไป
                echo json_encode([
                    "status" => "success",
                    "addons" => $addons
                ]);
            } else {
                // ถ้าไม่มี Addon ที่ตรงเงื่อนไข
                echo json_encode(["status" => "error", "message" => "No addons found for this type_id and giver_id"]);
            }
        } catch (PDOException $e) {
            // แจ้ง error จากฐานข้อมูล
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        // หากไม่มี type_id หรือ giver_id ในคำขอ
        echo json_encode(["status" => "error", "message" => "Both type_id and giver_id are required"]);
    }
} else {
    // รับเฉพาะ POST เท่านั้น
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
