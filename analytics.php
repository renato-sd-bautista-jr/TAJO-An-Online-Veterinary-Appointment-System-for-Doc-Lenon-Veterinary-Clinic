<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "taho";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
    exit;
}

// Filters
$period = isset($_GET['period']) ? $_GET['period'] : 'daily'; 
$view = isset($_GET['view']) ? $_GET['view'] : 'table'; // table | bar | pie
$where = "WHERE 1=1";

if ($period === 'daily') {
    if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
        $from = $conn->real_escape_string($_GET['date_from']);
        $to = $conn->real_escape_string($_GET['date_to']);
        $where .= " AND DATE(appointment_date) BETWEEN '$from' AND '$to'";
    }
    $group = "DATE(appointment_date)";
} elseif ($period === 'monthly') {
    if (!empty($_GET['month_from']) && !empty($_GET['month_to'])) {
        $from = $conn->real_escape_string($_GET['month_from']) . "-01";
        $to = date("Y-m-t", strtotime($_GET['month_to'] . "-01")); // last day of that month
        $where .= " AND appointment_date BETWEEN '$from' AND '$to'";
    }
    $group = "DATE_FORMAT(appointment_date, '%Y-%m')";
} else { // yearly
    if (!empty($_GET['year_from']) && !empty($_GET['year_to'])) {
        $from = intval($_GET['year_from']) . "-01-01";
        $to = intval($_GET['year_to']) . "-12-31";
        $where .= " AND appointment_date BETWEEN '$from' AND '$to'";
    }
    $group = "YEAR(appointment_date)";
}

$sql = "
SELECT 
    $group AS period,
    COUNT(*) AS total,
    SUM(status='Pending') AS pending,
    SUM(status='Confirmed') AS confirmed,
    SUM(status='Completed') AS completed,
    SUM(status='Cancelled') AS cancelled
FROM appointments
$where
GROUP BY period
ORDER BY period ASC
";

$res = $conn->query($sql);
$analytics = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doc Lenon Veterinary - Appointment Analytics</title>
  <link rel="icon" href="img/LOGO.png" type="image/png">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Sidebar + Custom Styles -->
  <style>
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
        <a href="admin.php?page=products" >
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
        <a href="analytics.php"class="active">
            <i class="fas fa-chart-bar"></i> Analytics
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

<div id="main" class="main-content">
    <!-- Header -->
    <div class="admin-header">
        <span class="burger-menu" onclick="openNav()">&#9776;</span>
        <h4><i class="fas fa-chart-bar paw-logo"></i> Appointment Analytics</h4>
    </div>

    <!-- Filters -->
<div class="filter-card">
    <form method="GET" class="d-flex flex-wrap gap-2">
        <select name="period" id="periodSelect" class="form-select w-auto" onchange="toggleDateFilters()">
            <option value="daily" <?php echo $period==='daily'?'selected':''; ?>>Daily</option>
            <option value="monthly" <?php echo $period==='monthly'?'selected':''; ?>>Monthly</option>
            <option value="yearly" <?php echo $period==='yearly'?'selected':''; ?>>Yearly</option>
        </select>

        <!-- Daily filter -->
        <input type="date" name="date_from" id="dailyFilterFrom" class="form-control w-auto d-none" 
               value="<?php echo $_GET['date_from'] ?? ''; ?>">
        <input type="date" name="date_to" id="dailyFilterTo" class="form-control w-auto d-none" 
               value="<?php echo $_GET['date_to'] ?? ''; ?>">

<!-- Monthly filter (Dropdown style with Year) -->
<div id="monthlyFilters" class="d-none d-flex gap-2">

  <!-- From Month -->
  <select name="month_from" class="form-select w-auto">
      <option value="">-- From Month --</option>
      <?php
      $currentYear = date("Y"); 
      $startYear = $currentYear - 2; // 2 years before
      $endYear = $currentYear + 2;   // 2 years ahead

      for ($y = $startYear; $y <= $endYear; $y++) {
          for ($m = 1; $m <= 12; $m++) {
              $monthVal = sprintf("%02d", $m);
              $monthName = date("F", mktime(0, 0, 0, $m, 1));
              $value = "$y-$monthVal";
              $selected = (isset($_GET['month_from']) && $_GET['month_from'] == $value) ? "selected" : "";
              echo "<option value='$value' $selected>$monthName $y</option>";
          }
      }
      ?>
  </select>

  <!-- To Month -->
  <select name="month_to" class="form-select w-auto">
      <option value="">-- To Month --</option>
      <?php
      for ($y = $startYear; $y <= $endYear; $y++) {
          for ($m = 1; $m <= 12; $m++) {
              $monthVal = sprintf("%02d", $m);
              $monthName = date("F", mktime(0, 0, 0, $m, 1));
              $value = "$y-$monthVal";
              $selected = (isset($_GET['month_to']) && $_GET['month_to'] == $value) ? "selected" : "";
              echo "<option value='$value' $selected>$monthName $y</option>";
          }
      }
      ?>
  </select>

