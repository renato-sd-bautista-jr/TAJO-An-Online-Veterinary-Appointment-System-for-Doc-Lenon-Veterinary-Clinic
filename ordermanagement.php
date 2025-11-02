<?php
session_start();

include 'sidebar.php';
// DB connecton
$conn = new mysqli("localhost","root","","taho");
if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);
include 'notiffunction.php';
include 'notificationmodal.php';
// Fetch unread count for notification bell
$unread_count = getUnreadCount($conn);



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
$stats_sql = "
SELECT 
    -- Counts by order status
    SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN status='Accepted' THEN 1 ELSE 0 END) AS accepted,
    SUM(CASE WHEN status='Rejected' THEN 1 ELSE 0 END) AS rejected,
    SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) AS completed,

    -- Total sales (all)
    SUM(total_amount) AS total_sales,

    -- Total amount paid
    SUM(CASE WHEN payment_status='Paid' THEN total_amount ELSE 0 END) AS total_paid,

    -- Total unpaid (only if NOT completed)
    SUM(
        CASE 
            WHEN payment_status!='Paid' AND status!='Completed' THEN total_amount 
            ELSE 0 
        END
    ) AS total_unpaid,

    -- Count by payment method
    SUM(CASE WHEN payment_method='GCash' THEN 1 ELSE 0 END) AS gcash_count,
    SUM(CASE WHEN payment_method='Pickup' THEN 1 ELSE 0 END) AS pickup_count
FROM orders
";

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
body {
  background: #f8f9fa;
  padding-top: 20px;
  overflow-x: hidden;
  font-family: "Segoe UI", Roboto, sans-serif;
  color: #212529;
}

/* Header */
.admin-header {
  background: #4DA6FF;
  color: white;
  padding: 15px 25px;
  margin-bottom: 30px;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.burger-menu {
  font-size: 24px;
  cursor: pointer;
  color: white;
  margin-right: 15px;
}

.paw-logo {
  color: white;
  margin-right: 10px;
}

/* Cards */
.card {
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
  background: white;
}

.card-header {
  background: #4DA6FF;
  color: white;
  font-weight: bold;
  border-radius: 8px 8px 0 0;
}

/* Buttons */
.btn-primary {
  background: #4DA6FF;
  border-color: #4DA6FF;
}
.btn-primary:hover {
  background: #3a8fd0;
  border-color: #3a8fd0;
}

/* Sidebar */
.sidebar {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 1000;
  top: 0;
  left: 0;
  background: #222;
  overflow-x: hidden;
  transition: width 0.4s ease;
  padding-top: 60px;
  box-shadow: 2px 0 6px rgba(0, 0, 0, 0.2);
}
.sidebar-header {
  position: absolute;
  top: 15px;
  left: 20px;
  font-size: 22px;
  color: #4DA6FF;
  font-weight: 600;
}
.sidebar a {
  padding: 12px 18px;
  text-decoration: none;
  font-size: 18px;
  color: #ddd;
  display: block;
  border-left: 3px solid transparent;
  transition: all 0.3s ease;
}
.sidebar a:hover {
  background: #333;
  border-left: 3px solid #4DA6FF;
  color: #fff;
}
.sidebar a.active {
  background: #2c2c2c;
  border-left: 3px solid #4DA6FF;
  color: #fff;
  font-weight: 600;
}
.sidebar .close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 30px;
  color: #aaa;
  transition: 0.3s;
}
.sidebar .close-btn:hover {
  color: #fff;
}

/* Main content transition */
.main-content {
  transition: margin-left 0.4s ease;
  padding: 16px;
}

