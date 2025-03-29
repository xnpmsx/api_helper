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
    if (!empty($data['receiver_id']) && !empty($data['profile_name']) && !empty($data['receiver_phone']) && !empty($data['detail']) && !empty($data['bd']) && isset($data['profile_image'])) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Path for storing images (Change to absolute path)
            $uploadDir = __DIR__ . '/assets/profile_img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Decode base64 image
            $imageData = $data['profile_image'];
            $imageFileName = "receiver_{$data['receiver_id']}_" . time() . ".jpg";
            $imageFilePath = $uploadDir . $imageFileName;
            $dbImagePath = "assets/profile_img/" . $imageFileName; // Move up one directory

            // Remove metadata (header) from Base64 string if exists
            if (strpos($imageData, 'data:image') === 0) {
                $imageData = explode(',', $imageData)[1];
            }

            // Debugging logs
            error_log("Saving image to: " . $imageFilePath);

            // Save image to file
            if (file_put_contents($imageFilePath, base64_decode($imageData))) {
                chmod($imageFilePath, 0644); // ให้สิทธิ์การเข้าถึงไฟล์

                // Prepare SQL statement for insertion
                $stmt = $pdo->prepare("INSERT INTO receiver_profile (receiver_id, profile_phone, profile_image, profile_bd, profile_name, profile_detail) 
                                       VALUES (:receiver_id, :profile_phone, :profile_image, :profile_bd, :profile_name, :profile_detail)");

                // Bind parameters
                $stmt->bindParam(':receiver_id', $data['receiver_id']);
                $stmt->bindParam(':profile_phone', $data['receiver_phone']);
                $stmt->bindParam(':profile_image', $dbImagePath); // Save relative path
                $stmt->bindParam(':profile_bd', $data['bd']);
                $stmt->bindParam(':profile_name', $data['profile_name']);
                $stmt->bindParam(':profile_detail', $data['detail']);

                // Execute query
                if ($stmt->execute()) {
                    // Get last inserted ID
                    $user_id = $pdo->lastInsertId();
                    $pdo->commit();

                    // Respond with success
                    echo json_encode(["status" => "success", "user_id" => $user_id, "user_type" => 2, "image_path" => $dbImagePath]);
                } else {
                    // Rollback if failed
                    $pdo->rollBack();
                    echo json_encode(["status" => "error", "message" => "Registration failed"]);
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
