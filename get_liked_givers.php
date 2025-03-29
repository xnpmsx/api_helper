<?php
include('connect.php');

$data = json_decode(file_get_contents("php://input"));

if(isset($data->receiver_id)) {
    try {
        $stmt = $pdo->prepare("
            SELECT gp.giver_id, gp.giver_name, u.email, gp.giver_img,gp.giver_bd,gp.Specialities 
            FROM user_likes l
            JOIN giver_profile gp ON l.giver_id = gp.giver_id
            JOIN user u ON u.user_id = gp.user_id
            WHERE l.receiver_id = :receiver_id
        ");
        $stmt->bindParam(':receiver_id', $data->receiver_id);
        $stmt->execute();
        $givers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'givers' => $givers]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing receiver_id']);
}

$pdo = null;
?>
