<?php
require 'connect.php';
session_start();

if (!isset($_GET['user_id'])) {
    die("User ID is required.");
}
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
$user_id = $_GET['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $profile_name = $_POST['profile_name'];
    $profile_bd = $_POST['profile_bd'];
    $profile_phone = $_POST['profile_phone'];
    $profile_detail = $_POST['profile_detail'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    
    $sql = "UPDATE user u 
            INNER JOIN receiver r ON r.user_id = u.user_id
            INNER JOIN receiver_profile rp ON rp.receiver_id = r.receiver_id
            SET u.phone = :phone, u.email = :email, rp.profile_name = :profile_name, rp.profile_bd = :profile_bd, rp.profile_phone = :profile_phone, rp.profile_detail = :profile_detail
            WHERE u.user_id = :user_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([ 'phone' => $phone, 'email' => $email, 'profile_name' => $profile_name, 'profile_bd' => $profile_bd, 'profile_phone' => $profile_phone, 'profile_detail' => $profile_detail, 'user_id' => $user_id ]);
    
    echo "<script>alert('Updated successfully!'); window.location.href='user_management.php?type=2';</script>";
}

$sql = "SELECT u.phone, u.email, rp.profile_name, rp.profile_bd, rp.profile_phone, rp.profile_detail 
        FROM user u 
        INNER JOIN receiver r ON r.user_id = u.user_id
        INNER JOIN receiver_profile rp ON rp.receiver_id = r.receiver_id
        WHERE u.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Receiver</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px; background: #007BFF; color: white; border: none; width: 100%; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Receiver Information</h2>
        <form method="POST">
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label>Name:</label>
            <input type="text" name="profile_name" value="<?php echo htmlspecialchars($user['profile_name']); ?>" required>
            
            <label>Birthday:</label>
            <input type="date" name="profile_bd" value="<?php echo htmlspecialchars($user['profile_bd']); ?>" required>
            
            <label>Phone:</label>
            <input type="text" name="profile_phone" value="<?php echo htmlspecialchars($user['profile_phone']); ?>" required>
            
            <label>Detail:</label>
            <input type="text" name="profile_detail" value="<?php echo htmlspecialchars($user['profile_detail']); ?>" required>
            
            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>