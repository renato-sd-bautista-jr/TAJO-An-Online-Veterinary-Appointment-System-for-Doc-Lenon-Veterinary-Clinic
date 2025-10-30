<?php
require_once 'db_connection.php'; // update with your actual DB connection file
header('Content-Type: application/json');

if (!isset($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];

// Fetch booked/confirmed slots
$stmt = $conn->prepare("
    SELECT appointment_time, status 
    FROM appointments 
    WHERE appointment_date = ?
      AND (status = 'Confirmed' OR status = 'Booked')
");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$booked_slots = [];
while ($row = $result->fetch_assoc()) {
    // Convert "08:00:00" → "8:00 AM"
    $formatted_time = date("g:i A", strtotime($row['appointment_time']));
    $booked_slots[] = [
        'time' => $formatted_time,
        'status' => $row['status']
    ];
}

echo json_encode($booked_slots);
exit;
