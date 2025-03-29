<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Data to PHP and Email via EmailJS (Data Received Externally)</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <h1>Processing Data Received from External Source</h1>
    <div id="php_response"></div>

    <script>
        // EmailJS configuration
        var serviceId = 'service_vqdh2lp';
        var templateId = 'template_twx1srj';
        var userId = 'hvedMSt7onx-3sVtj';

        // Function to receive data from external source (e.g., Flutter) and process
        function processExternalData(target, profile_id, date, detail) {
            sendDataToPHP(target, profile_id, date, detail);
            sendEmailJS(detail, date); // Optionally send email after PHP processing, or you can trigger it separately.
        }

        // Function to send data to PHP
        function sendDataToPHP(target, profile_id, date, detail) {
            var data = {
                target: target,
                profile_id: parseInt(profile_id), // Ensure profile_id is an integer
                date: date,
                detail: detail
            };

            $.ajax({
                url: 'your_php_file.php', // Replace 'your_php_file.php' with the actual path to your PHP file
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json', // Expect JSON response from PHP
                success: function(response) {
                    $('#php_response').html("<p>PHP Response: " + JSON.stringify(response) + "</p>");
                    if (response.status === "success") {
                        console.log("Data sent to PHP and inserted successfully!");
                        // You can trigger email sending here if you want to ensure PHP success first
                        // sendEmailJS(detail, date);
                    } else {
                        console.error("PHP Error: " + response.message);
                        alert("PHP Error: " + response.message); // Optionally alert on PHP error
                    }
                },
                error: function(xhr, status, error) {
                    $('#php_response').html("<p>PHP Error: " + error + "</p>");
                    console.error("AJAX Error sending data to PHP: " + error);
                    alert("AJAX Error sending data to PHP: " + error); // Optionally alert on AJAX error
                }
            });
        }

        // Function to send email via EmailJS
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
                console.log('Email sent successfully via EmailJS!');
                alert('Email sent successfully via EmailJS!'); // Optionally alert on email success
            }).fail(function(error) {
                console.error('EmailJS Error: ' + JSON.stringify(error));
                alert('EmailJS Error: ' + JSON.stringify(error)); // Optionally alert on email error
            });
        }

        // Example of how you might call processExternalData from your external source
        // (replace with your actual data passing mechanism)
        // In a real scenario, you would receive these values dynamically.
        // For testing in the browser console:
        // processExternalData("Clean Bathroom", "2", "2024-07-30", "Clean all bathroom surfaces and mirrors.");

    </script>

</body>
</html>