<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('connect.php');
include('sidebar.php'); 

$currentPage = basename($_SERVER['PHP_SELF'], ".php"); 
// ดึงข้อมูลจากฐานข้อมูล (เพิ่ม SQL ของคุณที่นี่)
$sql = "SELECT * FROM `report` 
INNER JOIN job_profile jp ON jp.job_id = report.job_id
INNER JOIN receiver_profile rp ON rp.profile_id = jp.profile_id"; // ใส่ SQL ของคุณเอง
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
    <h2>Report</h2>
    <hr>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User</th>
                <th>Complaint</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
            <tr>
                <td><?php echo htmlspecialchars($report['profile_name']); ?></td>
                <td><?php echo htmlspecialchars($report['report_topic']); ?></td>
                <td><?php echo htmlspecialchars($report['report_date']); ?></td>
                <td><a href="report_detail.php?id=<?php echo $report['report_id']; ?>" class="text-primary">ดูรายละเอียด</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
