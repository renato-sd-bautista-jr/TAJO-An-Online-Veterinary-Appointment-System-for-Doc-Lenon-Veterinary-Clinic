<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "taho");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$id = intval($_POST['id']);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid appointment ID']);
    exit;
}

// Get date and time
$res = $conn->query("SELECT appointment_date, appointment_time FROM appointments WHERE id = $id");
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Appointment not found']);
    exit;
}

$row = $res->fetch_assoc();
$date = $conn->real_escape_string($row['appointment_date']);
$time = $conn->real_escape_string($row['appointment_time']);

// Confirm selected
$conn->query("UPDATE appointments SET status = 'Confirmed' WHERE id = $id");

// Cancel others
$conn->query("UPDATE appointments 
              SET status = 'Cancelled' 
              WHERE appointment_date = '$date' 
              AND appointment_time = '$time' 
              AND id != $id 
              AND status = 'Pending'");

echo json_encode(['success' => true]);
$conn->close();
?>
