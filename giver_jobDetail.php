<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['giver_id'])) {
        try {
            // Query to get job details based on receiver_id
            $stmt = $pdo->prepare("
                SELECT 
    j.job_appointment, 
    j.job_target,
    GROUP_CONCAT(rp.profile_phone SEPARATOR ', ') AS profile_phones, 
    GROUP_CONCAT(rp.profile_detail SEPARATOR ', ') AS profile_details,
    j.job_id, 
    j.job_date, 
    j.job_time, 
    j.job_detail, 
    j.type_id, 
    jt.type_name, 
    j.job_status, 
    GROUP_CONCAT(rp.profile_name SEPARATOR ', ') AS profile_names,
    GROUP_CONCAT(rp.profile_image SEPARATOR ', ') AS profile_images,
    GROUP_CONCAT(a.addon_name SEPARATOR ', ') AS addon_names
FROM job j 
INNER JOIN job_type jt ON j.type_id = jt.type_id
INNER JOIN job_profile jp ON j.job_id = jp.job_id 
INNER JOIN receiver_profile rp ON jp.profile_id = rp.profile_id 
LEFT JOIN job_addon ja ON ja.job_id = j.job_id
LEFT JOIN addon a ON a.addon_id = ja.addon_id
WHERE j.giver_id = :giver_id
AND j.job_status = :status
GROUP BY j.job_id;


            ");
            $stmt->bindParam(':giver_id', $data['giver_id']);
            $stmt->bindParam(':status', $data['job_status']);
            $stmt->execute();
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($jobs) {
                echo json_encode(["status" => "success", "jobs" => $jobs]);
            } else {
                echo json_encode(["status" => "error", "message" => "No jobs found for this receiver_id"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "receiver_id is required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