/* Stats Cards */
.stats-card {
  border-left: 4px solid;
  background: white;
  padding: 15px;
  margin-bottom: 15px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
.stats-card-pending { border-left-color: #ffc107; }
.stats-card-accepted { border-left-color: #198754; }
.stats-card-rejected { border-left-color: #dc3545; }
.stats-card-total { background-color: #f9fafb; }
.stats-card-paid { background-color: #d1fae5; }
.stats-card-unpaid { background-color: #fee2e2; }
.stats-card-gcash { background-color: #bfdbfe; }
.stats-card-pickup { background-color: #fde68a; }
.stats-card .stats-number { font-size: 1.5rem; font-weight: bold; }
.stats-card .stats-title { color: #555; font-size: 0.9rem; }

.stats-number {
  font-size: 1.5rem;
  font-weight: bold;
}
.stats-title {
  font-size: 0.9rem;
  color: #6c757d;
  text-transform: uppercase;
}

/* Notification badge */
.badge.bg-danger {
  background: #dc3545 !important;
  font-size: 0.7rem;
  padding: 4px 6px;
  border-radius: 10px;
}

</style>
</head>
<body>
<link rel="stylesheet" href="sidebar.css">




<div id="main" class="main-content">
 <div class="admin-header">
  <div class="d-flex align-items-center">
    <span class="burger-menu" onclick="openNav()"><i class="fas fa-bars"></i></span>
    <h2><i class="fas fa-paw paw-logo"></i> Order Management</h2>
  </div>
  <div class="d-flex align-items-center">
    <button id="notifBtn" class="btn btn-light position-relative me-3">
  <i class="fas fa-bell text-primary"></i>
  <?php if ($unread_count > 0): ?>
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
      <?= $unread_count ?>
    </span>
  <?php endif; ?>
  </button>
    <span class="text-light me-3">Welcome, Admin</span>
    <a href="?logout=1" class="btn btn-light btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<div class="container">
  <div class="row mb-4">
    <div class="col-md-4"><div class="stats-card stats-card-pending"><div class="stats-number"><?= $stats['pending'] ?></div><div class="stats-title">Pending</div></div></div>
    <div class="col-md-4"><div class="stats-card stats-card-accepted"><div class="stats-number"><?= $stats['accepted'] ?></div><div class="stats-title">Accepted</div></div></div>
    <div class="col-md-4"><div class="stats-card stats-card-rejected"><div class="stats-number"><?= $stats['rejected'] ?></div><div class="stats-title">Rejected</div></div></div>
  <div class="row mb-4">
  <div class="col-md-4">
    <div class="stats-card stats-card-total">
      <div class="stats-number">₱<?= number_format($stats['total_sales'], 2) ?></div>
      <div class="stats-title">Total Sales</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stats-card stats-card-paid">
      <div class="stats-number">₱<?= number_format($stats['total_paid'], 2) ?></div>
      <div class="stats-title">Total Paid</div>
    </div>
  </div>
   
    <div class="col-md-4">
    <div class="stats-card stats-card-unpaid">
      <div class="stats-number">₱<?= number_format($stats['total_unpaid'], 2) ?></div>
      <div class="stats-title">Total Unpaid (excluding Completed)</div>
    </div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-md-6">
    <div class="stats-card stats-card-gcash">
      <div class="stats-number"><?= $stats['gcash_count'] ?></div>
      <div class="stats-title">GCash Orders</div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="stats-card stats-card-pickup">
      <div class="stats-number"><?= $stats['pickup_count'] ?></div>
      <div class="stats-title">Pickup Orders</div>
    </div>
  </div>
</div>
  
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
   <td>
    <button class="btn btn-sm btn-outline-primary view-receipt-btn" data-id="<?= $order['id'] ?>">
      <i class="fas fa-receipt"></i> View Receipt
    </button>
  <?php
  $status = $order['status'];

  if ($status == 'Pending'): ?>
    <button class="btn btn-sm btn-success accept-btn" data-id="<?= $order['id'] ?>">
      <i class="fas fa-check"></i>
    </button>
    <button class="btn btn-sm btn-danger reject-btn" data-id="<?= $order['id'] ?>">
      <i class="fas fa-times"></i>
    </button>
    

  <?php elseif ($status == 'Accepted'): ?>
    <button class="btn btn-sm btn-warning mark-paid-btn" data-id="<?= $order['id'] ?>">
      <i class="fas fa-coins"></i> Mark as Paid
    </button>

  <?php elseif ($status == 'Paid'): ?>
    <button class="btn btn-sm btn-info to-claim-btn" data-id="<?= $order['id'] ?>">
      <i class="fas fa-box-open"></i> Set To Claim
    </button>

  <?php elseif ($status == 'To Claim'): ?>
    <button class="btn btn-sm btn-success complete-btn" data-id="<?= $order['id'] ?>">
      <i class="fas fa-check-circle"></i> Complete
    </button>

  <?php else: ?>
    <span class="text-muted small">No actions available</span>
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


<!-- ================= Receipt Modal (Put This at the Bottom) ================= -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Order Receipt</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="receiptContent">
        <p class="text-center text-muted mb-0">Loading receipt...</p>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> 
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function() {
  $('#ordersTable').DataTable();

  // Accept order
  $(document).on('click', '.accept-btn', function(){
    let id = $(this).data('id');
    if(confirm('Accept this order?')){
      $.post('update_order_status.php',{id:id,status:'Accepted'},function(){
        location.reload();
      });
    }
  });

  // Reject order
  $(document).on('click', '.reject-btn', function(){
    let id = $(this).data('id');
    if(confirm('Reject this order?')){
      $.post('update_order_status.php',{id:id,status:'Rejected'},function(){
        location.reload();
      });
    }
  });

  // Mark as Paid
  $(document).on('click', '.mark-paid-btn', function(){
    let id = $(this).data('id');
    $.post('update_order_status.php',{id:id,status:'Paid'},function(){
      location.reload();
    });
  });

  // To Claim
  $(document).on('click', '.to-claim-btn', function(){
    let id = $(this).data('id');
    $.post('update_order_status.php',{id:id,status:'To Claim'},function(){
      location.reload();
    });
  });

  // Complete
  $(document).on('click', '.complete-btn', function(){
    let id = $(this).data('id');
    $.post('update_order_status.php',{id:id,status:'Completed'},function(){
      location.reload();
    });
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


$('#notifBtn').click(function(){
  // If modal not loaded yet, load it via AJAX
  if (!$('#notificationModal').length) {
    $.get('notificationmodal.php', function(data){
      $('body').append(data); // Add modal to DOM
      const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
      modal.show();
    });
  } else {
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    modal.show();
  }
});
 
$(document).ready(function() {

  // Initialize DataTable
  $('#ordersTable').DataTable();

  // Accept order
  $(document).on('click', '.accept-btn', function(){
    const id = $(this).data('id');
    if(confirm('Accept this order?')){
      $.post('update_order_status.php', { id, status: 'Accepted' }, function(){
        location.reload();
      });
    }
  });

  // Reject order
  $(document).on('click', '.reject-btn', function(){
    const id = $(this).data('id');
    if(confirm('Reject this order?')){
      $.post('update_order_status.php', { id, status: 'Rejected' }, function(){
        location.reload();
      });
    }
  });

  // Mark as Paid
  $(document).on('click', '.mark-paid-btn', function(){
    const id = $(this).data('id');
    $.post('update_order_status.php', { id, status: 'Paid' }, function(){
      location.reload();
    });
  });

  // To Claim
  $(document).on('click', '.to-claim-btn', function(){
    const id = $(this).data('id');
    $.post('update_order_status.php', { id, status: 'To Claim' }, function(){
      location.reload();
    });
  });

  // Complete
  $(document).on('click', '.complete-btn', function(){
    const id = $(this).data('id');
    $.post('update_order_status.php', { id, status: 'Completed' }, function(){
      location.reload();
    });
  });

  // View receipt
  $(document).on('click', '.view-receipt-btn', function(){
    const id = $(this).data('id');
    $('#receiptContent').html('<p class="text-center text-muted">Loading receipt...</p>');
    $('#receiptContent').load('receipt.php?id=' + id, function(response, status, xhr) {
      if (status === "error") {
        $('#receiptContent').html("<p class='text-danger text-center'>Error loading receipt.</p>");
        console.error("Error loading receipt:", xhr.statusText);
      } else {
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
      }
    });
  });

  // View order items
  $(document).on('click', '.view-items', function(){
    const id = $(this).data('id');
    $.get('get_order_items.php', { id }, function(html){
      $('#itemsModalBody').html(html);
      new bootstrap.Modal(document.getElementById('itemsModal')).show();
    });
  });

  // Notifications
  $('#notifBtn').click(function(){
    if (!$('#notificationModal').length) {
      $.get('notificationmodal.php', function(data){
        $('body').append(data);
        const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
        modal.show();
      });
    } else {
      const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
      modal.show();
    }
  });

});

</script>


</body>
</html>
