<?php
function autoCancelConflictingAppointments($conn) {
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');
    $sql = "UPDATE appointments 
            SET status = 'Cancelled' 
            WHERE (appointment_date < ? OR (appointment_date = ? AND appointment_time < ?)) 
            AND status != 'Completed'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $currentDate, $currentDate, $currentTime);
    $stmt->execute();
}
?>