<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Set the custom error log path
// ini_set('error_log', './my_php_errors.log');

//Load Composer's autoloader
require 'vendor/autoload.php';

//Create an instance; passing `true` enables exceptions
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collecting form data
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $description = $_POST['description'];

    // Validate form fields
    $errors = [];
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    if (empty($mobile)) {
        $errors[] = 'mobile number is required';
    }
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    if (empty($description)) {
        $errors[] = 'Description is required';
    }

    if (count($errors) > 0) {
        echo "<p style='color: red;'>The following errors occurred:</p>";
        echo "<ul style='color: red;'>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    } else {

        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host = 'mail.aruljothipipes.in';                     //Set the SMTP server to send through
            $mail->SMTPAuth = true;                                   //Enable SMTP authentication
            $mail->Username = 'support@aruljothipipes.in';                     //SMTP username
            $mail->Password = '5DceC3HGgmjbfXa';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($email, 'Client Id');
            $mail->addAddress('support@aruljothipipes.in', 'AJ Support');     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('srshankar145@gmail.com', 'Information');
            $mail->addCC('adityaramprabu@gmail.com');
            // $mail->addBCC('bcc@example.com');


            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = "Reg: Order Enquiry -  $subject";
            // $mail->Body = "Name: {$name} <br /> Email: {$email} <br /> Mobile: {$mobile} <br /> Message: {$description}";
            $mail->Body = "        
            <table style='font-family: Georgia, sans-serif; font-size: 16px;' border='1' cellpadding='5' cellspacing='0'>              
                <tr>
                    <td style='padding: 10px;'>Name:</td>
                    <td style='padding: 10px;'>{$name}</td>
                </tr>
                <tr>
                    <td style='padding: 10px;'>Email:</td>
                    <td style='padding: 10px;'>{$email}</td>
                </tr>
                <tr>
                    <td style='padding: 10px;'>Mobile:</td>
                    <td style='padding: 10px;'>{$mobile}</td>
                </tr>
                <tr>
                    <td style='padding: 10px;'>Message:</td>
                    <td style='padding: 10px;'>{$description}</td>
                </tr>
            </table>
        ";
            $mail->AltBody = "Name: {$name} \n Email: {$email} \n Mobile: {$mobile} \n Message: {$description}";
            $mail->send();
            echo "<p style='color: #1B4D3E; margin:1rem; font-size:18px; font-family: Times;'> Thank you for showing interest! we will get back to you soon.</p>";
            echo '<script>
                setTimeout(function() {
                window.location.href = "index.html";
                }, 4000); // 4 seconds delay
              </script>';
            // echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
