<?php
$conn = new mysqli("localhost", "root", "", "taho");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = (int)$_GET['id'];
$sql = "SELECT * FROM orders WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
  echo "<p class='text-danger text-center'>Receipt not found.</p>";
  exit;
}

$order = $result->fetch_assoc();
?>

<div class="p-4">
  <h4 class="text-primary text-center mb-3">Order Receipt</h4>
  <hr>
  <p><strong>Order ID:</strong> <?= $order['id'] ?></p>
  <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
  <p><strong>Contact Number:</strong> <?= htmlspecialchars($order['customer_number']) ?></p>
  <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
  <p><strong>Reference Number:</strong> <?= $order['reference_number'] ? htmlspecialchars($order['reference_number']) : '<span class="text-muted">N/A</span>' ?></p>
  <p><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>
  <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
  <p><strong>Date Ordered:</strong> <?= date('F d, Y h:i A', strtotime($order['created_at'])) ?></p>

  <hr>
  <h5 class="text-end text-success">Total: ₱<?= number_format($order['total_amount'], 2) ?></h5>
</div>
