<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include('connect.php');
include('sidebar.php');

// ดึง caregiver ที่ยังไม่ผ่านการอนุมัติ
$sql = "SELECT giver_id, giver_name, u.email FROM giver_profile gp
        INNER JOIN user u ON u.user_id = gp.user_id 
        WHERE giver_status = 0";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$caregivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึง job type
$sql2 = "SELECT type_id, type_name FROM job_type";
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute();
$jobTypes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// ดึง addon และจัดกลุ่มตาม type_id
$sql3 = "SELECT addon_id, addon_name, type_id FROM addon";
$stmt3 = $pdo->prepare($sql3);
$stmt3->execute();
$addons = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// จัดกลุ่ม addon ตาม type_id
$addonMap = [];
foreach ($addons as $addon) {
    $addonMap[$addon['type_id']][] = $addon;
}

// สร้าง CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content" style="margin-left: 270px; padding: 20px;">
    <h2>Verify Care Giver</h2>
    <hr>

    <div class="list-group">
        <?php foreach ($caregivers as $caregiver): ?>
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <strong><?= htmlspecialchars($caregiver['giver_name']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($caregiver['email']) ?></small>
                </div>
                <div>
                    <a href="showprofile.php?giver_id=<?= urlencode($caregiver['giver_id']) ?>" class="btn btn-info btn-sm">Show Profile</a>

                    <button 
                        class="btn btn-success btn-sm"
                        data-bs-toggle="modal" 
                        data-bs-target="#acceptModal" 
                        onclick="setGiverId(<?= $caregiver['giver_id'] ?>)">
                        ✔ Accept
                    </button>

                    <form action="update_status.php" method="POST" class="d-inline">
                        <input type="hidden" name="giver_id" value="<?= $caregiver['giver_id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">✖ Reject</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="acceptModal" tabindex="-1" aria-labelledby="acceptModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="update_status.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">เลือกประเภทงาน</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="giver_id" id="modalGiverId">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <?php foreach ($jobTypes as $type): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="type_ids[]" value="<?= $type['type_id'] ?>" id="type<?= $type['type_id'] ?>">
                    <label class="form-check-label fw-bold" for="type<?= $type['type_id'] ?>">
                        <?= htmlspecialchars($type['type_name']) ?>
                    </label>
                </div>

                <?php if (isset($addonMap[$type['type_id']])): ?>
                    <div class="ms-4 mb-2">
                        <?php foreach ($addonMap[$type['type_id']] as $addon): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="addons[]" value="<?= $addon['addon_id'] ?>" id="addon<?= $addon['addon_id'] ?>">
                                <label class="form-check-label text-muted" for="addon<?= $addon['addon_id'] ?>">
                                    <?= htmlspecialchars($addon['addon_name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="modal-footer">
          <button type="submit" name="action" value="accept" class="btn btn-primary">ยืนยัน</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
function setGiverId(id) {
    document.getElementById('modalGiverId').value = id;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
