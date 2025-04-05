<?php
// เชื่อมต่อกับฐานข้อมูล
session_start();

include('connect.php');
include('sidebar.php');  // ใช้ sidebar สำหรับ navigation
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
// ฟังก์ชันในการคำนวณอายุจากวันเกิด
$currentPage = basename($_SERVER['PHP_SELF'], ".php");
function calculateAge($birthDate) {
    $birthDate = new DateTime($birthDate);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

// SQL query ที่จะดึงข้อมูล
$sql = "SELECT j.job_id ,rp.profile_name, rp.profile_bd, rp.profile_detail, rp.profile_phone, rp.profile_image, 
        j.job_date, j.job_target, j.job_detail 
        FROM job j 
        INNER JOIN job_profile jp ON jp.job_id = j.job_id
        INNER JOIN receiver_profile rp ON rp.profile_id = jp.profile_id
        WHERE j.type_id = 5
        AND j.job_status=0";

$stmt = $pdo->query($sql);  // เรียกใช้คำสั่ง SQL
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);  // ดึงข้อมูลทั้งหมดจากฐานข้อมูล
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/emerjob.css">
    <title>Job Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
</head>
<body>

<!-- Sidebar -->
<?php include('sidebar.php'); ?>

<!-- Main Content -->
<div class="main-content">
    <h2>Job Details</h2>
    <hr>

    <!-- Job List -->
    <div class="row">
        <?php
        // ตรวจสอบข้อมูลทั้งหมดที่ได้จากฐานข้อมูล
        foreach ($jobs as $job) {
            // คำนวณอายุจากวันเกิด
            $age = calculateAge($job['profile_bd']);
            echo '
            <div class="col-md-4">
                <div class="job-card d-flex">
                    <img src="<?php echo htmlspecialchars($server); ?>/' . htmlspecialchars($job['profile_image']) . '" alt="Profile Image">
                    <div>
                        <h4>' . htmlspecialchars($job['profile_name']) . '</h4>
                        <p><strong>Age:</strong> ' . $age . ' years</p>
                        <p><strong>Phone:</strong> ' . htmlspecialchars($job['profile_phone']) . '</p>
                        <p><strong>Details:</strong> ' . htmlspecialchars($job['profile_detail']) . '</p>
                        <hr>
                        <p><strong>Job Date:</strong> ' . htmlspecialchars($job['job_date']) . '</p>
                        <p><strong>Target:</strong> ' . htmlspecialchars($job['job_target']) . '</p>
                        <p><strong>Job Details:</strong> ' . htmlspecialchars($job['job_detail']) . '</p>
                        <form action="select_service_provider.php" method="GET">
    <input type="hidden" name="job_id" value="' . htmlspecialchars($job['job_id']) . '">
    <button type="submit">เลือกผู้บริการ</button>
</form>
                    </div>
                </div>
            </div>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
