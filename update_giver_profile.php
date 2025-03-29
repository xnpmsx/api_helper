<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['giver_id'], $data['giver_name'], $data['giver_bd'], $data['Specialities'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    $giver_id = $data['giver_id'];
    $giver_name = $data['giver_name'];
    $giver_bd = $data['giver_bd'];
    $specialities = $data['Specialities'];
    $giver_img = $data['giver_img'] ?? '';

    $imagePath = '';

    // 🔹 ตรวจสอบว่ามีการอัปโหลดภาพหรือไม่
    if (!empty($giver_img)) {
        $uploadDir = __DIR__ . '/assets/giver_img/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 🔹 แปลง Base64 เป็นไฟล์ภาพ
        $imageFileName = "giver_{$giver_id}_" . time() . ".jpg";
        $imageFilePath = $uploadDir . $imageFileName;
        $dbImagePath = "assets/giver_img/" . $imageFileName; // พาธที่บันทึกลงฐานข้อมูล

        // ลบ metadata (header) ออกจาก Base64 ถ้ามี
        if (strpos($giver_img, 'data:image') === 0) {
            $giver_img = explode(',', $giver_img)[1];
        }

        // 🔹 บันทึกไฟล์ภาพ
        file_put_contents($imageFilePath, base64_decode($giver_img));
        $imagePath = $dbImagePath;
    }

    // 🔹 อัปเดตข้อมูลในฐานข้อมูล
    $stmt = $pdo->prepare("UPDATE giver_profile SET giver_name = ?, giver_bd = ?, Specialities = ?, giver_img = ? WHERE giver_id = ?");
    $stmt->execute([$giver_name, $giver_bd, $specialities, $imagePath, $giver_id]);

    echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
