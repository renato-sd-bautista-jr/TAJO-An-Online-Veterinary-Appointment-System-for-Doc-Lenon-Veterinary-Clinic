<?php
// First, you need to install PHPMailer using Composer:
// composer require phpmailer/phpmailer

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $fullName = htmlspecialchars($_POST['fullName']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $petName = htmlspecialchars($_POST['petName']);
    $species = htmlspecialchars($_POST['petType']); // Changed from 'Pet Type' to match form
    $preferredDate = htmlspecialchars($_POST['preferredDate']); // Fixed spelling and removed space
    $breed = htmlspecialchars($_POST['breed']);
    $reason = htmlspecialchars($_POST['reason']);
    $notes = htmlspecialchars($_POST['notes']);
    
    // Format the date
    $date = date("F j, Y, g:i a");
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        // $mail->SMTPDebug = 2;                 // Enable verbose debug output
        $mail->isSMTP();                         // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                // Enable SMTP authentication
        
        // SECURITY ISSUE: Credentials should be stored in environment variables or a secure config file
        // Load credentials from secure config file
        $config = include 'config.php';
        $mail->Username   = $config['email_username'];
        $mail->Password   = $config['email_password'];
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587;                 // TCP port to connect to
        
        // Recipients
        $mail->setFrom($config['email_username'], 'TAHO Appointment System');
        $mail->addAddress($config['admin_email'], 'Clinic Owner');
        $mail->addReplyTo($email, $fullName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Service Request - $reason";
        
        $mail->Body = "
        <html>
        <head>
            <title>New Service Request</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                h2 {
                    color: #3498db;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                }
                .details {
                    margin-bottom: 20px;
                }
                .label {
                    font-weight: bold;
                    width: 150px;
                    display: inline-block;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 0.9em;
                    color: #777;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>New Service Request</h2>
                <p>A new service request has been submitted on $date.</p>
                
                <div class='details'>
                    <p><span class='label'>Client Name:</span> $fullName</p>
                    <p><span class='label'>Email:</span> $email</p>
                    <p><span class='label'>Phone:</span> $phone</p>
                    <p><span class='label'>Pet Name:</span> $petName</p>
                    <p><span class='label'>Pet Type:</span> $species</p>
                    <p><span class='label'>Breed:</span> $breed</p>
                    <p><span class='label'>Preferred Date:</span> $preferredDate</p>
                    <p><span class='label'>Service Requested:</span> $reason</p>
                    <p><span class='label'>Additional Notes:</span> $notes</p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message from your TAHO Appointment System.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->send();
        
        // Store request in a text file as a backup
        $logFile = "appointments.log";
        $logEntry = date('Y-m-d H:i:s') . " | $fullName | $email | $phone | $petName | $species | $breed | $preferredDate | $reason\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        // Redirect to success page
        header("Location: success.php");
        exit();
    } catch (Exception $e) {
        // Log the error
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        
        // Still save the request to the backup file
        $logFile = "appointments.log";
        $logEntry = date('Y-m-d H:i:s') . " | $fullName | $email | $phone | $petName | $species | $breed | $preferredDate | $reason\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        // Redirect to error page
        header("Location: error.php");
        exit();
    }
} else {
    // If not submitted through POST, redirect to form page
    header("Location: Appointment.php");
    exit();
}
?>