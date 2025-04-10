<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['phone']) && !empty($data['password']) && !empty($data['email'])) {
        try {
            // Hash the password for security
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            // Start transaction
            $pdo->beginTransaction();

            // Prepare SQL statement for user registration
            $stmt = $pdo->prepare("INSERT INTO user (phone, password, email, user_type) 
                                   VALUES (:phone, :password, :email, 2)");

            // Bind parameters
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':email', $data['email']);

            // Execute query
            if ($stmt->execute()) {
                // Get last inserted user_id
                $user_id = $pdo->lastInsertId();

                // Insert into receiver table
                $stmt2 = $pdo->prepare("INSERT INTO receiver (user_id) VALUES (:user_id)");
                $stmt2->bindParam(':user_id', $user_id);
                $stmt2->execute();

                // Commit transaction
                $pdo->commit();

                echo json_encode(["status" => "success", "user_id" => $user_id, "user_type" => 2]);
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
