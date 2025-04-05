<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
require 'connect.php';
include 'sidebar.php';
$currentPage = basename($_SERVER['PHP_SELF'], ".php");


$type = isset($_GET['type']) ? (int)$_GET['type'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';

function calculateAge($birthDate) {
    $birthDate = new DateTime($birthDate);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

if ($type == 1) {
    $sql = "SELECT u.user_id, u.phone, u.email, gp.giver_name, gp.giver_bd, gp.Specialities, gp.giver_img 
            FROM user u 
            INNER JOIN giver_profile gp ON gp.user_id = u.user_id 
            WHERE u.user_type = 1 AND gp.giver_name LIKE :search";
} else {
    $sql = "SELECT u.user_id, u.phone, u.email, rp.profile_name, rp.profile_bd, rp.profile_phone, rp.profile_detail, rp.profile_image 
            FROM user u 
            INNER JOIN receiver r ON r.user_id = u.user_id 
            INNER JOIN receiver_profile rp ON rp.receiver_id = r.receiver_id 
            WHERE u.user_type = 2 AND rp.profile_name LIKE :search";
}

$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/user_management.css">
    <title>User Management</title>
    <script>
        function confirmDelete(userId, type) {
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?')) {
                window.location.href = 'delete_user.php?user_id=' + userId + '&type=' + type;
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body>

    <div class="main-content">
        <div class="container">
            <h2>จัดการผู้ใช้งาน</h2>
            <div class="tabs">
                <a href="?type=1" class="<?php echo $type == 1 ? 'active' : ''; ?>">ผู้ให้บริการ</a>
                <a href="?type=2" class="<?php echo $type == 2 ? 'active' : ''; ?>">ผู้รับบริการ</a>
            </div>
            <form method="GET">
                <input type="hidden" name="type" value="<?php echo $type; ?>">
                <input type="text" name="search" placeholder="ค้นหาชื่อ" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">ค้นหา</button>
            </form>

            <table>
                <tr>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Age</th>
                    <?php if ($type == 1): ?>
                        <th>Specialities</th>
                    <?php else: ?>
                        <th>Phone</th>
                        <th>Detail</th>
                    <?php endif; ?>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row[$type == 1 ? 'giver_name' : 'profile_name']); ?></td>
                        <td><?php echo calculateAge($row[$type == 1 ? 'giver_bd' : 'profile_bd']); ?></td>
                        <?php if ($type == 1): ?>
                            <td><?php echo htmlspecialchars($row['Specialities']); ?></td>
                        <?php else: ?>
                            <td><?php echo htmlspecialchars($row['profile_phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['profile_detail']); ?></td>
                        <?php endif; ?>
                        <td><img src="<?php echo htmlspecialchars($server); ?>/<?php echo htmlspecialchars($type == 1 ? $row['giver_img'] : $row['profile_image']); ?>" alt="Image"></td>
                        <td class="actions">
                            <a href="<?php echo $type == 1 ? 'giver_update.php' : 'receiver_update.php'; ?>?user_id=<?php echo $row['user_id']; ?>">
                                <button class="update"><i class="fa fa-edit"></i></button>
                            </a>
                            <button class="delete" onclick="confirmDelete(<?php echo $row['user_id']; ?>, <?php echo $type; ?>)"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

</body>

</html>
