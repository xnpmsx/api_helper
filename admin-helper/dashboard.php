<?php
// เริ่ม session
session_start();
// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
include('connect.php');


include('sidebar.php');  

$currentPage = basename($_SERVER['PHP_SELF'], ".php");

// ใช้ Prepared Statement เพื่อป้องกัน SQL Injection
$sql = "SELECT giver_id, giver_name, u.email FROM giver_profile gp
        INNER JOIN user u ON u.user_id = gp.user_id 
        WHERE giver_status = 0";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$caregivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// สร้าง CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <style>
        body {
            font-family: Arial, sans-serif;
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }
        .logout-btn {
            position: absolute;
            bottom: 20px;
            width: calc(100% - 40px);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include('sidebar.php'); ?>

<!-- Main Content -->
<div class="main-content">
    <h2>Verify Care Giverrr</h2>
    <hr>
    
    <div class="list-group">
        <?php
        foreach ($caregivers as $caregiver) {
            echo '
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <strong>' . htmlspecialchars($caregiver['giver_name'], ENT_QUOTES, 'UTF-8') . '</strong><br>
                    <small class="text-muted">' . htmlspecialchars($caregiver['email'], ENT_QUOTES, 'UTF-8') . '</small>
                </div>
                <div>
                    <a href="showprofile.php?giver_id=' . urlencode($caregiver['giver_id']) . '" class="btn btn-info btn-sm">Show Profile</a>

                    <form action="update_status.php" method="POST" class="d-inline">
                        <input type="hidden" name="giver_id" value="' . htmlspecialchars($caregiver['giver_id'], ENT_QUOTES, 'UTF-8') . '">
                        <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
                        <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">✔ Accept</button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">✖ Reject</button>
                    </form>
                </div>
            </div>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
