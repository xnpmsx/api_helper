<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['receiver_id']) && isset($data['tab'])) {
        try {
            // กำหนด job_status ตามค่า tab
            $jobStatuses = [];
            if ($data['tab'] == 0) {
                $jobStatuses = [0, 1, 2];
            } 
            elseif ($data['tab'] == 1) {
                $jobStatuses = [3];
            } 
            elseif ($data['tab'] == 2) {
                $jobStatuses = [10,15];
            }
            elseif ($data['tab'] == 3) {
                $jobStatuses = [5];
            }

            // Query เพื่อดึงข้อมูลงานตาม receiver_id และ job_status
            $query = "
                SELECT 
                    j.job_id, 
                    j.job_date, 
                    j.job_time, 
                    j.job_detail, 
                    j.type_id, 
                    jt.type_name, 
                    j.job_status, 
                    rp.profile_name,
                    SUM(a.addon_price) AS total_addon_price
                FROM 
                    job j
                INNER JOIN 
                    job_type jt ON j.type_id = jt.type_id
                INNER JOIN 
                    job_profile jp ON j.job_id = jp.job_id
                INNER JOIN 
                    receiver_profile rp ON jp.profile_id = rp.profile_id
                INNER JOIN 
                    job_addon ja ON ja.job_id = j.job_id
                INNER JOIN 
                    addon a ON a.addon_id = ja.addon_id
                WHERE 
                    rp.receiver_id = :receiver_id
                    AND j.job_status IN (" . implode(",", $jobStatuses) . ")
                GROUP BY 
                    j.job_id, j.job_date, j.job_time, j.job_detail, j.type_id, jt.type_name, j.job_status, rp.profile_name
            ";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':receiver_id', $data['receiver_id']);
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
        echo json_encode(["status" => "error", "message" => "receiver_id and tab are required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
