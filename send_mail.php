<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

// ตั้งค่า Header
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// รับ JSON
$data = json_decode(file_get_contents('php://input'), true);

// ตรวจสอบว่ามีข้อมูล status และค่าอื่นๆ ที่ต้องการหรือไม่
if (isset($data['status']) && $data['status'] === 'success' && isset($data['detail']) && isset($data['target']) && isset($data['time'])) {
    $mail = new PHPMailer(true);

    try {
        // ตั้งค่า SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // ใช้ Gmail SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'helperapp.noti@gmail.com'; // เปลี่ยนเป็นอีเมลของคุณ
        $mail->Password   = 'mvwl pqlw znpu wukb'; // ใช้ App Password จาก Google
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = "UTF-8";
        // ตั้งค่าผู้ส่งและผู้รับ
        $mail->setFrom('helperapp.noti@gmail.com', 'ระบบแจ้งเตือน');
        $mail->addAddress('helperapp.noti@gmail.com'); // เปลี่ยนอีเมลผู้รับตามต้องการ
        

        // รับค่า detail, target, time และสร้างเนื้อหาอีเมล
        $detail = htmlspecialchars($data['detail']);
        $target = htmlspecialchars($data['target']);
        $time = htmlspecialchars($data['time']);

        // เนื้อหาอีเมล
        $mail->isHTML(true);
        $mail->Subject = 'แจ้งเตือน: มีงานใหม่เข้ามา';
        $mail->Body    = "<h3>มีงานใหม่เข้ามา</h3>
                          <p><strong>วันที่:</strong> $time</p>
                          <p><strong>สถานที่:</strong> $target</p>
                          <p><strong>รายละเอียด:</strong> $detail</p>
                          ";

        // ส่งอีเมล
        $mail->send();
        echo json_encode(["message" => "อีเมลถูกส่งสำเร็จ"]);
    } catch (Exception $e) {
        echo json_encode(["message" => "ไม่สามารถส่งอีเมลได้: " . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(["message" => "ข้อมูลไม่ถูกต้อง"]);
}
?>
