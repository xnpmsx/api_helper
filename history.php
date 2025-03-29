<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['giver_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing giver_id"]);
        exit;
    }

    $giver_id = $data['giver_id'];

    try {
        // ดึงข้อมูลงานที่ 'เสร็จสิ้น' (job_status = 3)
        $stmtCompleted = $pdo->prepare("SELECT * FROM job WHERE giver_id = ? AND job_status = 5 ORDER BY job_date DESC");
        $stmtCompleted->execute([$giver_id]);
        $completedJobs = $stmtCompleted->fetchAll(PDO::FETCH_ASSOC);

        // ดึงข้อมูลงานที่ 'ยกเลิก' (job_status = 5)
        $stmtCancelled = $pdo->prepare("SELECT * FROM job WHERE giver_id = ? AND job_status = 10 ORDER BY job_date DESC");
        $stmtCancelled->execute([$giver_id]);
        $cancelledJobs = $stmtCancelled->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "completed_jobs" => $completedJobs,
            "cancelled_jobs" => $cancelledJobs
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
