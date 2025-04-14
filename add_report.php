<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Check required fields (no need to check image)
    if (!empty($data['job_id']) && !empty($data['report_topic']) && !empty($data['report_detail'])) {
        try {
            $pdo->beginTransaction();

            $imagePathToSave = null;

            // If image is provided, process it
            if (!empty($data['report_img'])) {
                $uploadDir = __DIR__ . '/assets/report_img/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $imageData = $data['report_img'];

                if (strpos($imageData, 'data:image') === 0) {
                    $imageData = explode(',', $imageData)[1];
                }

                $imageFileName = "report_{$data['job_id']}_" . time() . ".jpg";
                $imageFilePath = $uploadDir . $imageFileName;
                $imagePathToSave = "assets/report_img/" . $imageFileName;

                if (!file_put_contents($imageFilePath, base64_decode($imageData))) {
                    echo json_encode(["status" => "error", "message" => "Failed to save image"]);
                    exit;
                }

                chmod($imageFilePath, 0644);
            }

            // Prepare SQL with or without image
            if ($imagePathToSave) {
                $stmt = $pdo->prepare("INSERT INTO report (job_id, report_topic, report_detail, report_img, report_date) 
                                       VALUES (:job_id, :report_topic, :report_detail, :report_img, NOW())");
                $stmt->bindParam(':report_img', $imagePathToSave);
            } else {
                $stmt = $pdo->prepare("INSERT INTO report (job_id, report_topic, report_detail, report_date) 
                                       VALUES (:job_id, :report_topic, :report_detail, NOW())");
            }

            // Bind common values
            $stmt->bindParam(':job_id', $data['job_id']);
            $stmt->bindParam(':report_topic', $data['report_topic']);
            $stmt->bindParam(':report_detail', $data['report_detail']);

            if ($stmt->execute()) {
                $report_id = $pdo->lastInsertId();
                $pdo->commit();
                echo json_encode([
                    "status" => "success"
                ]);
            } else {
                $pdo->rollBack();
                echo json_encode(["status" => "error", "message" => "Failed to save report"]);
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "job_id, report_topic, and report_detail are required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
