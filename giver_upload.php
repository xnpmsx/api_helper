<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'connect.php';  // Include your database connection file

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['giver_id']) || empty($_POST['giver_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing giver_id"]);
        exit;
    }

    $giver_id = $_POST['giver_id'];

    if (!isset($_FILES['file1']) || !isset($_FILES['file2']) || !isset($_FILES['file3'])) {
        echo json_encode(["status" => "error", "message" => "All three files are required"]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Define file storage paths
        $uploadDirs = [
            'file1' => __DIR__ . '/assets/cer/',
            'file2' => __DIR__ . '/assets/criminal/',
            'file3' => __DIR__ . '/assets/idcopy/'
        ];

        foreach ($uploadDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        // Process file uploads
        $filesData = [];
        foreach (['file1' => 'certificate', 'file2' => 'criminal_record', 'file3' => 'id_copy'] as $key => $dbColumn) {
            $file = $_FILES[$key];
            $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = "giver_{$giver_id}_{$key}_" . time() . '.' . $fileExt;
            $filePath = $uploadDirs[$key] . $fileName;
            $dbFilePath = "assets/" . basename($uploadDirs[$key]) . "/" . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                echo json_encode(["status" => "error", "message" => "Failed to upload $key"]);
                exit;
            }
            chmod($filePath, 0644);
            $filesData[$dbColumn] = $dbFilePath;
        }

        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO giver_verify (giver_id, certificate, criminal_record, id_img) VALUES (:giver_id, :file1, :file2, :file3)");
        $stmt->execute([
            ':giver_id' => $giver_id,
            ':file1' => $filesData['certificate'],
            ':file2' => $filesData['criminal_record'],
            ':file3' => $filesData['id_copy']
        ]);

        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Files uploaded successfully.", "files" => $filesData]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
