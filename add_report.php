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
    if (!empty($data['job_id']) && !empty($data['report_topic']) && !empty($data['report_detail']) && isset($data['report_img'])) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Path for storing images (Change to absolute path)
            $uploadDir = __DIR__ . '/assets/report_img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
            }

            // Decode base64 image
            $imageData = $data['report_img'];
            $imageFileName = "report_{$data['job_id']}_{$data['receiver_id']}_" . time() . ".jpg";
            $imageFilePath = $uploadDir . $imageFileName;
            $dbImagePath = "assets/report_img/" . $imageFileName; // Save relative path for database

            // Remove metadata (header) from Base64 string if exists
            if (strpos($imageData, 'data:image') === 0) {
                $imageData = explode(',', $imageData)[1];
            }

            // Debugging logs
            error_log("Saving image to: " . $imageFilePath);

            // Save image to file
            if (file_put_contents($imageFilePath, base64_decode($imageData))) {
                chmod($imageFilePath, 0644); // Set file permissions

                // Prepare SQL statement for insertion
                $stmt = $pdo->prepare("INSERT INTO report (job_id,  report_topic, report_detail, report_img, report_date) 
                                       VALUES (:job_id,  :report_topic, :report_detail, :report_img, NOW())");

                // Bind parameters
                $stmt->bindParam(':job_id', $data['job_id']);
                $stmt->bindParam(':report_topic', $data['report_topic']);
                $stmt->bindParam(':report_detail', $data['report_detail']);
                $stmt->bindParam(':report_img', $dbImagePath); // Save relative path

                // Execute query
                if ($stmt->execute()) {
                    // Get last inserted ID
                    $report_id = $pdo->lastInsertId();
                    $pdo->commit();

                    // Respond with success
                    echo json_encode(["status" => "success", "report_id" => $report_id, "image_path" => $dbImagePath]);
                } else {
                    // Rollback if failed
                    $pdo->rollBack();
                    echo json_encode(["status" => "error", "message" => "Failed to save report"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to save image"]);
            }
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        // If required fields are missing
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
} else {
    // If the request is not POST
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
