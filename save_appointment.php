<?php
session_start();

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "taho";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Appointment ID is required']);
    exit;
}

$id = intval($_POST['id']);

// 1. Get appointment details including owner email
$stmt = $conn->prepare("SELECT appointment_date, appointment_time, owner_name, owner_email, pet_name, service_type FROM appointments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$apt = $result->fetch_assoc();
$stmt->close();

if (!$apt) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Appointment not found']);
    exit;
}

$date = $apt['appointment_date'];
$time = $apt['appointment_time'];
$ownerName = $apt['owner_name'];
$ownerEmail = $apt['owner_email'];
$petName = $apt['pet_name'];
$service = $apt['service_type'];

// 2. Confirm this appointment
$stmt = $conn->prepare("UPDATE appointments SET status = 'Confirmed' WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// 3. Cancel other appointments at same date/time
$stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_date = ? AND appointment_time = ? AND id != ?");
$stmt->bind_param("ssi", $date, $time, $id);
$stmt->execute();
$stmt->close();

// 4. Send email notification to owner using PHPMailer
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Change to your SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'yourgmail@gmail.com'; // SMTP username
    $mail->Password   = 'your_app_password';    // SMTP password / app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom('no-reply@yourclinic.com', 'Doc Lenon Veterinary Clinic');
    $mail->addAddress($ownerEmail, $ownerName);

    // Content
    $mail->isHTML(false);
    $mail->Subject = "Your Appointment is Confirmed!";
    $mail->Body    = "Hello $ownerName,

Your appointment for $petName ($service) on $date at $time has been confirmed.

Thank you for choosing our clinic.

- Doc Lenon Veterinary Clinic";

    $mail->send();
} catch (Exception $e) {
    // Optional: log error
    error_log("Mailer Error: " . $mail->ErrorInfo);
}

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);

$conn->close();
?>
