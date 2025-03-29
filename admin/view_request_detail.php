<?php
include('connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
// ตรวจสอบว่ามีการส่ง request_id มาหรือไม่
if (isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];

    // ดึงข้อมูลรายละเอียดคำร้องจากฐานข้อมูล
    $sql = "SELECT 
        gp.giver_name, 
        gp.Specialities, 
        gp.giver_img, 
        r.request_detail, 
        j.job_date, 
        j.job_time, 
        j.job_target, 
        j.job_detail, 
        a.addon_name, 
        jt.type_name,
        r.job_id
    FROM 
        request r
    INNER JOIN 
        job j ON j.job_id = r.job_id 
    INNER JOIN 
        job_type jt ON j.type_id = jt.type_id
    INNER JOIN 
        job_addon ja ON ja.job_id = j.job_id
    INNER JOIN 
        addon a ON a.addon_id = ja.addon_id
    INNER JOIN 
        giver_profile gp ON gp.giver_id = j.giver_id
    WHERE 
        r.request_id = :request_id"; // เพิ่มเงื่อนไขกรองตาม request_id

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดคำร้อง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>รายละเอียดคำร้อง</h2>
    <hr>
    
    <?php if ($report): ?>
    <div class="mb-3">
        <label><strong>ID คำร้อง:</strong></label>
        <p><?php echo htmlspecialchars($request_id); ?></p>
    </div>
    <div class="mb-3">
        <label><strong>ชื่อผู้ให้บริการ:</strong></label>
        <p><?php echo htmlspecialchars($report['giver_name']); ?></p>
    </div>
    <div class="mb-3">
        <label><strong>ประเภทงาน:</strong></label>
        <p><?php echo htmlspecialchars($report['type_name']); ?></p>
    </div>
    <div class="mb-3">
        <label><strong>เหตุผลที่ขอเปลี่ยน:</strong></label>
        <p><?php echo nl2br(htmlspecialchars($report['request_detail'])); ?></p>
    </div>
    <div class="mb-3">
        <label><strong>รายละเอียดงาน:</strong></label>
        <p><?php echo nl2br(htmlspecialchars($report['job_detail'])); ?></p>
    </div>
    <div class="mb-3">
        <label><strong>วันที่และเวลา:</strong></label>
        <p><?php echo htmlspecialchars($report['job_date'] . ' ' . $report['job_time']); ?></p>
    </div>
    <div class="mb-3">
        <label><strong>เป้าหมายงาน:</strong></label>
        <p><?php echo htmlspecialchars($report['job_target']); ?></p>
    </div>
    <div class="mb-3">
        <label><strong>Addon ที่เกี่ยวข้อง:</strong></label>
        <p><?php echo htmlspecialchars($report['addon_name']); ?></p>
    </div>
    <div class="mb-3">
        <label><strong>ความเชี่ยวชาญของผู้ให้บริการ:</strong></label>
        <p><?php echo htmlspecialchars($report['Specialities']); ?></p>
    </div>
    <div class="mb-3">
        <label><strong>รูปภาพผู้ให้บริการ:</strong></label>
        <?php if ($report['giver_img']): ?>
            <img src="http://192.168.1.42/helper/api/<?php echo htmlspecialchars($report['giver_img']); ?>" alt="giver image" width="200">
        <?php else: ?>
            <p>ไม่มีรูปภาพ</p>
        <?php endif; ?>
    </div>

    <!-- Reject Request Button -->
    <form action="reject_request.php" method="POST">
        <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
        <button type="submit" class="btn btn-danger">ปฏิเสธคำร้อง</button>
    </form>

    <!-- Change Giver Button -->
    <form action="change_giver.php" method="POST">
        <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
        <button type="submit" class="btn btn-warning">เปลี่ยนผู้ช่วย</button>
    </form>

    <?php else: ?>
        <p>ไม่พบคำร้องนี้</p>
    <?php endif; ?>

    <a href="report.php" class="btn btn-primary">กลับไปยังรายการคำร้อง</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
