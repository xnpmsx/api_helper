<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if receiver_id is provided
    if (!empty($data['receiver_id'])) {
        try {
            // Query to get all receiver profiles for the given receiver_id
            $stmt = $pdo->prepare("SELECT * FROM receiver_profile WHERE receiver_id = :receiver_id");
            $stmt->bindParam(':receiver_id', $data['receiver_id'], PDO::PARAM_INT);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($users) {
                // Return the list of profiles
                echo json_encode([
                    "status" => "success",
                    "profiles" => $users  // Return all profiles as an array
                ]);
            } else {
                // No user found for the provided receiver_id
                echo json_encode(["status" => "error", "message" => "Receiver profiles not found"]);
            }
        } catch (PDOException $e) {
            // Handle database connection errors
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        // If receiver_id is not provided
        echo json_encode(["status" => "error", "message" => "Receiver ID is required"]);
    }
} else {
    // Only POST requests are allowed
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
