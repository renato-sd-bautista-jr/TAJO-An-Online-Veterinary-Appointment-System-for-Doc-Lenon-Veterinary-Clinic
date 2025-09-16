<?php
// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "taho"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Get appointment ID from request
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid appointment ID']);
    exit;
}

// Fetch appointment data
$sql = "SELECT *, DATE_FORMAT(appointment_time, '%l:%i %p') as formatted_time FROM appointments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'Appointment not found']);
}

$stmt->close();
$conn->close();
?>