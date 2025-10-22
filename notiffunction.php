<?php
// notiffunction.php
// Safe function definitions: only declare if not already declared.

if (!function_exists('getUnreadCount')) {
    function getUnreadCount($conn) {
        $result = $conn->query("SELECT COUNT(*) AS unread_count FROM notifications WHERE readstatus = 0");
        if (!$result) return 0;
        $row = $result->fetch_assoc();
        return isset($row['unread_count']) ? (int)$row['unread_count'] : 0;
    }
}

if (!function_exists('insertNotification')) {
    function insertNotification($conn, $username, $action) {
        $date = date('Y-m-d H:i:s');
        $readStatus = 0;
        $stmt = $conn->prepare("INSERT INTO notifications (username, action, date, readstatus) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssi", $username, $action, $date, $readStatus);
            $stmt->execute();
            $stmt->close();
        }
    }
}

if (!function_exists('getAllNotifications')) {
    function getAllNotifications($conn) {
        $sql = "SELECT * FROM notifications ORDER BY date DESC";
        $result = $conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}

if (!function_exists('markAllRead')) {
    function markAllRead($conn) {
        $conn->query("UPDATE notifications SET readstatus = 1 WHERE readstatus = 0");
    }
}
?>
