<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['username']) && !empty($data['password'])) {
        try {
            // Query to check username and password
            $stmt = $pdo->prepare("SELECT user_id, user_type FROM user WHERE phone = :username AND password = :password");
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':password', $data['password']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Base response
                $response = [
                    "status" => "success",
                    "user_id" => $user['user_id'],
                    "user_type" => $user['user_type']
                ];

                // Handle user type 1 (Giver)
                if ($user['user_type'] == 1) {
                    $stmt2 = $pdo->prepare("SELECT giver_id FROM giver_profile WHERE user_id = :user_id");
                    $stmt2->bindParam(':user_id', $user['user_id']);
                    $stmt2->execute();
                    $giver = $stmt2->fetch(PDO::FETCH_ASSOC);

                    // Add giver info to the response
                    if ($giver) {
                        $response['giver_id'] = $giver['giver_id'];
                    } else {
                        $response['message'] = 'Giver profile not found';
                    }
                }

                // Handle user type 2 (Receiver)
                elseif ($user['user_type'] == 2) {
                    $stmt2 = $pdo->prepare("SELECT receiver_id FROM receiver WHERE user_id = :user_id");
                    $stmt2->bindParam(':user_id', $user['user_id']);
                    $stmt2->execute();
                    $receiver = $stmt2->fetch(PDO::FETCH_ASSOC);

                    // Add receiver info to the response
                    if ($receiver) {
                        $response['receiver_id'] = $receiver['receiver_id'];
                    } else {
                        $response['message'] = 'Receiver profile not found';
                    }
                }

                // Send final response
                echo json_encode($response);

            } else {
                echo json_encode(["status" => "error", "message" => "Invalid username or password"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Username or password missing"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
