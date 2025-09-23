<?php
session_start();

// DB connection
$conn = new mysqli("localhost","root","","taho");
if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Admin auth
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php"); exit;
}

// Filters
$status_filter = $_GET['status'] ?? 'all';

// Get orders (header only)
// get the orders first (header info only)
$sql = "SELECT id, customer_name, customer_number, total_amount, status, created_at 
        FROM orders WHERE 1=1";
if ($status_filter !== 'all') {
    $sql .= " AND status='".$conn->real_escape_string($status_filter)."'";
}
$sql .= " ORDER BY created_at DESC";
$res = $conn->query($sql);
$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
// Fetch all order items grouped by order_id
$order_items = [];
$res_items = $conn->query("SELECT order_id, product_name, price, quantity FROM order_items");
if ($res_items) {
    while ($row = $res_items->fetch_assoc()) {
        $order_items[$row['order_id']][] = $row;
    }
}
// Stats
$stats_sql = "SELECT 
 SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) as pending,
 SUM(CASE WHEN status='Accepted' THEN 1 ELSE 0 END) as accepted,
 SUM(CASE WHEN status='Rejected' THEN 1 ELSE 0 END) as rejected
 FROM orders";
$stats = $conn->query($stats_sql)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Order Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
/* reuse your styles */
body{background:#f8f9fa;padding-top:20px;overflow-x:hidden;}
.admin-header{background:#4DA6FF;color:white;padding:15px;margin-bottom:30px;border-radius:5px;display:flex;justify-content:space-between;align-items:center;}
.card{border-radius:8px;box-shadow:0 4px 6px rgba(0,0,0,0.1);margin-bottom:30px;}
.card-header{background:#4DA6FF;color:white;font-weight:bold;}
.btn-primary{background:#4DA6FF;border-color:#4DA6FF;}
.btn-primary:hover{background:#3a8fd0;border-color:#3a8fd0;}
.paw-logo{color:#4DA6FF;margin-right:10px;}
.sidebar{height:100%;width:0;position:fixed;z-index:1000;top:0;left:0;background:#222;overflow-x:hidden;transition:0.5s;padding-top:60px;box-shadow:2px 0 5px rgba(0,0,0,0.2);}
.sidebar a{padding:12px 15px;text-decoration:none;font-size:18px;color:#fff;display:block;transition:0.3s;border-left:3px solid transparent;}
.sidebar a:hover{background:#333;border-left:3px solid #4DA6FF;}
.sidebar a.active{background:#2c2c2c;border-left:3px solid #4DA6FF;font-weight:bold;}
.sidebar .close-btn{position:absolute;top:10px;right:15px;font-size:30px;}
.main-content{transition:margin-left .5s;padding:16px;}
.burger-menu{font-size:24px;cursor:pointer;color:white;margin-right:15px;}
.stats-card{border-left:4px solid;background:white;padding:15px;margin-bottom:15px;box-shadow:0 2px 4px rgba(0,0,0,0.05);}
.stats-card-pending{border-left-color:#ffc107;}
.stats-card-accepted{border-left-color:#198754;}
.stats-card-rejected{border-left-color:#dc3545;}
.stats-number{font-size:1.5rem;font-weight:bold;}
.stats-title{font-size:.9rem;color:#6c757d;text-transform:uppercase;}
</style>
</head>
<body>
<!-- Sidebar Navigation -->
    <div id="mySidebar" class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-paw"></i> Doc Lenon
        </div>
        <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">&times;</a>
        <a href="admin.php?page=products"class="active">
            <i class="fas fa-box"></i> Products Inventory
        </a>
        <a href="post.php" >
            <i class="fas fa-blog"></i> Post Management
        </a>
        <a href="calendar.php">
            <i class="fas fa-calendar-alt"></i> Appointment Calendar
        </a>
        <a href="history1.php">
            <i class="fas fa-history"></i> Appointment History
        </a>
        <a href="ordermanagement.php">
            <i class="fas fa-box"></i> Order Management
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

<div id="main" class="main-content">
  <div class="admin-header">
    <div class="d-flex align-items-center">
      <span class="burger-menu" onclick="openNav()"><i class="fas fa-bars"></i></span>
      <h2><i class="fas fa-paw paw-logo"></i> Order Management</h2>
    </div>
    <div>
      <span class="text-light me-3">Welcome, Admin</span>
      <a href="?logout=1" class="btn btn-light btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>

<div class="container">
  <div class="row mb-4">
    <div class="col-md-4"><div class="stats-card stats-card-pending"><div class="stats-number"><?= $stats['pending'] ?></div><div class="stats-title">Pending</div></div></div>
    <div class="col-md-4"><div class="stats-card stats-card-accepted"><div class="stats-number"><?= $stats['accepted'] ?></div><div class="stats-title">Accepted</div></div></div>
    <div class="col-md-4"><div class="stats-card stats-card-rejected"><div class="stats-number"><?= $stats['rejected'] ?></div><div class="stats-title">Rejected</div></div></div>
  </div>

  <div class="card">
    <div class="card-header"><i class="fas fa-box me-2"></i>Orders</div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="ordersTable" class="table table-striped table-hover">
          <thead>
            <tr>
                <th>ID</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Number</th>
        <th>Total</th>
        <th>Status</th>
        <th>Items</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($orders as $order): ?>
<tr>
  <td><?= $order['id'] ?></td>
  <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
  <td><?= htmlspecialchars($order['customer_name']) ?></td>
  <td><?= htmlspecialchars($order['customer_number']) ?></td>
  <td>₱<?= number_format($order['total_amount'],2) ?></td>
  <td>
    <span class="badge 
      <?= $order['status']=='Pending'?'bg-warning':
         ($order['status']=='Accepted'?'bg-success':
         ($order['status']=='Rejected'?'bg-danger':'bg-secondary')); ?>">
      <?= $order['status'] ?>
    </span>
  </td>
  <td>
    <?php if (!empty($order_items[$order['id']])): ?>
      <ul class="mb-0 ps-3">
        <?php foreach ($order_items[$order['id']] as $item): ?>
          <li>
            <?= htmlspecialchars($item['product_name']) ?> 
            (₱<?= number_format($item['price'],2) ?> × <?= $item['quantity'] ?>)
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <span class="text-muted">No items</span>
    <?php endif; ?>
    <?php if($order['status']=='Pending'): ?>
      <button class="btn btn-sm btn-success accept-btn" data-id="<?= $order['id'] ?>"><i class="fas fa-check"></i></button>
      <button class="btn btn-sm btn-danger reject-btn" data-id="<?= $order['id'] ?>"><i class="fas fa-times"></i></button>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>
<div class="modal fade" id="itemsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Order Items</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="itemsModalBody"></div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function(){
  $('#ordersTable').DataTable();

  $('.accept-btn').click(function(){
    let id=$(this).data('id');
    if(confirm('Accept this order?')){
      $.post('update_order_status.php',{id:id,status:'Accepted'},function(){
        location.reload();
      });
    }
  });
  $('.reject-btn').click(function(){
    let id=$(this).data('id');
    if(confirm('Reject this order?')){
      $.post('update_order_status.php',{id:id,status:'Rejected'},function(){
        location.reload();
      });
    }
  });
});

function openNav(){document.getElementById("mySidebar").style.width="250px";document.getElementById("main").style.marginLeft="250px";}
function closeNav(){document.getElementById("mySidebar").style.width="0";document.getElementById("main").style.marginLeft="0";}

$('.view-items').click(function(){
  let id=$(this).data('id');
  $.get('get_order_items.php',{id:id},function(html){
    $('#itemsModalBody').html(html);
    new bootstrap.Modal(document.getElementById('itemsModal')).show();
  });
});

</script>
</body>
</html>
