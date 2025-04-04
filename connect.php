<?php
// connect.php

// กำหนดค่าการเชื่อมต่อ
$host = 'bzk85108cpoxi360fu7u-mysql.services.clever-cloud.com'; // ชื่อโฮสต์ (เช่น localhost หรือ IP)
$dbname = 'bzk85108cpoxi360fu7u'; // ชื่อฐานข้อมูล
$username = 'u0u7c5mh568myzi7'; // ชื่อผู้ใช้ของฐานข้อมูล
$password = 'OGUxOfZ0a9nWFyNoQyDO'; // รหัสผ่านของฐานข้อมูล

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
