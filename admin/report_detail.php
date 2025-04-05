<?php
// report_detail.php
session_start();
include('sidebar.php');
include('connect.php');
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
if (isset($_GET['id'])) {
    $report_id = $_GET['id'];
    $sql = "SELECT * FROM `report` 
INNER JOIN job_profile jp ON jp.job_id = report.job_id
INNER JOIN receiver_profile rp ON rp.profile_id = jp.profile_id
WHERE report_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/report_detail.css">
    <title>Report Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/report_detail.css">
</head>
<body>
<div class="main-content">
    <h2>Report Detail</h2>
    <hr>
    <p><strong>User:</strong> <?php echo htmlspecialchars($report['profile_name']); ?></p>
    <p><strong>Complaint:</strong> <?php echo htmlspecialchars($report['report_topic']); ?></p>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($report['report_date']); ?></p>
    <p><strong>Details:</strong> <?php echo htmlspecialchars($report['report_detail']); ?></p>
    <?php if (!empty($report['report_img'])): ?>
        <img src="<?php echo htmlspecialchars($server); ?>/<?php echo htmlspecialchars($report['report_img']); ?>" alt="Report Image" class="img-fluid">
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
