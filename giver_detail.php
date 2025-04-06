<?php
// giver_detail.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'connect.php'; // เชื่อมต่อฐานข้อมูล

// รับ JSON จาก Flutter
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['giver_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing giver_id"]);
    exit();
}

$giver_id = $input['giver_id'];

// ดึงข้อมูล giver
$sql = "SELECT 
    gp.giver_id,
    gp.giver_name,
    gp.giver_bd,
    gp.Specialities AS specialitie,
    gp.giver_img,
    u.phone AS tel,
    COUNT(DISTINCT j.job_id) AS profile_count,
    COUNT(DISTINCT r.review_id) AS giver_review_count
FROM giver_profile gp
INNER JOIN user u ON u.user_id = gp.user_id
LEFT JOIN job j ON j.giver_id = gp.giver_id
LEFT JOIN review r ON r.job_id = j.job_id
WHERE gp.giver_id = ?
GROUP BY gp.giver_id, gp.giver_name, gp.giver_bd, gp.Specialities, gp.giver_img, u.phone";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(1, $giver_id, PDO::PARAM_INT);
$stmt->execute();
$giver = $stmt->fetch(PDO::FETCH_ASSOC);

if ($giver) {
    // ดึงข้อมูลรีวิว
    $sql_reviews = "SELECT rp.profile_name AS name, r.review_rating AS rating, r.review_date AS date, r.review_detail AS detail 
    FROM review r
    INNER JOIN job j ON j.job_id = r.job_id
    INNER JOIN job_profile jp ON j.job_id = jp.job_id
    INNER JOIN receiver_profile rp ON rp.profile_id = jp.profile_id
    WHERE j.giver_id = ?";

    $stmt_reviews = $pdo->prepare($sql_reviews);
    $stmt_reviews->bindParam(1, $giver_id, PDO::PARAM_INT);
    $stmt_reviews->execute();
    $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

    // สร้าง JSON Response
    $response = [
        "status" => "success",
        "data" => [
            "giver_id" => $giver["giver_id"],
            "giver_name" => $giver["giver_name"],
            "giver_img" => $giver["giver_img"],
            "profile_count" => $giver["profile_count"],
            "giver_review_count" => $giver["giver_review_count"],
            "tel" => $giver["tel"],
            "specialitie" => $giver["specialitie"],
            "review" => $reviews
        ]
    ];
} else {
    $response = ["status" => "error", "message" => "Giver not found"];
}

// ส่ง JSON Response กลับไป
echo json_encode($response);

?>
