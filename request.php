<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['job_id']) && !empty($data['detail'])) {
        try {
            $pdo->beginTransaction();

            // Prepare SQL statement for inserting request
            $stmt = $pdo->prepare("INSERT INTO request (job_id, request_detail) VALUES (:job_id, :detail)");

            // Bind parameters
            $stmt->bindParam(':job_id', $data['job_id']);
            $stmt->bindParam(':detail', $data['detail']);

            // Execute query
            if ($stmt->execute()) {
                $pdo->commit();
                echo json_encode(["status" => "success", "job_id" => $data['job_id']]);
            } else {
                $pdo->rollBack();
                echo json_encode(["status" => "error", "message" => "Insertion failed"]);
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Database error: " . $e->getMessage()); // Logs error for debugging
            echo json_encode(["status" => "error", "message" => "Database error"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
