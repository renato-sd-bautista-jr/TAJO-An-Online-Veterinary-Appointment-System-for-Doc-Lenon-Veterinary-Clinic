<?php
session_start();
ob_clean();

$conn = new mysqli("localhost", "root", "", "taho");
header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// Security check (same as your save_appointment.php)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

// Validate ID
$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid appointment ID']);
    exit;
}

// Check if appointment exists
$res = $conn->query("SELECT id, status FROM appointments WHERE id = $id");
if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Appointment not found']);
    exit;
}

$row = $res->fetch_assoc();

// Only allow completing confirmed appointments
if ($row['status'] !== 'Confirmed') {
    echo json_encode(['success' => false, 'error' => 'Only confirmed appointments can be completed']);
    exit;
}

// Update appointment to Completed
$update = $conn->query("UPDATE appointments SET status = 'Completed' WHERE id = $id");
if (!$update) {
    echo json_encode(['success' => false, 'error' => 'Failed to update appointment']);
    exit;
}

echo json_encode(['success' => true]);
$conn->close();
exit;
?>
