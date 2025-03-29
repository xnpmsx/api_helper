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
    if (!empty($data['job_id']) && !empty($data['appoint']) && !empty($data['target']) && !empty($data['date']) && !empty($data['profile_id']) && !empty($data['time'])) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // If 'detail' is not provided, set it to empty string
            $detail = isset($data['detail']) ? $data['detail'] : '';

            // Prepare SQL statement for updating the job record
            $stmt = $pdo->prepare("UPDATE job SET job_appointment = :appoint, job_target = :target, job_date = :date, job_time = :time, job_detail = :detail WHERE job_id = :job_id");

            // Bind parameters
            $stmt->bindParam(':job_id', $data['job_id']);
            $stmt->bindParam(':appoint', $data['appoint']);
            $stmt->bindParam(':target', $data['target']);
            $date = DateTime::createFromFormat('d/m/Y', $data['date']);
$formattedDate = $date ? $date->format('Y-m-d') : null;
            $stmt->bindParam(':date', $formattedDate);
            $stmt->bindParam(':time', $data['time']);
            $stmt->bindParam(':detail', $detail);

            // Execute query
            if ($stmt->execute()) {
                
                $pdo->commit();

                // Insert into receiver table
                $stmt2 = $pdo->prepare("INSERT INTO job_profile (profile_id,job_id) VALUES (:profile,:job_id)");
                $stmt2->bindParam(':job_id', $data['job_id']);
                $stmt2->bindParam(':profile', $data['profile_id']);
                $stmt2->execute();
                echo json_encode(["status" => "success", "message" => "Job updated successfully"]);
            } else {
                // Rollback if failed
                $pdo->rollBack();
                echo json_encode(["status" => "error", "message" => "Failed to update job"]);
            }
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
