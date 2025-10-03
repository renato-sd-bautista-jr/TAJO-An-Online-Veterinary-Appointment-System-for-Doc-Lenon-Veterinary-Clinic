<?php
// order-products.php
// Ordering system with cart management, multi-item ordering, stock integration,
// admin confirmation, user cancellation (by name+number).

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "taho";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['resetcart'])) {
    $_SESSION['cart'] = [];
    echo "Cart has been reset";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST DATA: ' . print_r($_POST, true));
    error_log('CART BEFORE: ' . print_r($_SESSION['cart'], true));
}
if (isset($_GET['resetcart'])) {
    $_SESSION['cart'] = [];
    echo "Cart has been reset";
    exit;
}

function find_product($products, $id) {
    foreach ($products as $p) {
        if ((int)$p['ID'] === (int)$id) {
            return $p;
        }
    }
    return null;
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];

    
}



$products = [];
$sql = "SELECT * FROM products ORDER BY Category, Product_Name";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['action']) 
    && $_POST['action'] === 'add_to_cart') {

    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $qty        = max(1, (int)$_POST['quantity']);

    // ✅ Only allow IDs > 0
    if ($product_id <= 0) {
        $flash = ['type'=>'danger','msg'=>'Invalid product.'];
    } else {
        $product = find_product($products, $product_id);
        if (!$product) {
            $flash = ['type'=>'danger','msg'=>'Product not found.'];
        } elseif ($product['Stock'] < $qty) {
            $flash = ['type'=>'warning','msg'=>'Not enough stock to add that quantity.'];
        } else {
            if (!isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = 0;
            }
            $_SESSION['cart'][$product_id] += $qty;
            $flash = ['type'=>'success','msg'=>'Added to cart.'];
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_cart') {
    foreach ($_POST['quantities'] as $pid => $q) {
        $pid = (int)$pid;
        $q = max(0, (int)$q);
        if ($q === 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $prod = find_product($products, $pid);
            if ($prod && $q <= $prod['Stock']) {
                $_SESSION['cart'][$pid] = $q;
            } else {
                $_SESSION['cart'][$pid] = min($q, $prod ? (int)$prod['Stock'] : $q);
            }
        }
    }
    $flash = ['type'=>'success','msg'=>'Cart updated.'];
}

if (isset($_GET['remove'])) {
    $rid = (int)$_GET['remove'];
    unset($_SESSION['cart'][$rid]);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $customer_name   = trim($_POST['customer_name']);
    $customer_number = trim($_POST['customer_number']);

    if ($customer_name === '' || $customer_number === '') {
        $flash = ['type'=>'danger','msg'=>'Please provide your name and number.'];
    } elseif (empty($_SESSION['cart'])) {
        $flash = ['type'=>'warning','msg'=>'Your cart is empty.'];
    } else {
        $fresh = [];

         $ids = array_keys($_SESSION['cart']);
        $ids = array_filter($ids, function($v){ return (int)$v > 0; });
        $ids = array_map('intval', $ids); // force integers

        if (!empty($ids)) {
            $in = implode(',', $ids);
            $q = $conn->query("SELECT * FROM products WHERE ID IN ($in)");
            while ($r = $q->fetch_assoc()) {
                $id = (int)$r['ID'];
                $r['Stock'] = (int)$r['Stock'];
                $r['Price'] = (float)$r['Price'];
                $fresh[$id] = $r;
            }
        }

        $ok = true;
        $total = 0.0;
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $pid = (int)$pid;
            $qty = (int)$qty;
            // check stock
            if (!isset($fresh[$pid])) {
                $ok = false;
                $flash = ['type'=>'danger','msg'=>"Product $pid not found."];
                break;
            }
            if ($fresh[$pid]['Stock'] < $qty) {
                $ok = false;
                $flash = ['type'=>'danger','msg'=>"Not enough stock for ".$fresh[$pid]['Product_Name'].". Available: ".$fresh[$pid]['Stock'].", you wanted $qty."];
                break;
            }
            $total += $fresh[$pid]['Price'] * $qty;
        }

        if ($ok) {
            // create order
            $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_number, total_amount, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->bind_param('ssd', $customer_name, $customer_number, $total);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();

            // insert items & update stock
            $insertItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $pid => $qty) {
                $pid = (int)$pid;
                $qty = (int)$qty;
                $p = $fresh[$pid];
                $insertItem->bind_param('iisdi', $order_id, $pid, $p['Product_Name'], $p['Price'], $qty);
                $insertItem->execute();

                $stmt2 = $conn->prepare("UPDATE products SET Stock = Stock - ? WHERE ID = ?");
                $stmt2->bind_param('ii', $qty, $pid);
                $stmt2->execute();
                $stmt2->close();
            }
            $insertItem->close();

            $_SESSION['cart'] = [];
            
            $flash = ['type'=>'success','msg'=>'Order placed successfully. Your Order ID: ' . $order_id . '. Admin will confirm soon.'];
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Order placed successfully. Your Order ID: ' . $order_id . '. Admin will confirm soon.'];
header("Location: ".$_SERVER['PHP_SELF']);
exit;
        }
    }
}

