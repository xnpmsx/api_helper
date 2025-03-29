<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response and CORS
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Include database connection
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Check for required fields
    if (!empty($data['phone']) && !empty($data['password']) && !empty($data['email']) 
        && !empty($data['specialities']) && !empty($data['giver_fname']) && !empty($data['giver_bd']) && !empty($data['image'])) {
        
        try {
            // Hash the password
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            // Begin transaction
            $pdo->beginTransaction();

            // Directory for image upload
            $uploadDir = __DIR__ . '/assets/giver_img/';

            // Check if upload directory exists, if not, create it
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Handle Base64 image
            $imageData = $data['image'];
            $imageFileName = "giver_" . time() . ".jpg";
            $imageFilePath = $uploadDir . $imageFileName;
            $dbImagePath = "assets/giver_img/" . $imageFileName;

            // Remove metadata from Base64 string if exists
            if (strpos($imageData, 'data:image') === 0) {
                $imageData = explode(',', $imageData)[1];
            }

            // Check if image data is valid
            if ($imageData == "") {
                throw new Exception("Invalid Base64 image data.");
            }

            // Save the image to the directory
            if (file_put_contents($imageFilePath, base64_decode($imageData))) {
                chmod($imageFilePath, 0644); // Set file permissions
            } else {
                throw new Exception("Image saving failed.");
            }

            // Insert into user table
            $stmt = $pdo->prepare("INSERT INTO user (phone, password, email, user_type) 
                                   VALUES (:phone, :password, :email, 1)");
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':email', $data['email']);

            if (!$stmt->execute()) {
                error_log("User Insert Error: " . json_encode($stmt->errorInfo()));
                throw new Exception("User insertion failed.");
            }

            // Get last inserted user ID
            $user_id = $pdo->lastInsertId();

            // Insert into giver_profile table
            $stmt2 = $pdo->prepare("INSERT INTO giver_profile (user_id, giver_name, giver_bd, Specialities, giver_img) 
                                    VALUES (:user_id, :fullname, :bd, :s, :giver_img)");
            $stmt2->bindParam(':user_id', $user_id);
            $stmt2->bindParam(':fullname', $data['giver_fname']);
            // Convert DD/MM/YYYY to YYYY-MM-DD
$date = DateTime::createFromFormat('d/m/Y', $data['giver_bd']);
$formattedDate = $date ? $date->format('Y-m-d') : null;

$stmt2->bindParam(':bd', $formattedDate);
            $stmt2->bindParam(':s', $data['specialities']);
            $stmt2->bindParam(':giver_img', $dbImagePath);

            if (!$stmt2->execute()) {
                error_log("Giver Profile Insert Error: " . json_encode($stmt2->errorInfo()));
                throw new Exception("Giver profile insertion failed.");
            }

            // Get last inserted giver ID
            $giver_id = $pdo->lastInsertId();

            // Commit the transaction
            $pdo->commit();

            // Return success response
            echo json_encode(["status" => "success", "user_id" => $user_id, "giver_id" => $giver_id, "user_type" => 1]);

        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            error_log("Error: " . $e->getMessage());
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}

?>
