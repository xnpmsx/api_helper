<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
include('connect.php');
include('sidebar.php'); // รวม Sidebar

// ดึงข้อมูลจากฐานข้อมูล (เพิ่ม SQL ของคุณที่นี่)
$sql = "SELECT * FROM request r
INNER JOIN job j ON j.job_id = r.job_id 
INNER JOIN job_type jt ON j.type_id = jt.type_id
WHERE request_status = 0"; // ใส่ SQL ของคุณเอง
$stmt = $pdo->query($sql);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>คำร้องเเจ้งขอเปลี่ยนผู้ให้บริการ</h2>
    <hr>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>ประเภทงาน</th>
                <th>เหตุผล</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
            <tr>
                <td><?php echo htmlspecialchars($report['request_id']); ?></td>
                <td><?php echo htmlspecialchars($report['type_name']); ?></td>
                <td><?php echo htmlspecialchars($report['request_detail']); ?></td>
                <td>
                    <!-- ปุ่ม ดูรายละเอียด -->
                    <a href="view_request_detail.php?request_id=<?php echo htmlspecialchars($report['request_id']); ?>" class="btn btn-info">ดูรายละเอียด</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