$user_orders = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'view_orders') {
    $v_name = trim($_POST['v_name']);
    $v_number = trim($_POST['v_number']);
    if ($v_name === '' || $v_number === '') {
        $flash = ['type'=>'danger','msg'=>'Provide name and number to view orders.'];
    } else {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE customer_name = ? AND customer_number = ? ORDER BY created_at DESC");
        $stmt->bind_param('ss', $v_name, $v_number);
        $stmt->execute();
        $resO = $stmt->get_result();
        while ($o = $resO->fetch_assoc()) {
            $oids = $o['ID'];
            $itR = $conn->query("SELECT * FROM order_items WHERE order_id = " . (int)$oids);
            $items = [];
            while ($it = $itR->fetch_assoc()) $items[] = $it;
            $o['items'] = $items;
            $user_orders[] = $o;
        }
        $stmt->close();
    }
}

if (isset($_GET['cancel_order'])) {
    $cancel_id = (int)$_GET['cancel_order'];
    $cname = isset($_GET['name']) ? trim($_GET['name']) : '';
    $cnum = isset($_GET['number']) ? trim($_GET['number']) : '';
    if ($cname === '' || $cnum === '') {
        $flash = ['type'=>'danger','msg'=>'Name and number required to cancel order. Use the "View Orders" form to cancel.'];
    } else {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_name = ? AND customer_number = ? AND status = 'Pending'");
        $stmt->bind_param('iss', $cancel_id, $cname, $cnum);
        $stmt->execute();
        $resC = $stmt->get_result();
        if ($resC->num_rows === 0) {
            $flash = ['type'=>'danger','msg'=>'Order not found, not pending, or information does not match.'];
        } else {
            $itR = $conn->query("SELECT * FROM order_items WHERE order_id = " . $cancel_id);
            while ($it = $itR->fetch_assoc()) {
                $pid = (int)$it['product_id'];
                $qty = (int)$it['quantity'];
                $stmt2 = $conn->prepare("UPDATE products SET Stock = Stock + ? WHERE id = ?");
                $stmt2->bind_param('ii', $qty, $pid);
                $stmt2->execute();
                $stmt2->close();
            }
            $u = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
            $u->bind_param('i', $cancel_id);
            $u->execute();
            $u->close();

            $flash = ['type'=>'success','msg'=>'Order cancelled and items restocked.'];
        }
        $stmt->close();
    }
}

// Close DB connection
$conn->close();

