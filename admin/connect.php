<?php
// connect.php

// กำหนดค่าการเชื่อมต่อ
$host = 'localhost'; // ชื่อโฮสต์ (เช่น localhost หรือ IP)
$dbname = 'db_helper'; // ชื่อฐานข้อมูล
$username = 'root'; // ชื่อผู้ใช้ของฐานข้อมูล
$password = ''; // รหัสผ่านของฐานข้อมูล

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// try {
//     $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Connection failed: " . $e->getMessage());
// }
?>