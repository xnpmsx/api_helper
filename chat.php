<?php
header("Content-Type: application/json; charset=UTF-8");
require 'connect.php'; // ใช้การเชื่อมต่อจาก connect.php

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["job_id"])) {
    echo json_encode(["status" => "error", "message" => "Missing job_id"]);
    exit;
}

$job_id = $data["job_id"];

if (isset($data["message"]) && isset($data["user_id"])) {
    // กรณีส่งข้อความใหม่
    $user_id = $data["user_id"];
    $message = $data["message"];

    try {
        $stmt = $pdo->prepare("INSERT INTO chat (job_id, user_id, chat_text, chat_datetime) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$job_id, $user_id, $message]);

        echo json_encode(["status" => "success", "message" => "Message sent"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Failed to send message", "error" => $e->getMessage()]);
    }
} else {
    // กรณีดึงข้อความแชท
    try {
        $stmt = $pdo->prepare("SELECT * FROM chat WHERE job_id = ? ORDER BY chat_datetime ASC");
        $stmt->execute([$job_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "messages" => $messages]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Failed to fetch messages", "error" => $e->getMessage()]);
    }
}
?>
