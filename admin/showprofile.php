<?php
session_start();
include('connect.php');
include('sidebar.php');
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}


// Check if 'giver_id' is passed in the URL
if (isset($_GET['giver_id'])) {
    $giver_id = $_GET['giver_id'];

    // Get details from the giver_profile table using the provided giver_id
    $sql = "SELECT u.email,u.phone,gp.giver_name, gp.giver_bd, gp.Specialities, gp.giver_img, 
                   gv.item1, gv.item2, gv.item3, gv.id_img, gv.certificate, gv.criminal_record
            FROM giver_profile gp
            INNER JOIN giver_verify gv ON gv.giver_id = gp.giver_id
            INNER JOIN user u ON u.user_id = gp.user_id
            WHERE gp.giver_id = :giver_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':giver_id', $giver_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the result
    $caregiver = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($caregiver) {
        // Fetch the related user info
        $sql_user = "SELECT * FROM user WHERE user_id = :user_id";
        $stmt_user = $pdo->prepare($sql_user);
        $stmt_user->bindParam(':user_id', $caregiver['user_id'], PDO::PARAM_INT);
        $stmt_user->execute();
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "Caregiver not found.";
        exit;
    }
} else {
    echo "No caregiver selected.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/profile.css">
    <title>Caregiver Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<>
<div class="main-content">
<div class="container ">
    <h2>Caregiver Profile</h2>
    <hr>

    <!-- Display caregiver profile details -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($caregiver['giver_name']); ?></h5>
            <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($caregiver['email']); ?></p>
            <p class="card-text"><strong>Phone:</strong> <?php echo htmlspecialchars($caregiver['phone']); ?></p>
            <p class="card-text"><strong>Birthdate:</strong> <?php echo htmlspecialchars($caregiver['giver_bd']); ?></p>
            <p class="card-text"><strong>Specialities:</strong> <?php echo htmlspecialchars($caregiver['Specialities']); ?></p>
            <p class="card-text"><strong>Item 1:</strong> <?php echo htmlspecialchars($caregiver['item1']); ?></p>
            <p class="card-text"><strong>Item 2:</strong> <?php echo htmlspecialchars($caregiver['item2']); ?></p>
            <p class="card-text"><strong>Item 3:</strong> <?php echo htmlspecialchars($caregiver['item3']); ?></p>
            <p class="card-text"><strong>Identity Image:</strong> <img src="http://192.168.21.242/helper/api/<?php echo htmlspecialchars($caregiver['id_img']); ?>" alt="ID Image" class="img-fluid" style="max-width: 200px;"></p>
            
            <p class="card-text"><strong>Certificate:</strong> <img src="http://192.168.21.242/helper/api/<?php echo htmlspecialchars($caregiver['certificate']); ?>" alt="Certificate" class="img-fluid" style="max-width: 200px;"></p>
            <p class="card-text"><strong>Profile Image:</strong> <img src="http://192.168.21.242/helper/api/<?php echo htmlspecialchars($caregiver['criminal_record']); ?>" alt="Caregiver Image" class="img-fluid" style="max-width: 200px;"></p>

            <p class="card-text"><strong>Profile Image:</strong> <img src="http://192.168.21.242/helper/api/<?php echo htmlspecialchars($caregiver['giver_img']); ?>" alt="Caregiver Image" class="img-fluid" style="max-width: 200px;"></p>
        </div>
    </div>

    <br>
    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
