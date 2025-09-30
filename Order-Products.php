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
  <title>Order Products - TAHO</title>
  <link rel="icon" href="img/LOGO.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .navbar {
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      padding: 15px 0;
      background-color: #e3f2fd;
    }
    .navbar-brand {
      font-size: 1.5rem;
      font-weight: bold;
      color: #2c3e50;
      margin-right: 2rem;
    }
    .nav-link { font-weight: 500; color: #2c3e50; }
    .nav-link:hover { color: #3498db; }
    .card img { height:160px; object-fit:cover; }
    footer {
      background-color: #e3f2fd;
      color: #2c3e50;
      padding: 15px 0;
      text-align: center;
      margin-top: 30px;
    }
  </style>
</head>
<body>
 <!-- Navbar same as index.php -->
  <nav class="navbar">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <img src="img/LOGO.png" alt="Logo" width="45" height="40" class="d-inline-block align-text-top">
        DOC LENON VETERINARY
      </a>
      <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle active" data-bs-toggle="dropdown" href="#" role="button">Services</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="pw.php">Pet Wellness</a></li>
            <li><a class="dropdown-item" href="Consultation.php">Consultation</a></li>
            <li><a class="dropdown-item" href="Vaccine.php">Vaccination</a></li>
            <li><a class="dropdown-item" href="deworming.php">Deworming</a></li>
            <li><a class="dropdown-item" href="laboratory.php">Laboratory</a></li>
            <li><a class="dropdown-item" href="Surgery.php">Surgery</a></li>
            <li><a class="dropdown-item" href="Confinement.php">Confinement</a></li>
            <li><a class="dropdown-item" href="Grooming.php">Grooming</a></li>
            <li><a class="dropdown-item" href="Pet-Boarding.php">Pet Boarding</a></li>
            <li><a class="dropdown-item active" href="Order-Products.php">Order Products</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="Contact Us.php">Contact Us</a></li>
        <li><button type="button" class="btn btn-primary" onclick="window.location.href='Appointment.php'">Book Appointment</button></li>
      </ul>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container mt-4">
    <?php if ($flash): ?>
      <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['msg']); ?></div>
    <?php endif; ?>

    <h1 class="mb-4">Order Our Products</h1>

    <!-- Product Cards (same as before) -->
    <div class="row">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
          <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
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
                  <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['Stock']; ?>" class="form-control" style="width:80px;">
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

    <!-- Cart / Checkout / Orders sections remain same -->
    <!-- (your existing code for cart, modal, view orders, admin panel goes here) -->

  </div>



  <!-- Footer same as index -->
  <footer>
    <p>&copy; ALL RIGHTS RESERVED 2025 SE FINAL - TAHO</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>