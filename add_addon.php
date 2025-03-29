<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Fix: Correct the empty check by properly using the AND operator
    if (!empty($data['addon_id']) && !empty($data['job_id'])) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Prepare SQL statement for adding addon to job
            $stmt = $pdo->prepare("INSERT INTO job_addon (job_id, addon_id) VALUES (:job_id, :addon_id)");

            // Bind parameters
            $stmt->bindParam(':job_id', $data['job_id']);
            $stmt->bindParam(':addon_id', $data['addon_id']);

            // Execute query
            if ($stmt->execute()) {
                // Commit transaction
                $pdo->commit();
                echo json_encode(["status" => "success"]);
            } else {
                // Rollback in case of failure
                $pdo->rollBack();
                echo json_encode(["status" => "error", "message" => "Registration failed"]);
            }
        } catch (PDOException $e) {
            // Rollback in case of an exception
            $pdo->rollBack();
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        // Return error if required fields are missing
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
} else {
    // Return error if the request method is not POST
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
