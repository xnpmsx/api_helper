<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email via EmailJS</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <form id="email-form">
        <input type="text" id="username" placeholder="Username" required><br>
        <input type="text" id="recaptcha" placeholder="g-recaptcha-response" required><br>
        <button type="submit">Send Email</button>
    </form>

    <script>
        // Your specific values
        var serviceId = 'service_vqdh2lp';
        var templateId = 'template_twx1srj';
        var userId = 'hvedMSt7onx-3sVtj';

        // Form submission handler
        $('#email-form').on('submit', function(event) {
            event.preventDefault(); // Prevent form from refreshing the page

            // Get the values from the form
            var username = $('#username').val();
            var recaptchaResponse = $('#recaptcha').val();

            // Prepare the data object
            var data = {
                service_id: serviceId,
                template_id: templateId,
                user_id: userId,
                template_params: {
                    'username': username,  // Ensure this matches your EmailJS template parameters
                    'recaptcha': recaptchaResponse, // Corrected variable usage
                    'to_email': 'helperapp.noti@gmail.com' // Only include if needed
                }
            };

            // Send the email via AJAX
            $.ajax('https://api.emailjs.com/api/v1.0/email/send', {
                type: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json'
            }).done(function() {
                alert('Your mail is sent!');
            }).fail(function(error) {
                alert('Oops... ' + JSON.stringify(error));
            });
        });
    </script>
</body>
</html>
