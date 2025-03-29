<?php
include('connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
if (isset($_POST['giver_id']) && isset($_POST['job_id']) && isset($_POST['request_id'])) {
    $giver_id = $_POST['giver_id'];
    $job_id = $_POST['job_id'];
    $request_id = $_POST['request_id'];

    try {
        // Start a transaction to ensure both updates happen together
        $pdo->beginTransaction();

        // Update the job table with the new giver_id
        $sql1 = "UPDATE job SET giver_id = :giver_id WHERE job_id = :job_id";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindParam(':giver_id', $giver_id, PDO::PARAM_INT);
        $stmt1->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt1->execute();

        // Update the request_status to 1 (indicating the request has been updated)
        $sql2 = "UPDATE request SET request_status = 1 WHERE request_id = :request_id";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt2->execute();

        // Commit the transaction
        $pdo->commit();

        // Redirect to a success page or the request details page
        header("Location: request.php");
        exit();
    } catch (PDOException $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Error: Missing parameters";
}
?>
