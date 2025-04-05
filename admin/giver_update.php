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
    $giver_name = $_POST['giver_name'];
    $giver_bd = $_POST['giver_bd'];
    $specialities = $_POST['specialities'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    
    $sql = "UPDATE user u 
            INNER JOIN giver_profile gp ON gp.user_id = u.user_id
            SET u.phone = :phone, u.email = :email, gp.giver_name = :giver_name, gp.giver_bd = :giver_bd, gp.Specialities = :specialities
            WHERE u.user_id = :user_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([ 'phone' => $phone, 'email' => $email, 'giver_name' => $giver_name, 'giver_bd' => $giver_bd, 'specialities' => $specialities, 'user_id' => $user_id ]);
    
    echo "<script>alert('Updated successfully!'); window.location.href='user_management.php?type=1';</script>";
}

$sql = "SELECT u.phone, u.email, gp.giver_name, gp.giver_bd, gp.Specialities 
        FROM user u 
        INNER JOIN giver_profile gp ON gp.user_id = u.user_id
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
    <link rel="stylesheet" type="text/css" href="css/update_giver.css">
    
    <title>Update Giver</title>
    
</head>
<body>
    <div class="container">
        <h2>Update Giver Information</h2>
        <form method="POST">
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label>Name:</label>
            <input type="text" name="giver_name" value="<?php echo htmlspecialchars($user['giver_name']); ?>" required>
            
            <label>Birthday:</label>
            <input type="date" name="giver_bd" value="<?php echo htmlspecialchars($user['giver_bd']); ?>" required>
            
            <label>Specialities:</label>
            <input type="text" name="specialities" value="<?php echo htmlspecialchars($user['Specialities']); ?>" required>
            
            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>