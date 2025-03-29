<?php
// เชื่อมต่อกับฐานข้อมูล
include('connect.php');
include('sidebar.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT gp.giver_id, gp.giver_name, gp.Specialities, gp.giver_img,
            GROUP_CONCAT(DISTINCT a.addon_name ORDER BY a.addon_name SEPARATOR ', ') AS addon_names
     FROM giver_profile gp
     INNER JOIN job j ON j.giver_id = gp.giver_id
     INNER JOIN giver_addon ga ON ga.giver_id = gp.giver_id
     INNER JOIN addon a ON a.addon_id = ga.addon_id
     WHERE NOT EXISTS (
         SELECT 1
         FROM job j2
         WHERE j2.giver_id = gp.giver_id
         AND j2.job_date = CURDATE()
     )
     GROUP BY gp.giver_id, gp.giver_name, gp.Specialities, gp.giver_img;
";

$stmt = $pdo->query($sql);
$providers = $stmt->fetchAll(PDO::FETCH_ASSOC);  // ดึงข้อมูลทั้งหมดจากฐานข้อมูล
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Providers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .card-img-top {
        width: 100%; /* กำหนดความกว้างของรูปภาพให้เต็ม */
        height: 200px; /* กำหนดความสูงของรูปภาพ */
        object-fit: contain; /* ทำให้รูปภาพไม่ยืดหรือบีบอัดเกินไป */
    }
</style>
</head>
<body>

<div class="container">
    <h2>Available Service Providers</h2>
    <div class="row">
        <?php foreach ($providers as $provider): ?>
            <div class="col-md-4">
                <div class="card">
                    <img src="http://192.168.1.42/helper/api/<?php echo htmlspecialchars($provider['giver_img']); ?>" alt="Provider Image" class="card-img-top">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($provider['giver_name']); ?></h5>
                        <p class="card-text"><strong>Specialities:</strong> <?php echo htmlspecialchars($provider['Specialities']); ?></p>
                        <p class="card-text"><strong>Available Add-ons:</strong> <?php echo htmlspecialchars($provider['addon_names']); ?></p>
                        <form action="update_emerJob.php" method="GET">
                            <input type="hidden" name="job_id" value="<?php echo $_GET['job_id']; ?>"> <!-- ส่งค่า job_id -->
                            <input type="hidden" name="giver_id" value="<?php echo $provider['giver_id']; ?>"> <!-- ส่งค่า giver_id -->
                            <button type="submit" class="btn btn-primary">Select Provider</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
