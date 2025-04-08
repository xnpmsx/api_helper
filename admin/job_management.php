<?php
// ดึงข้อมูลจากฐานข้อมูล
session_start();

include('connect.php');
include('sidebar.php');  // รวม Sidebar

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF'], ".php");

// กำหนดค่าเริ่มต้นของ job_status เป็น 2 (รอการยืนยัน)
$status_filter = isset($_GET['status']) ? $_GET['status'] : 2;

// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT j.job_id, t.type_name, cg.giver_name AS caregiver, cr.profile_name AS carereceiver, j.job_status 
        FROM job j
        INNER JOIN job_type t ON j.type_id = t.type_id 
        INNER JOIN giver_profile cg ON j.giver_id = cg.giver_id
        INNER JOIN job_profile jp ON jp.job_id = j.job_id
        INNER JOIN receiver_profile cr ON jp.profile_id = cr.profile_id
        WHERE j.job_status = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$status_filter]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
    <h2>Job Management</h2>
    <hr>
    
    <div class="mb-3 d-flex">
        <input type="text" class="form-control w-25" placeholder="Search">
        <button class="btn btn-secondary ms-2">Filter</button>
        <select class="form-select w-25 ms-2" onchange="filterStatus(this.value)">
            <option value="0" <?= $status_filter == 0 ? 'selected' : '' ?>>รอผู้ให้บริการยืนยัน</option>
            <option value="1" <?= $status_filter == 1 ? 'selected' : '' ?>>เลือกผู้ให้บริการใหม่</option>
            <option value="2" <?= $status_filter == 2 ? 'selected' : '' ?>>รอการยืนยัน</option>
            <option value="3" <?= $status_filter == 3 ? 'selected' : '' ?>>กำลังดำเนินการ</option>
            <option value="5" <?= $status_filter == 5 ? 'selected' : '' ?>>เสร็จสิ้น</option>
            <option value="10" <?= $status_filter == 10 ? 'selected' : '' ?>>ถูกยกเลิก</option>
        </select>
    </div>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Job</th>
                <th>Care Giver</th>
                <th>Care Receiver</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $job): ?>
            <tr>
                <td><?php echo htmlspecialchars($job['type_name']); ?></td>
                <td><?php echo htmlspecialchars($job['caregiver']); ?></td>
                <td><?php echo htmlspecialchars($job['carereceiver']); ?></td>
                <td>
                    <?php
                    switch ($job['job_status']) {
                        case 0: echo "รอผู้ให้บริการยืนยัน"; break;
                        case 1: echo "เลือกผู้ให้บริการใหม่"; break;
                        case 2: echo "รอการยืนยัน"; break;
                        case 3: echo "กำลังดำเนินการ"; break;
                        case 5: echo "เสร็จสิ้น"; break;
                        case 10: echo "ถูกยกเลิก"; break;
                    }
                    ?>
                </td>
                <td>
                    <form action="update_job.php" method="POST" class="d-inline">
                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                        <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm">Cancel</button>
                        <?php if ($job['job_status'] == 2): ?>
                            <button type="submit" name="action" value="confirm" class="btn btn-success btn-sm">Confirm</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function filterStatus(status) {
        window.location.href = '?status=' + status;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