</div>



        <!-- Yearly filter -->
        <input type="number" min="2000" max="2100" name="year_from" id="yearlyFilterFrom" class="form-control w-auto d-none" 
               placeholder="Year From" value="<?php echo $_GET['year_from'] ?? ''; ?>">
        <input type="number" min="2000" max="2100" name="year_to" id="yearlyFilterTo" class="form-control w-auto d-none" 
               placeholder="Year To" value="<?php echo $_GET['year_to'] ?? ''; ?>">

        <select name="view" class="form-select w-auto">
            <option value="table" <?php echo $view==='table'?'selected':''; ?>>Table</option>
            <option value="bar" <?php echo $view==='bar'?'selected':''; ?>>Bar Chart</option>
            <option value="pie" <?php echo $view==='pie'?'selected':''; ?>>Pie Chart</option>
        </select>

        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
    </form>
</div>

    <!-- Analytics Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-pie"></i> Analytics (<?php echo ucfirst($period); ?> - <?php echo ucfirst($view); ?>)
        </div>
        <div class="card-body">

            <?php if ($view === 'table'): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo ucfirst($period); ?></th>
                                <th>Total</th>
                                <th class="text-warning">Pending</th>
                                <th class="text-primary">Confirmed</th>
                                <th class="text-success">Completed</th>
                                <th class="text-danger">Cancelled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analytics as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['period']); ?></td>
                                <td><?php echo $row['total']; ?></td>
                                <td class="text-warning fw-bold"><?php echo $row['pending']; ?></td>
                                <td class="text-primary fw-bold"><?php echo $row['confirmed']; ?></td>
                                <td class="text-success fw-bold"><?php echo $row['completed']; ?></td>
                                <td class="text-danger fw-bold"><?php echo $row['cancelled']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($analytics)): ?>
                            <tr><td colspan="6" class="text-center">No data available</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($view === 'bar' || $view === 'pie'): ?>
                <canvas id="analyticsChart" height="120"></canvas>
                <script>
                    const labels = <?php echo json_encode(array_column($analytics, 'period')); ?>;
                    const total = <?php echo json_encode(array_column($analytics, 'total')); ?>;
                    const pending = <?php echo json_encode(array_column($analytics, 'pending')); ?>;
                    const confirmed = <?php echo json_encode(array_column($analytics, 'confirmed')); ?>;
                    const completed = <?php echo json_encode(array_column($analytics, 'completed')); ?>;
                    const cancelled = <?php echo json_encode(array_column($analytics, 'cancelled')); ?>;

                    const ctx = document.getElementById('analyticsChart').getContext('2d');
                    new Chart(ctx, {
                        type: '<?php echo $view === "bar" ? "bar" : "pie"; ?>',
                        data: {
                            labels: labels,
                            datasets: [
                                <?php if ($view === "bar"): ?>
                                { label: 'Pending', data: pending, backgroundColor: '#ffc107' },
                                { label: 'Confirmed', data: confirmed, backgroundColor: '#0d6efd' },
                                { label: 'Completed', data: completed, backgroundColor: '#198754' },
                                { label: 'Cancelled', data: cancelled, backgroundColor: '#dc3545' }
                                <?php else: ?>
                                {
                                    label: 'Appointments',
                                    data: total,
                                    backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#6f42c1','#20c997']
                                }
                                <?php endif; ?>
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: 'top' } }
                        }
                    });
                </script>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Sidebar Toggle JS -->
<script>

  function toggleDateFilters() {
    const period = document.getElementById("periodSelect").value;

    // hide all filters
    document.getElementById("dailyFilterFrom").classList.add("d-none");
    document.getElementById("dailyFilterTo").classList.add("d-none");
    document.getElementById("monthlyFilters")?.classList.add("d-none");
    document.getElementById("yearlyFilterFrom").classList.add("d-none");
    document.getElementById("yearlyFilterTo").classList.add("d-none");

    if (period === "daily") {
        document.getElementById("dailyFilterFrom").classList.remove("d-none");
        document.getElementById("dailyFilterTo").classList.remove("d-none");
    } else if (period === "monthly") {
        document.getElementById("monthlyFilters")?.classList.remove("d-none");
    } else if (period === "yearly") {
        document.getElementById("yearlyFilterFrom").classList.remove("d-none");
        document.getElementById("yearlyFilterTo").classList.remove("d-none");
    }
}
document.addEventListener("DOMContentLoaded", toggleDateFilters);

function openNav() {
  document.getElementById("mySidebar").style.width = "250px";
  document.getElementById("main").style.marginLeft = "250px";
}
function closeNav() {
  document.getElementById("mySidebar").style.width = "0";
  document.getElementById("main").style.marginLeft= "0";
}
</script>
</body>
</html>
