<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';  // Include your database connection file

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if all required fields are present
    if (!empty($data['job_id']) && !empty($data['giver_id'])) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Prepare SQL query
            $stmt = $pdo->prepare("UPDATE job SET giver_id = :giver_id, job_status = 0 WHERE job_id = :job_id");

            // Bind parameters
            $stmt->bindParam(':job_id', $data['job_id']);
            $stmt->bindParam(':giver_id', $data['giver_id']);

            // Execute query
            $stmt->execute();

            // Commit transaction
            $pdo->commit();

            // Return success response
            echo json_encode(["status" => "success", "message" => "Giver assigned successfully"]);
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        // If required fields are missing
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    }
} else {
    // If the request is not POST
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
