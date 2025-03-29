<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // ค้นหาข้อมูลโปรไฟล์จากฐานข้อมูล
        $stmt = $pdo->prepare("SELECT 
            gp.giver_id, 
            gp.giver_name, 
            gp.giver_bd, 
            gp.Specialities, 
            gp.giver_img,
            GROUP_CONCAT(jt.type_name ORDER BY jt.type_name ASC) AS type_name, 
            AVG(r.review_rating) AS avg_review_rate
        FROM giver_profile gp
        INNER JOIN job j ON j.giver_id = gp.giver_id
        INNER JOIN giver_type gt ON gt.giver_id = gp.giver_id
        INNER JOIN job_type jt ON jt.type_id = gt.type_id
        INNER JOIN review r ON r.job_id = j.job_id
        GROUP BY gp.giver_id, gp.giver_name, gp.giver_bd, gp.Specialities, gp.giver_img
        ORDER BY avg_review_rate DESC
        LIMIT 5;");
        
        $stmt->execute();

        // Fetch all the giver profiles
        $givers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($givers) {
            echo json_encode(["status" => "success", "givers" => $givers]);
        } else {
            echo json_encode(["status" => "error", "message" => "No givers found"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
