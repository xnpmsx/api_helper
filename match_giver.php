<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // ตรวจสอบว่ามี job_id หรือไม่
    if (!empty($data['job_id'])) {
        try {
            // ดึงข้อมูล giver_profile ที่มี addon ตรงกับ job_id
            $stmt = $pdo->prepare("
                        WITH job_addon_count AS (
    SELECT job_id, COUNT(addon_id) AS total_job_addons
    FROM job_addon
    WHERE job_id = :job_id
    GROUP BY job_id
),
giver_addon_count AS (
    SELECT ga.giver_id, COUNT(ga.addon_id) AS total_giver_addons
    FROM giver_addon ga
    INNER JOIN job_addon ja ON ja.addon_id = ga.addon_id
    WHERE ja.job_id = :job_id
    GROUP BY ga.giver_id
),
missing_addons AS (
    SELECT gp.giver_id, a.addon_name
    FROM giver_profile gp
    CROSS JOIN job_addon ja
    LEFT JOIN giver_addon ga ON ga.giver_id = gp.giver_id AND ga.addon_id = ja.addon_id
    INNER JOIN addon a ON ja.addon_id = a.addon_id
    WHERE ja.job_id = :job_id AND ga.addon_id IS NULL
),
giver_reviews AS (
    SELECT j.giver_id, AVG(r.review_rating) AS avg_review_rating
    FROM review r
    INNER JOIN job j ON r.job_id = j.job_id
    GROUP BY j.giver_id
)
SELECT gp.giver_id, gp.giver_name, gp.giver_img,
    COALESCE(gac.total_giver_addons, 0) AS giver_addon_count, 
    jac.total_job_addons,
    ROUND(COALESCE(gac.total_giver_addons, 0) * 100.0 / jac.total_job_addons, 2) AS match_percentage,
    COALESCE(GROUP_CONCAT(m.addon_name SEPARATOR ', '), '') AS missing_addons,
    COALESCE(gr.avg_review_rating, 0) AS avg_review_rating
FROM giver_profile gp
LEFT JOIN giver_addon_count gac ON gp.giver_id = gac.giver_id
CROSS JOIN job_addon_count jac
LEFT JOIN missing_addons m ON gp.giver_id = m.giver_id
LEFT JOIN giver_reviews gr ON gp.giver_id = gr.giver_id
-- Join with giver_type to ensure the type_id matches the job type_id
INNER JOIN giver_type gt ON gp.giver_id = gt.giver_id
-- Ensure the giver_status is 1
WHERE gt.type_id = (SELECT type_id FROM job WHERE job_id = :job_id) 
AND gp.giver_status = 1
-- Exclude givers who have a job on the same date and time as the current job
AND NOT EXISTS (
    SELECT 1 
    FROM job j_conflict
    WHERE j_conflict.giver_id = gp.giver_id
    AND j_conflict.job_date = (SELECT job_date FROM job WHERE job_id = :job_id)
    AND j_conflict.job_time = (SELECT job_time FROM job WHERE job_id = :job_id)
)
GROUP BY gp.giver_id, gp.giver_name, jac.total_job_addons, gac.total_giver_addons, gr.avg_review_rating
ORDER BY match_percentage DESC, giver_addon_count DESC, avg_review_rating DESC, gp.giver_id ASC
LIMIT 10;

            ");

            $stmt->bindParam(':job_id', $data['job_id'], PDO::PARAM_INT);
            $stmt->execute();
            $addons = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($addons)) {
                // ส่งข้อมูลกลับไป
                echo json_encode([
                    "status" => "success",
                    "data" => $addons
                ]);
            } else {
                // ไม่พบข้อมูลที่ตรงกับ job_id
                echo json_encode(["status" => "error", "message" => "No matching giver profiles found for this job_id"]);
            }
        } catch (PDOException $e) {
            // แจ้ง error จากฐานข้อมูล
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        // ไม่มี job_id ในคำขอ
        echo json_encode(["status" => "error", "message" => "job_id is required"]);
    }
} else {
    // รับเฉพาะ POST เท่านั้น
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
