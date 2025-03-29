<?php
// กำหนด header เพื่อให้ส่งข้อมูลเป็น JSON
header("Content-Type: application/json");

// ข้อมูลที่ได้รับจากการส่ง API
$data = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบว่าได้รับข้อมูลที่จำเป็นหรือไม่
if (empty($data['detail']) || empty($data['date'])) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Integration</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <script>
        console.log("WebView HTML content loaded!");

        // รับข้อมูลจาก PHP
        var detail = "<?php echo $data['detail']; ?>";
        var date = "<?php echo $data['date']; ?>";

        // EmailJS configuration
        var serviceId = 'service_vqdh2lp';
        var templateId = 'template_twx1srj';
        var userId = 'hvedMSt7onx-3sVtj';

        // ฟังก์ชันสำหรับส่งอีเมลผ่าน EmailJS
        function sendEmailJS(detail, date) {
            var emailData = {
                service_id: serviceId,
                template_id: templateId,
                user_id: userId,
                template_params: {
                    'time': date,
                    'message': detail,
                    'to_email': 'helperapp.noti@gmail.com'
                }
            };

            $.ajax('https://api.emailjs.com/api/v1.0/email/send', {
                type: 'POST',
                data: JSON.stringify(emailData),
                contentType: 'application/json'
            }).done(function() {
                console.log('Success:', response);
                alert('Email sent successfully via EmailJS!');
            }).fail(function(error) {
                console.error('EmailJS Error: ' + JSON.stringify(error));
                alert('EmailJS Error: ' + JSON.stringify(error));
            });
        }

        // เรียกใช้งานฟังก์ชันเพื่อส่งอีเมล
        sendEmailJS(detail, date);

    </script>

</body>
</html>
