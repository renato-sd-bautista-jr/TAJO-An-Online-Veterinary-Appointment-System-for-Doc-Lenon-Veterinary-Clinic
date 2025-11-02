<?php
session_start();
include 'navbar.php';
$conn = new mysqli("localhost", "root", "", "taho");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

include_once "notiffunction.php"; // 🧩 include your notification helper

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Fetch products
$products = [];
$res = $conn->query("SELECT * FROM products ORDER BY Category, Product_Name");
while ($row = $res->fetch_assoc()) $products[] = $row;

function find_product($products, $id) {
    foreach ($products as $p) if ($p['ID'] == $id) return $p;
    return null;
}

$flash = null;

// ✅ Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add_to_cart') {
    $id = (int)$_POST['product_id'];
    $qty = max(1, (int)$_POST['quantity']);
    $product = find_product($products, $id);

    if (!$product) {
        $flash = ['type'=>'danger', 'msg'=>'Product not found.'];
    } elseif ($product['Stock'] < $qty) {
        $flash = ['type'=>'warning', 'msg'=>'Not enough stock.'];
    } else {
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
        $flash = ['type'=>'success', 'msg'=>'Added to cart.'];
    }
}

// ✅ Update Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_cart') {
    foreach ($_POST['quantities'] as $pid => $q) {
        $pid = (int)$pid;
        $q = max(0, (int)$q);
        if ($q === 0) unset($_SESSION['cart'][$pid]);
        else $_SESSION['cart'][$pid] = $q;
    }
    $flash = ['type'=>'info','msg'=>'Cart updated.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'checkout') {
    $name = trim($_POST['customer_name']);
    $num = trim($_POST['customer_number']);
    $method = $_POST['payment_method'];
    $ref = $_POST['reference_number'] ?? null;

    if ($name === '' || $num === '') {
        $flash = ['type'=>'danger','msg'=>'Please provide your name and number.'];
    } elseif (empty($_SESSION['cart'])) {
        $flash = ['type'=>'warning','msg'=>'Your cart is empty.'];
    } else {
        $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
        $fresh = [];
        $q = $conn->query("SELECT * FROM products WHERE ID IN ($ids)");
        while ($r = $q->fetch_assoc()) $fresh[$r['ID']] = $r;

        $ok = true; $total = 0;
        foreach ($_SESSION['cart'] as $pid => $qty) {
            if (!isset($fresh[$pid]) || $fresh[$pid]['Stock'] < $qty) {
                $ok = false;
                $flash = ['type'=>'danger','msg'=>'Insufficient stock for '.$fresh[$pid]['Product_Name']];
                break;
            }
            $total += $fresh[$pid]['Price'] * $qty;
        }

        if ($ok) {
            $payment_status = ($method === 'Pickup') ? 'Unpaid' : 'To Claim';
            $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_number, total_amount, status, payment_method, reference_number, payment_status) VALUES (?, ?, ?, 'Pending', ?, ?, ?)");
            $stmt->bind_param("ssdsss", $name, $num, $total, $method, $ref, $payment_status);
            $stmt->execute();
            $order_id = $stmt->insert_id;

            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $pid => $qty) {
                $p = $fresh[$pid];
                $itemStmt->bind_param("iisdi", $order_id, $pid, $p['Product_Name'], $p['Price'], $qty);
                $itemStmt->execute();
                $conn->query("UPDATE products SET Stock = Stock - $qty WHERE ID = $pid");
            }

            $_SESSION['cart'] = [];
            insertNotification($conn, $name, "New order (#$order_id) placed with $method payment.");

            $flash = ['type'=>'success','msg'=>"Order placed successfully! Your Order ID: $order_id"];
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Products - TAHO</title>
  <link rel="icon" href="img/LOGO.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8fafc; }
    .navbar { background-color: #e3f2fd; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .navbar-brand { font-weight: bold; color: #2c3e50; }
    .card { border: none; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: transform .2s; }
    .card:hover { transform: translateY(-3px); }
    .card img { height: 180px; object-fit: cover; border-radius: 15px 15px 0 0; }
    footer { background-color: #e3f2fd; color: #2c3e50; padding: 10px 0; text-align: center; margin-top: 40px; }
    .btn-primary { background-color: #3498db; border-color: #3498db; }
    .btn-primary:hover { background-color: #2980b9; }
    .alert { border-radius: 10px; }
  </style>
</head>
<body>
 <link rel="stylesheet" href="navbar.css">

<div class="container mt-4">
  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> text-center"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>

  <h2 class="mb-4 text-center text-primary fw-bold">Available Products</h2>
  <div class="row">
    <?php foreach ($products as $p): ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <img src="<?= htmlspecialchars($p['Image'] ?: 'img/placeholder.jpg') ?>" class="card-img-top">
          <div class="card-body">
            <h5><?= htmlspecialchars($p['Product_Name']) ?></h5>
            <p class="text-muted mb-1"><?= htmlspecialchars($p['Category']) ?></p>
            <p class="mb-2">₱<?= number_format($p['Price'],2) ?></p>
            <p class="small text-secondary">Stock: <?= (int)$p['Stock'] ?></p>
            <form method="post">
              <input type="hidden" name="action" value="add_to_cart">
              <input type="hidden" name="product_id" value="<?= $p['ID'] ?>">
              <div class="d-flex gap-2">
                <input type="number" name="quantity" value="1" min="1" max="<?= $p['Stock'] ?>" class="form-control" style="width:80px;">
                <button class="btn btn-primary w-100" <?= $p['Stock']<=0?'disabled':'' ?>>Add</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Cart -->
  <div class="card p-4 mt-5">
    <h4>Your Cart</h4>
    <?php if (empty($_SESSION['cart'])): ?>
      <p class="text-muted">Your cart is empty.</p>
    <?php else: ?>
      <form method="post">
        <input type="hidden" name="action" value="update_cart">
        <table class="table">
          <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
          <tbody>
          <?php $total = 0; foreach ($_SESSION['cart'] as $pid => $qty): 
            $p = find_product($products, $pid); if (!$p) continue;
            $sub = $p['Price'] * $qty; $total += $sub; ?>
            <tr>
              <td><?= htmlspecialchars($p['Product_Name']) ?></td>
              <td>₱<?= number_format($p['Price'],2) ?></td>
              <td><input type="number" name="quantities[<?= $pid ?>]" value="<?= $qty ?>" class="form-control" min="1" max="<?= $p['Stock'] ?>" style="width:80px;"></td>
              <td>₱<?= number_format($sub,2) ?></td>
              <td><a href="?remove=<?= $pid ?>" class="btn btn-sm btn-danger">Remove</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center">
          <strong>Total: ₱<?= number_format($total,2) ?></strong>
          <div>
            <button class="btn btn-secondary">Update</button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#checkoutModal">Checkout</button>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="checkout">
        <div class="modal-header">
          <h5 class="modal-title text-primary">Checkout</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Full Name</label>
          <input type="text" name="customer_name" class="form-control mb-3" required>

          <label class="form-label">Contact Number</label>
          <input type="text" name="customer_number" class="form-control mb-3" required>

          <label class="form-label">Mode of Payment</label>
          <select name="payment_method" id="payment_method" class="form-select mb-3" required>
            <option value="Pickup">Pickup / Pay on-site</option>
            <option value="GCash">GCash</option>
          </select>

          <div id="gcashDetails" style="display:none;">
            <p class="fw-semibold">Scan this QR to pay:</p>
            <img src="img/gcash_qr.jpg" alt="GCash QR" class="img-fluid rounded mb-3 border">
            <label class="form-label">Reference Number</label>
            <input type="text" name="reference_number" class="form-control" placeholder="Enter GCash reference number">
          </div>

          <small class="text-muted">Your order will be reviewed once payment is verified.</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Place Order</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer><p>&copy; ALL RIGHTS RESERVED 2025 SE FINAL - TAHO</p></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const paymentSelect = document.getElementById('payment_method');
  const gcashDetails = document.getElementById('gcashDetails');

  function toggleGCashDetails() {
    gcashDetails.style.display = (paymentSelect.value === 'GCash') ? 'block' : 'none';
  }

  if (paymentSelect) {
    paymentSelect.addEventListener('change', toggleGCashDetails);
  }

  const checkoutModal = document.getElementById('checkoutModal');
  if (checkoutModal) {
    checkoutModal.addEventListener('shown.bs.modal', toggleGCashDetails);
  }
});
</script>

</body>
</html>