// --- HTML / UI ---
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products & Ordering - TAHO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card img { height:160px; object-fit:cover; }
        .navbar { background:#e3f2fd; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="#">DOC LENON VETERINARY</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="Index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
      </ul>
      <div>
        <button class="btn btn-outline-primary" onclick="document.getElementById('viewCart').scrollIntoView();">Cart (<?php echo array_sum(array_values($_SESSION['cart'])); ?>)</button>
        <!-- Admin toggle for demo purposes: set session is_admin -->
        <?php if (empty($_SESSION['is_admin'])): ?>
            <a href="?become_admin=1" class="btn btn-sm btn-secondary ms-2">Become Admin (demo)</a>
        <?php else: ?>
            <a href="?logout_admin=1" class="btn btn-sm btn-danger ms-2">Logout Admin</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['msg']); ?></div>
    <?php endif; ?>

    <h1 class="mb-3">Our Products</h1>
    <div class="row">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($product['Image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['Image']); ?>" class="card-img-top" alt="">
                        <?php else: ?>
                            <img src="img/placeholder.jpg" class="card-img-top" alt="">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($product['Product_Name']); ?></h5>
                            <p>Category: <?php echo htmlspecialchars($product['Category']); ?></p>
                            <p>Price: ₱<?php echo number_format($product['Price'],2); ?></p>
                            <p>Stock: <?php echo (int)$product['Stock']; ?></p>
                            <form method="post" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$product['ID']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['Stock']; ?>">
                                <button class="btn btn-primary" <?php echo ((int)$product['Stock']<=0)?'disabled':''; ?>>Add</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">No products found.</div>
        <?php endif; ?>
    </div>

    <!-- Cart Section -->
    <div id="viewCart" class="card p-3 mt-4">
        <h3>Cart</h3>
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="action" value="update_cart">
                <table class="table">
                    <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody>
                        <?php
                        $subtotal = 0;
                        foreach ($_SESSION['cart'] as $pid => $qty):
                            $p = find_product($products, $pid);
                            if (!$p) continue;
                            $line = $p['Price'] * $qty;
                            $subtotal += $line;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['Product_Name']); ?></td>
                            <td>₱<?php echo number_format($p['Price'],2); ?></td>
                            <td><input type="number" name="quantities[<?php echo $pid; ?>]" value="<?php echo $qty; ?>" min="0" max="<?php echo (int)$p['Stock']; ?>" class="form-control" style="width:100px;"></td>
                            <td>₱<?php echo number_format($line,2); ?></td>
                            <td><a class="btn btn-sm btn-danger" href="?remove=<?php echo $pid; ?>">Remove</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center">
                    <div><strong>Total: ₱<?php echo number_format($subtotal,2); ?></strong></div>
                    <div>
                        <button class="btn btn-secondary">Update Cart</button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#checkoutModal">Checkout</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post">
            <input type="hidden" name="action" value="checkout">
            <div class="modal-header"><h5 class="modal-title">Checkout</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Number</label>
                    <input type="text" name="customer_number" class="form-control" required>
                </div>
                <p class="small text-muted">Only name and number required. After placing order you'll get an Order ID shown in the success message.</p>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary">Place Order</button></div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Orders (for customers) -->
    <div class="card p-3 mt-4">
        <h4>View / Cancel Your Orders</h4>
        <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="action" value="view_orders">
            <div class="col-md-5">
                <label class="form-label">Name</label>
                <input type="text" name="v_name" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Number</label>
                <input type="text" name="v_number" class="form-control" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary">View Orders</button>
            </div>
        </form>

        <?php if (!empty($user_orders)): ?>
            <hr>
            <?php foreach ($user_orders as $o): ?>
                <div class="border p-3 mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>Order #<?php echo $o['ID']; ?></strong>
                            <div>Placed: <?php echo $o['created_at']; ?></div>
                            <div>Status: <?php echo $o['status']; ?></div>
                        </div>
                        <div><strong>₱<?php echo number_format($o['total_amount'],2); ?></strong></div>
                    </div>
                    <ul>
                        <?php foreach ($o['items'] as $it): ?>
                            <li><?php echo htmlspecialchars($it['product_name']); ?> x <?php echo (int)$it['quantity']; ?> — ₱<?php echo number_format($it['price'],2); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($o['status'] === 'Pending'): ?>
                        <a class="btn btn-sm btn-danger" href="?cancel_order=<?php echo $o['ID']; ?>&name=<?php echo urlencode($o['customer_name']); ?>&number=<?php echo urlencode($o['customer_number']); ?>">Cancel Order</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Admin Dashboard (simple) -->
    <?php if (!empty($_SESSION['is_admin'])): ?>
        <div class="card p-3 mt-4">
            <h4>Admin Dashboard — Orders</h4>
            <?php if (empty($admin_orders)): ?>
                <p>No orders yet.</p>
            <?php else: ?>
                <?php foreach ($admin_orders as $a): ?>
                    <div class="border p-3 mb-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Order #<?php echo $a['ID']; ?></strong>
                                <div><?php echo htmlspecialchars($a['customer_name']); ?> — <?php echo htmlspecialchars($a['customer_number']); ?></div>
                                <div>Placed: <?php echo $a['created_at']; ?></div>
                                <div>Status: <?php echo $a['status']; ?></div>
                            </div>
                            <div>
                                <a class="btn btn-sm btn-success" href="?admin_action=confirm&order_id=<?php echo $a['ID']; ?>">Confirm</a>
                                <a class="btn btn-sm btn-danger" href="?admin_action=cancel&order_id=<?php echo $a['ID']; ?>">Cancel</a>
                            </div>
                        </div>
                        <ul>
                            <?php foreach ($a['items'] as $it): ?>
                                <li><?php echo htmlspecialchars($it['product_name']); ?> x <?php echo (int)$it['quantity']; ?> — ₱<?php echo number_format($it['price'],2); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<footer class="text-center mt-4 py-3">&copy; ALL RIGHTS RESERVED 2025 SE FINAL - TAHO</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>