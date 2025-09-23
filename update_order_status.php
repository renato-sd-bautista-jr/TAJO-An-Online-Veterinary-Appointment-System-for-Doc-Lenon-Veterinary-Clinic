
<?php
session_start();
$conn=new mysqli("localhost","root","","taho");
if($conn->connect_error) die("DB error");

if(!isset($_SESSION['admin_logged_in'])) {http_response_code(403);exit;}

$id=(int)$_POST['id'];
$status=$_POST['status'];

// If rejected, restore stock
if ($status === 'Rejected') {
    $items = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id=$id");
    if ($items) {
        while ($row = $items->fetch_assoc()) {
            $conn->query("UPDATE products SET Stock = Stock + {$row['quantity']} WHERE ID = {$row['product_id']}");
        }
    }
}

// Update order status (only once)
$stmt=$conn->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->bind_param("si",$status,$id);
$stmt->execute();

echo "ok";