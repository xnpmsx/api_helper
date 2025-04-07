<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';  // Connect to database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['profile_id']) && !empty($data['job_id'])) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Prepare and execute insert
            $stmt = $pdo->prepare("INSERT INTO job_profile (profile_id, job_id) VALUES (:profile, :job_id)");
            $stmt->bindParam(':profile', $data['profile_id'], PDO::PARAM_INT);
            $stmt->bindParam(':job_id', $data['job_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                $pdo->commit();
                echo json_encode(["status" => "success", "message" => "Job updated successfully"]);
            } else {
                $pdo->rollBack();
                echo json_encode(["status" => "error", "message" => "Failed to update job"]);
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
