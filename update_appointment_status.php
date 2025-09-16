<?php
// Start session for admin authentication
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if required parameters are provided
if (!isset($_POST['id']) || !isset($_POST['status'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$appointment_id = (int)$_POST['id'];
$status = $_POST['status'];

// Validate status value
$valid_statuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "taho"; // Change to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Prepare SQL query with parameter binding for security
$sql = "UPDATE appointments SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $appointment_id);

// Execute update
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Successfully updated
        echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully']);
    } else {
        // No rows affected, appointment might not exist
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Appointment not found or status unchanged']);
    }
} else {
    // Update failed
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to update appointment status']);
}

// Optional: Log the status change
$log_sql = "INSERT INTO appointment_logs (appointment_id, action, performed_by, details) 
            VALUES (?, ?, ?, ?)";
$log_stmt = $conn->prepare($log_sql);

if ($log_stmt) {
    $action = "Status Change";
    $performed_by = "Admin"; // You could store admin username in session and use it here
    $details = "Status changed to " . $status;
    
    $log_stmt->bind_param("isss", $appointment_id, $action, $performed_by, $details);
    $log_stmt->execute();
    $log_stmt->close();
}

// Close statement and connection
$stmt->close();
$conn->close();
?>