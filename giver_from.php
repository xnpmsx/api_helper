<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// รับ JSON ที่ส่งมา
$data = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบว่า JSON ที่ส่งมามีค่า giver_id หรือไม่
// if (!isset($data["giver_id"])) {
//     echo json_encode(["status" => "error", "message" => "Missing giver_id"]);
//     exit();
// }

// ตรวจสอบว่า JSON ถูกต้องหรือไม่
if (json_last_error() != JSON_ERROR_NONE) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
    exit();
}

// เชื่อมต่อฐานข้อมูล
include 'connect.php';  // Include your database connection file

try {
    // Prepare the SQL query
    $stmt = $pdo->prepare("UPDATE giver_verify SET item1 = :q1, item2 = :q2, item3 = :q3, item4 = :q4, item5 = :q5 WHERE giver_id = :giver_id");

    // Bind parameters
    $stmt->bindParam(':giver_id', $data["giver_id"]);
    $stmt->bindParam(':q1', $data["q1"]);
    $stmt->bindParam(':q2', $data["q2"]);
    $stmt->bindParam(':q3', $data["q3"]);
    $stmt->bindParam(':q4', $data["q4"]);
    $stmt->bindParam(':q5', $data["q5"]);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Insert failed"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}

// Close the statement and the database connection
$stmt->closeCursor();
$pdo = null;
?>
