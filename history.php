<?php
// Start session for admin authentication
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "taho"; // Change to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to admin login page
    header("Location: admin.php");
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Default filter values
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Prepare SQL query with filters
$sql = "SELECT id, appointment_date, appointment_time, pet_name, pet_type, owner_name, owner_phone, service_type, notes, status, created_at
        FROM appointments WHERE 1=1";

// Add status filter
if ($status_filter !== 'all') {
    $sql .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Add date filter
if ($date_filter !== 'all') {
    switch ($date_filter) {
        case 'past':
            $sql .= " AND appointment_date < CURDATE()";
            break;
        case 'today':
            $sql .= " AND appointment_date = CURDATE()";
            break;
        case 'future':
            $sql .= " AND appointment_date > CURDATE()";
            break;
        case 'this_week':
            $sql .= " AND YEARWEEK(appointment_date, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'this_month':
            $sql .= " AND MONTH(appointment_date) = MONTH(CURDATE()) AND YEAR(appointment_date) = YEAR(CURDATE())";
            break;
    }
}

// Add search filter
if (!empty($search)) {
    $sql .= " AND (pet_name LIKE '%$search%' OR owner_name LIKE '%$search%' OR service_type LIKE '%$search%')";
}

// Order by date
$sql .= " ORDER BY appointment_date DESC, appointment_time DESC";

// Execute query
$result = $conn->query($sql);
$appointments = [];

// Fetch appointments
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// Get appointment status statistics
$stats_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN appointment_date < CURDATE() THEN 1 ELSE 0 END) as past,
                SUM(CASE WHEN appointment_date = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN appointment_date > CURDATE() THEN 1 ELSE 0 END) as upcoming
              FROM appointments";
              
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc Lenon Veterinary - Appointment History</title>
    <link rel="icon" href="img/LOGO.png" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            overflow-x: hidden;
        }
        .admin-header {
            background-color: #4DA6FF;
            color: white;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #4DA6FF;
            color: white;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #4DA6FF;
            border-color: #4DA6FF;
        }
        .btn-primary:hover {
            background-color: #3a8fd0;
            border-color: #3a8fd0;
        }
        .paw-logo {
            color: #4DA6FF;
            margin-right: 10px;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
        }
        .sidebar {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            background-color: #222;
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.2);
        }
        
        .sidebar a {
            padding: 12px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #fff;
            display: block;
            transition: 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover {
            background-color: #333;
            border-left: 3px solid #4DA6FF;
        }
        
        .sidebar a.active {
            background-color: #2c2c2c;
            border-left: 3px solid #4DA6FF;
            font-weight: bold;
        }
        
        .sidebar .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 30px;
            margin-left: 50px;
        }
        
        .main-content {
            transition: margin-left .5s;
            padding: 16px;
        }
        
        .burger-menu {
            font-size: 24px;
            cursor: pointer;
            color: white;
            margin-right: 15px;
        }
        
        .sidebar-header {
            padding: 0 15px 20px 15px;
            color: #4DA6FF;
            font-size: 22px;
            position: absolute;
            top: 15px;
            left: 15px;
        }
        
        .filter-card {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #f0f7ff;
            border: 1px solid #cfe2ff;
        }
        
        .stats-card {
            border-left: 4px solid;
            background-color: white;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .stats-card-pending {
            border-left-color: #ffc107;
        }
        .stats-card-confirmed {
            border-left-color: #0d6efd;
        }
        .stats-card-completed {
            border-left-color: #198754;
        }
        .stats-card-cancelled {
            border-left-color: #dc3545;
        }
        
        .stats-number {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .stats-title {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div id="mySidebar" class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-paw"></i> Doc Lenon
        </div>
        <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">&times;</a>
        <a href="admin.php?page=products">
            <i class="fas fa-box"></i> Products Inventory
        </a>
        <a href="post.php">
            <i class="fas fa-blog"></i> Post Management
        </a>
        <a href="calendar.php">
            <i class="fas fa-calendar-alt"></i> Appointment Calendar
        </a>
        <a href="appointment_history.php" class="active">
            <i class="fas fa-history"></i> Appointment History
        </a>
        <a href="?logout=1">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div id="main" class="main-content">
        <div class="admin-header">
            <div class="d-flex align-items-center">
                <span class="burger-menu" onclick="openNav()">
                    <i class="fas fa-bars"></i>
                </span>
                <h2><i class="fas fa-paw paw-logo"></i> Doc Lenon Veterinary</h2>
            </div>
            <div>
                <span class="text-light me-3">Welcome, Admin</span>
                <a href="?logout=1" class="btn btn-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="container">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card stats-card-pending">
                        <div class="stats-number"><?php echo $stats['pending']; ?></div>
                        <div class="stats-title">Pending</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card stats-card-confirmed">
                        <div class="stats-number"><?php echo $stats['confirmed']; ?></div>
                        <div class="stats-title">Confirmed</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card stats-card-completed">
                        <div class="stats-number"><?php echo $stats['completed']; ?></div>
                        <div class="stats-title">Completed</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card stats-card-cancelled">
                        <div class="stats-number"><?php echo $stats['cancelled']; ?></div>
                        <div class="stats-title">Cancelled</div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Options -->
            <div class="card filter-card">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status Filter</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Confirmed" <?php echo $status_filter === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="date_filter" class="form-label">Date Filter</label>
                        <select class="form-select" id="date_filter" name="date_filter">
                            <option value="all" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Dates</option>
                            <option value="past" <?php echo $date_filter === 'past' ? 'selected' : ''; ?>>Past Appointments</option>
                            <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today's Appointments</option>
                            <option value="future" <?php echo $date_filter === 'future' ? 'selected' : ''; ?>>Future Appointments</option>
                            <option value="this_week" <?php echo $date_filter === 'this_week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="this_month" <?php echo $date_filter === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search pet, owner, service..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Appointment History Table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-history me-2"></i> Appointment History
                        </div>
                        <div>
                            <a href="calendar.php" class="btn btn-sm btn-light">
                                <i class="fas fa-calendar-alt me-1"></i> Go to Calendar
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="appointmentHistoryTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Pet Name</th>
                                    <th>Pet Type</th>
                                    <th>Owner</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['id']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['pet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['pet_type']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['owner_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['service_type']); ?></td>
                                        <td>
                                            <?php
                                                $status_class = '';
                                                switch($appointment['status']) {
                                                    case 'Pending':
                                                        $status_class = 'bg-warning';
                                                        break;
                                                    case 'Confirmed':
                                                        $status_class = 'bg-primary';
                                                        break;
                                                    case 'Completed':
                                                        $status_class = 'bg-success';
                                                        break;
                                                    case 'Cancelled':
                                                        $status_class = 'bg-danger';
                                                        break;
                                                    default:
                                                        $status_class = 'bg-secondary';
                                                }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo $appointment['status']; ?></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($appointment['status'] !== 'Completed' && $appointment['status'] !== 'Cancelled'): ?>
                                                <button class="btn btn-sm btn-primary" onclick="editAppointment(<?php echo $appointment['id']; ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'Completed')" title="Mark as Completed">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="updateStatus(<?php echo $appointment['id']; ?>, 'Cancelled')" title="Cancel">
                                                    <i class="fas fa-times"></i>
                                                </button>
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
    
    <!-- Appointment Details Modal -->
    <div class="modal fade" id="appointmentDetailsModal" tabindex="-1" aria-labelledby="appointmentDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentDetailsModalLabel">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="appointmentDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#appointmentHistoryTable').DataTable({
                "order": [[1, "desc"], [2, "desc"]], // Order by date and time
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
            });
        });
        
        function openNav() {
            document.getElementById("mySidebar").style.width = "250px";
            document.getElementById("main").style.marginLeft = "250px";
        }
        
        function closeNav() {
            document.getElementById("mySidebar").style.width = "0";
            document.getElementById("main").style.marginLeft = "0";
        }
        
        function viewAppointmentDetails(id) {
            // Fetch appointment details via AJAX
            $.ajax({
                url: 'get_appointment_details.php',
                type: 'GET',
                data: { id: id },
                success: function(response) {
                    $('#appointmentDetailsContent').html(response);
                    const detailsModal = new bootstrap.Modal(document.getElementById('appointmentDetailsModal'));
                    detailsModal.show();
                },
                error: function() {
                    alert('Failed to load appointment details.');
                }
            });
        }
        
        function editAppointment(id) {
            // Redirect to calendar.php with edit parameter
            window.location.href = 'calendar.php?edit_appointment=' + id;
        }
        
        function updateStatus(id, status) {
            if (confirm('Are you sure you want to mark this appointment as ' + status + '?')) {
                $.ajax({
                    url: 'update_appointment_status.php',
                    type: 'POST',
                    data: { 
                        id: id,
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Appointment status updated successfully!');
                            location.reload();
                        } else {
                            alert('Failed to update appointment status: ' + response.error);
                        }
                    },
                    error: function() {
                        alert('An error occurred while updating appointment status.');
                    },
                    dataType: 'json'
                });
            }
        }
    </script>
</body>
</html>