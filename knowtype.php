<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['job_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT type_id FROM job WHERE job_id = :job_id");
            $stmt->bindParam(':job_id', $data['job_id'], PDO::PARAM_INT);
            $stmt->execute();
            $job_type = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($job_type) {
                echo json_encode([
                    "status" => "success",
                    "jobtype" => $job_type['type_id']
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Job not found"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Job ID is required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
