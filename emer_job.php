<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *"); // Change '*' to a specific domain for security
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'connect.php'; // Ensure this file correctly initializes $pdo

// Check if database connection is valid
if (!isset($pdo)) {
    echo json_encode(["status" => "error", "message" => "Database connection error"]);
    exit;
}

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
    exit;
}

// Check if Content-Type is JSON
if (empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    echo json_encode(["status" => "error", "message" => "Invalid Content-Type, expected application/json"]);
    exit;
}

// Read input data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['target']) || empty($data['profile_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

try {
    $pdo->beginTransaction(); // Start transaction

    // Sanitize input
    $target = htmlspecialchars(strip_tags($data['target']));
    $profile_id = intval($data['profile_id']);
    $detail = isset($data['detail']) ? htmlspecialchars(strip_tags($data['detail'])) : '';
    $date = !empty($data['date']) ? $data['date'] : date('Y-m-d'); // Default to today

    // Validate date format (YYYY-MM-DD)
    if (!empty($data['date']) && !DateTime::createFromFormat('Y-m-d', $data['date'])) {
        echo json_encode(["status" => "error", "message" => "Invalid date format"]);
        exit;
    }

    // Insert job record
    $stmt = $pdo->prepare("INSERT INTO job (job_target, job_date, job_detail, type_id, job_time, job_appointment) 
                           VALUES (:target, :date, :detail, 5, 'ฉุกเฉิน', '-')");

    $stmt->bindParam(':target', $target);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':detail', $detail);

    if ($stmt->execute()) {
        $job_id = $pdo->lastInsertId(); 

        // Insert into job_profile table
        $stmt2 = $pdo->prepare("INSERT INTO job_profile (profile_id, job_id) VALUES (:profile, :job_id)");
        $stmt2->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt2->bindParam(':profile', $profile_id, PDO::PARAM_INT);
        $stmt2->execute();

        $stmt3 = $pdo->prepare("INSERT INTO job_addon (addon_id, job_id) VALUES (5, :job_id)");
        $stmt3->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt3->execute();


        $pdo->commit(); // Commit transaction

        echo json_encode(["status" => "success", "message" => "Job inserted successfully", "job_id" => $job_id]);
    } else {
        $pdo->rollBack(); // Rollback if insertion fails
        echo json_encode(["status" => "error", "message" => "Failed to insert job"]);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Unexpected error: " . $e->getMessage()]);
}
?>
