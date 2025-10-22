<?php
session_start();
$conn = new mysqli("localhost", "root", "", "taho");
if ($conn->connect_error) die("DB connection failed");

include 'notiffunction.php'; // ✅ import reusable notification functions

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit;
}

$id = (int)$_POST['id'];
$status = $_POST['status'];
$username = $_SESSION['admin_username'] ?? 'Admin';

// ✅ Restore stock if rejected
if ($status === 'Rejected') {
    $items = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $id");
    while ($row = $items->fetch_assoc()) {
        $conn->query("UPDATE products SET Stock = Stock + {$row['quantity']} WHERE ID = {$row['product_id']}");
    }
}

// ✅ Update order status
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();
$stmt->close();

// ✅ Add notification (for accepted/rejected)
if (in_array($status, ['Accepted', 'Rejected'])) {
    $action = "Order #$id has been $status by admin.";
    insertNotification($conn, $username, $action);
}

echo "ok";
?>
