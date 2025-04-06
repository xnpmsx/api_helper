<?php
// Include the database connection file
include('connect.php');

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Check if required fields exist
if (!isset($data['giver_id']) || !isset($data['receiver_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing giver_id or receiver_id']);
    exit();
}

$giver_id = $data['giver_id'];
$receiver_id = $data['receiver_id'];

try {
    // Check if the user already liked this giver
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_likes WHERE giver_id = :giver_id AND receiver_id = :receiver_id");
    $stmt->execute(['giver_id' => $giver_id, 'receiver_id' => $receiver_id]);
    $likeExists = $stmt->fetchColumn();

    // Check if like exists and return the appropriate response
    if ($likeExists > 0) {
        echo json_encode(['check' => 'like']);
    } else {
        echo json_encode(['check' => 'notlike']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}


?>
