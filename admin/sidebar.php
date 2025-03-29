<!-- sidebar.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    
</head>
<body>
<div class="sidebar">
    <img src="../assets/picture/icon.png" alt="Care System Logo" width="30" height="30" style="vertical-align: middle; margin-right: 10px;">
    Care System
    <a href="dashboard.php" class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>"><i class="fas fa-user-check"></i> ยืนยันผู้ให้บริการ</a>
    <a href="user_management.php" class="<?php echo ($currentPage == 'user_management') ? 'active' : ''; ?>"><i class="fas fa-users"></i> จัดการบัญชีผู้ใช้</a>
    <a href="job_management.php" class="<?php echo ($currentPage == 'job_management') ? 'active' : ''; ?>"><i class="fas fa-briefcase"></i> จัดการงาน</a>
    <a href="report.php" class="<?php echo ($currentPage == 'report') ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> รายงาน</a>
    <a href="request.php" class="<?php echo ($currentPage == 'request') ? 'active' : ''; ?>"><i class="fas fa-cogs"></i> จัดการคำร้อง</a>
    <a href="emer_job.php" class="<?php echo ($currentPage == 'emer_job') ? 'active' : ''; ?>"><i class="fas fa-exclamation-triangle"></i> งานด่วน!</a>
    <a href="review.php" class="<?php echo ($currentPage == 'report') ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> รีวิว</a>


    <!-- ปุ่ม Logout -->
    <form action="logout.php" method="POST">
        <button type="submit" class="btn btn-dark logout-btn">
            <i class="fas fa-sign-out-alt"></i> Log out
        </button>
    </form>
</div>
</body>
</html>