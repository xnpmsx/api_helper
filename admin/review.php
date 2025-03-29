<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include('connect.php');
include('sidebar.php'); 

$currentPage = basename($_SERVER['PHP_SELF'], ".php");

// ตรวจสอบว่ามีค่าจากฟิลเตอร์หรือไม่ (ถ้าไม่มีให้เป็น 'all')
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// SQL พื้นฐาน
$sql = "SELECT r.review_id, r.review_rating, r.review_detail, r.review_date, 
               j.job_id, jt.type_name, gp.giver_name 
        FROM review r
        INNER JOIN job j ON j.job_id = r.job_id
        INNER JOIN job_type jt ON j.type_id = jt.type_id
        INNER JOIN giver_profile gp ON gp.giver_id = j.giver_id";

// กำหนดเงื่อนไข Filter
if ($filter == "positive") {
    $sql .= " WHERE r.review_rating > 3";
} elseif ($filter == "negative") {
    $sql .= " WHERE r.review_rating <= 3";
}

$stmt = $pdo->prepare($sql);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <h2>Report</h2>
    <hr>

    <!-- Filter Dropdown -->
    <form method="GET" class="mb-3">
        <label for="filter" class="form-label"><b>Filter by Rating:</b></label>
        <select name="filter" id="filter" class="form-select w-auto d-inline">
            <option value="all" <?php echo ($filter == 'all') ? 'selected' : ''; ?>>All</option>
            <option value="positive" <?php echo ($filter == 'positive') ? 'selected' : ''; ?>>แง่บวก (> 3)</option>
            <option value="negative" <?php echo ($filter == 'negative') ? 'selected' : ''; ?>>แง่ลบ (≤ 3)</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>คะเเนน</th>
                <th>ประเภทงาน</th>
                <th>ข้อความ</th>
                <th>วันที่</th>
                <th>ผู้ให้บริการ</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reviews as $review): ?>
            <tr>
                <td>
                    <?php echo htmlspecialchars($review['review_rating']); ?>
                    <?php echo ($review['review_rating'] > 3) ? '✅ (แง่บวก)' : '❌ (แง่ลบ)'; ?>
                </td>
                <td><?php echo htmlspecialchars($review['type_name']); ?></td>
                <td><?php echo htmlspecialchars($review['review_detail']); ?></td>
                <td><?php echo htmlspecialchars($review['review_date']); ?></td>
                <td><?php echo htmlspecialchars($review['giver_name']); ?></td>
                <td><form action="delete_review.php" method="POST" class="d-inline">
                        <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['review_id']); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบรีวิวนี้?');">ลบ</button>
                    </form></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
