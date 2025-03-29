<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (!isset($data['profile_id'], $data['profile_name'], $data['profile_phone'], $data['profile_detail'], $data['profile_bd'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    $profile_id = $data['profile_id'];
    $profile_name = $data['profile_name'];
    $profile_phone = $data['profile_phone'];
    $profile_detail = $data['profile_detail'];
    $profile_bd = $data['profile_bd'];
    $profile_image = $data['profile_image'] ?? ''; // Optional image

    $imagePath = ""; // Default: empty image path

    if (!empty($profile_image)) {
        // Directory to save images
        $uploadDir = __DIR__ . '/assets/profile_img/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate a unique filename
        $imageFileName = "profile_{$profile_id}_" . time() . ".jpg";
        $imageFilePath = $uploadDir . $imageFileName;
        $dbImagePath = "assets/profile_img/" . $imageFileName; // Path to store in DB

        // Remove metadata (header) if exists
        if (strpos($profile_image, 'data:image') === 0) {
            $profile_image = explode(',', $profile_image)[1];
        }

        // Decode and save image
        if (file_put_contents($imageFilePath, base64_decode($profile_image))) {
            $imagePath = $dbImagePath;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save image"]);
            exit;
        }
    }

    try {
        if (!empty($imagePath)) {
            // Update with image
            $stmt = $pdo->prepare("UPDATE receiver_profile 
                SET profile_name = :profile_name, profile_phone = :profile_phone, 
                    profile_detail = :profile_detail, profile_bd = :profile_bd, 
                    profile_image = :profile_image 
                WHERE profile_id = :profile_id");

            $stmt->bindParam(':profile_image', $imagePath, PDO::PARAM_STR);
        } else {
            // Update without changing image
            $stmt = $pdo->prepare("UPDATE receiver_profile 
                SET profile_name = :profile_name, profile_phone = :profile_phone, 
                    profile_detail = :profile_detail, profile_bd = :profile_bd
                WHERE profile_id = :profile_id");
        }

        $stmt->bindParam(':profile_id', $profile_id, PDO::PARAM_INT);
        $stmt->bindParam(':profile_name', $profile_name, PDO::PARAM_STR);
        $stmt->bindParam(':profile_phone', $profile_phone, PDO::PARAM_STR);
        $stmt->bindParam(':profile_detail', $profile_detail, PDO::PARAM_STR);
        $stmt->bindParam(':profile_bd', $profile_bd, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update profile"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
