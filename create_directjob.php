<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['type_id']) && !empty($data['giver_id'])) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Check if giver has the correct type
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM giver_profile gp
                INNER JOIN giver_type gt ON gt.giver_id = gp.giver_id
                INNER JOIN job_type jt ON jt.type_id = gt.type_id
                WHERE gp.giver_id = :giver_id AND jt.type_id = :type_id
            ");
            $stmt->bindParam(':giver_id', $data['giver_id']);
            $stmt->bindParam(':type_id', $data['type_id']);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                // If no matching giver type found
                echo json_encode(["status" => "error", "message" => "Giver does not accept this job type"]);
                return;
            }

            // Prepare SQL statement for job creation
            $stmt = $pdo->prepare("INSERT INTO job (type_id, giver_id) VALUES (:type_id, :giver_id)");

            // Bind parameters
            $stmt->bindParam(':type_id', $data['type_id']);
            $stmt->bindParam(':giver_id', $data['giver_id']);

            // Execute query
            if ($stmt->execute()) {
                // Get last inserted job_id
                $job_id = $pdo->lastInsertId();

                $pdo->commit();

                echo json_encode(["status" => "success", "job_id" => $job_id]);
            } else {
                $pdo->rollBack();
                echo json_encode(["status" => "error", "message" => "Job creation failed"]);
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
