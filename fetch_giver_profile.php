<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['giver_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing giver_id"]);
        exit;
    }

    $giver_id = $data['giver_id'];

    try {
        // 🔹 ค้นหาข้อมูลโปรไฟล์จากฐานข้อมูล
        $stmt = $pdo->prepare("SELECT giver_id, giver_name, giver_bd, Specialities, giver_img FROM giver_profile WHERE giver_id = ?");
        $stmt->execute([$giver_id]);
        $giver = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($giver) {
            echo json_encode(["status" => "success", "data" => $giver]);
        } else {
            echo json_encode(["status" => "error", "message" => "Giver profile not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
