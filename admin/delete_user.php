<?php
require 'connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
if (!isset($_GET['user_id']) || !isset($_GET['type'])) {
    die("User ID and type are required.");
}

$user_id = $_GET['user_id'];
$type = $_GET['type'];

if ($type == 1) {
    // Delete giver profile
    $sql = "DELETE gp FROM giver_profile gp INNER JOIN user u ON gp.user_id = u.user_id WHERE u.user_id = :user_id";
} else {
    // Delete receiver profile
    $sql = "DELETE rp FROM receiver_profile rp INNER JOIN receiver r ON rp.receiver_id = r.receiver_id INNER JOIN user u ON r.user_id = u.user_id WHERE u.user_id = :user_id";
}

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);

// Delete user account
$sql = "DELETE FROM user WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);

echo "<script>alert('User deleted successfully!'); window.location.href='user_management.php?type=$type';</script>";
?>
