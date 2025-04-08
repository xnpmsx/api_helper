<?php
include('connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['giver_id']) && isset($_POST['action'])) {
    $giver_id = $_POST['giver_id'];
    $action = $_POST['action'];

    // กำหนดค่า giver_status
    if ($action == 'accept') {
        $giver_status = 1;
    } elseif ($action == 'reject') {
        $giver_status = 5;
    }

    // อัปเดตสถานะใน giver_profile
    $sql = "UPDATE giver_profile SET giver_status = :giver_status WHERE giver_id = :giver_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':giver_status', $giver_status, PDO::PARAM_INT);
    $stmt->bindParam(':giver_id', $giver_id, PDO::PARAM_INT);

    if ($stmt->execute()) {

        // ถ้า action เป็น accept และมี type_ids ที่ส่งมา
        if ($action == 'accept' && isset($_POST['type_ids']) && is_array($_POST['type_ids'])) {
            foreach ($_POST['type_ids'] as $type_id) {
                $insertSql = "INSERT INTO giver_type (giver_id, type_id) VALUES (:giver_id, :type_id)";
                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->bindParam(':giver_id', $giver_id, PDO::PARAM_INT);
                $insertStmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
                $insertStmt->execute();
            }
        }

        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error updating status.";
    }
} else {
    echo "Invalid request.";
}
?>
