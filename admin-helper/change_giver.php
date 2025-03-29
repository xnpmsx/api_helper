<?php
session_start();

include('connect.php');
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ถ้าไม่ได้ล็อกอินให้ไปที่หน้า login
    exit();
}
// ตรวจสอบว่า request_id ถูกส่งมาใน URL หรือไม่
if (isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    // Get the current job_id based on request_id
    $sql = "SELECT job_id FROM request WHERE request_id = :request_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($job) {
        $job_id = $job['job_id'];

        // Fetch the list of possible givers based on the job
        $sql = "
            WITH job_addon_count AS (
                SELECT job_id, COUNT(addon_id) AS total_job_addons
                FROM job_addon
                WHERE job_id = :job_id
                GROUP BY job_id
            ),
            giver_addon_count AS (
                SELECT ga.giver_id, COUNT(ga.addon_id) AS total_giver_addons
                FROM giver_addon ga
                INNER JOIN job_addon ja ON ja.addon_id = ga.addon_id
                WHERE ja.job_id = :job_id
                GROUP BY ga.giver_id
            ),
            missing_addons AS (
                SELECT gp.giver_id, a.addon_name
                FROM giver_profile gp
                CROSS JOIN job_addon ja
                LEFT JOIN giver_addon ga ON ga.giver_id = gp.giver_id AND ga.addon_id = ja.addon_id
                INNER JOIN addon a ON ja.addon_id = a.addon_id
                WHERE ja.job_id = :job_id AND ga.addon_id IS NULL
            ),
            giver_reviews AS (
                SELECT j.giver_id, AVG(r.review_rating) AS avg_review_rating
                FROM review r
                INNER JOIN job j ON r.job_id = j.job_id
                GROUP BY j.giver_id
            )
            SELECT gp.giver_id, gp.giver_name, gp.giver_img,
                COALESCE(gac.total_giver_addons, 0) AS giver_addon_count, 
                jac.total_job_addons,
                ROUND(COALESCE(gac.total_giver_addons, 0) * 100.0 / jac.total_job_addons, 2) AS match_percentage,
                COALESCE(GROUP_CONCAT(m.addon_name SEPARATOR ', '), '') AS missing_addons,
                COALESCE(gr.avg_review_rating, 0) AS avg_review_rating
            FROM giver_profile gp
            LEFT JOIN giver_addon_count gac ON gp.giver_id = gac.giver_id
            CROSS JOIN job_addon_count jac
            LEFT JOIN missing_addons m ON gp.giver_id = m.giver_id
            LEFT JOIN giver_reviews gr ON gp.giver_id = gr.giver_id
            INNER JOIN giver_type gt ON gp.giver_id = gt.giver_id
            WHERE gt.type_id = (SELECT type_id FROM job WHERE job_id = :job_id) 
            AND gp.giver_status = 1
            AND NOT EXISTS (
                SELECT 1 
                FROM job j_conflict
                WHERE j_conflict.giver_id = gp.giver_id
                AND j_conflict.job_date = (SELECT job_date FROM job WHERE job_id = :job_id)
                AND j_conflict.job_time = (SELECT job_time FROM job WHERE job_id = :job_id)
            )
            GROUP BY gp.giver_id, gp.giver_name, jac.total_job_addons, gac.total_giver_addons, gr.avg_review_rating
            ORDER BY match_percentage DESC, giver_addon_count DESC, avg_review_rating DESC, gp.giver_id ASC
            LIMIT 10;
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt->execute();
        $givers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนผู้ช่วย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>เลือกผู้ให้บริการใหม่</h2>
    <hr>
    
    <?php if ($givers): ?>
    <form action="change_giver_action.php" method="POST">
        <div class="mb-3">
            <label><strong>เลือกผู้ให้บริการ:</strong></label>
            <select name="giver_id" class="form-select" required>
                <?php foreach ($givers as $giver): ?>
                    <option value="<?php echo $giver['giver_id']; ?>">
                        <?php echo htmlspecialchars($giver['giver_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
        <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
        <button type="submit" class="btn btn-success">เลือกผู้ให้บริการ</button>
    </form>
    <?php else: ?>
        <p>ไม่พบผู้ให้บริการที่ตรงกับงานนี้</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
