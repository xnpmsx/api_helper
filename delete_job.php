<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['job_id']) )  {
        try {
            // Hash the password for security
            // Start transaction
            $pdo->beginTransaction();
            // Prepare SQL statement for user registration
            $stmt = $pdo->prepare("DELETE FROM job  
                                   WHERE job_id=:job_id");

            // Bind parameters
            $stmt->bindParam(':job_id', $data['job_id']);

            // Execute query
            if ($stmt->execute()) {
                // Get last inserted user_id

                $pdo->commit();

                echo json_encode(["status" => "success",]);
            } else {
                $pdo->rollBack();
                echo json_encode(["status" => "error", "message" => "Registration failed"]);
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
