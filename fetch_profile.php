<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['job_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing job_id"]);
        exit;
    }

    $job_id = $data['job_id'];

    try {
        // 🔹 ดึงข้อมูล profile ที่เกี่ยวข้องกับ job_id
        $stmt = $pdo->prepare("
            SELECT 
                rp.profile_name, 
                rp.profile_image, 
                rp.profile_bd, 
                rp.profile_detail
            FROM 
                job_profile jp
            INNER JOIN 
                receiver_profile rp ON jp.profile_id = rp.profile_id
            WHERE 
                jp.job_id = ?
        ");
        $stmt->execute([$job_id]);
        $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($profiles) {
            echo json_encode(["status" => "success", "data" => $profiles]);
        } else {
            echo json_encode(["status" => "error", "message" => "No profiles found for this job_id"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
