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

// Get the current year and month (default to current date)
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// Validate year and month to prevent invalid dates
if ($year < 2000 || $year > 2050) {
    $year = date('Y');
}
if ($month < 1 || $month > 12) {
    $month = date('n');
}

// Get first day of the month
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$numberDays = date('t', $firstDayOfMonth);
$dateComponents = getdate($firstDayOfMonth);
$monthName = $dateComponents['month'];
$dayOfWeek = $dateComponents['wday'];

// Get the previous and next month links
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Placeholder for getting appointments from the database
// In the future, you would replace this with actual database queries
function getBookedTimeSlots($conn, $date) {
    $bookedSlots = [];
    $sql = "SELECT appointment_time FROM appointments WHERE appointment_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Format time to match our time slot format (e.g. "9:00 AM")
        $bookedSlots[] = date('g:i A', strtotime($row['appointment_time']));
    }
    
    return $bookedSlots;
}

// Add this to handle AJAX requests for booked slots
if (isset($_GET['check_slots'])) {
    $date = $conn->real_escape_string($_GET['check_slots']);
    $bookedSlots = getBookedTimeSlots($conn, $date);
    
    header('Content-Type: application/json');
    echo json_encode(['bookedSlots' => $bookedSlots]);
    exit;
}

// Handle appointment form submission (placeholder)
if (isset($_POST['add_appointment'])) {
    // Sanitize and retrieve form data
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $appointment_time = $conn->real_escape_string($_POST['appointment_time']);
    $pet_name = $conn->real_escape_string($_POST['pet_name']);
    $pet_type = $conn->real_escape_string($_POST['pet_type']);
    $owner_name = $conn->real_escape_string($_POST['owner_name']);
    $owner_phone = $conn->real_escape_string($_POST['owner_phone']);
    $service_type = $conn->real_escape_string($_POST['service_type']);
    $notes = $conn->real_escape_string($_POST['notes']);

    // Insert data into the database
    $status = "Pending";

    // Insert data into the database
    $sql = "INSERT INTO appointments (appointment_date, appointment_time, pet_name, pet_type, owner_name, owner_phone, service_type, notes, status)
            VALUES ('$appointment_date', '$appointment_time', '$pet_name', '$pet_type', '$owner_name', '$owner_phone', '$service_type', '$notes', '$status')";

    if ($conn->query($sql) === TRUE) {
        $message = "Appointment successfully scheduled!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
$appointments = [];
$sql = "SELECT appointment_date, appointment_time, pet_name, service_type FROM appointments";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['appointment_date'];
        $appointments[$date][] = [
            'time' => date('g:i A', strtotime($row['appointment_time'])),
            'pet_name' => $row['pet_name'],
            'service' => $row['service_type']
        ];
    }
}

// Replace the existing upcoming appointments query with this
$upcomingAppointments = [];
$sql = "SELECT id, appointment_date, appointment_time, pet_name, owner_name, service_type, status 
        FROM appointments 
        WHERE appointment_date >= CURDATE() 
        ORDER BY appointment_date, appointment_time";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $upcomingAppointments[] = [
            'id' => $row['id'],
            'date' => date('M d, Y', strtotime($row['appointment_date'])),
            'time' => date('g:i A', strtotime($row['appointment_time'])),
            'pet_name' => $row['pet_name'],
            'owner' => $row['owner_name'],
            'service' => $row['service_type'],
            'status' => $row['status']
        ];
    }
}

$appointments = [];
$sql = "SELECT id, appointment_date, appointment_time, pet_name, service_type, status 
        FROM appointments";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['appointment_date'];
        $appointments[$date][] = [
            'id' => $row['id'],
            'time' => date('g:i A', strtotime($row['appointment_time'])),
            'pet_name' => $row['pet_name'],
            'service' => $row['service_type'],
            'status' => $row['status'] ?? 'Pending' // fallback if NULL
        ];
    }
}

// Add these functions at the beginning of calendar.php, after database connection
// Handle appointment editing
if (isset($_POST['update_appointment'])) {
    $appointment_id = $conn->real_escape_string($_POST['appointment_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    
    // Convert to 24-hour format for DB
    $appointment_time_12h = $_POST['appointment_time']; // e.g. "3:00 PM"
    $appointment_time = date("H:i:s", strtotime($appointment_time_12h)); // -> "15:00:00"

    $pet_name = $conn->real_escape_string($_POST['pet_name']);
    $pet_type = $conn->real_escape_string($_POST['pet_type']);
    $owner_name = $conn->real_escape_string($_POST['owner_name']);
    $owner_phone = $conn->real_escape_string($_POST['owner_phone']);
    $service_type = $conn->real_escape_string($_POST['service_type']);
    $notes = $conn->real_escape_string($_POST['notes']);

    // Update data in the database
    $sql = "UPDATE appointments 
            SET appointment_date = '$appointment_date', 
                appointment_time = '$appointment_time', 
                pet_name = '$pet_name', 
                pet_type = '$pet_type', 
                owner_name = '$owner_name', 
                owner_phone = '$owner_phone', 
                service_type = '$service_type', 
                notes = '$notes'
            WHERE id = $appointment_id";

    if ($conn->query($sql) === TRUE) {
        $message = "Appointment successfully updated!";
    } else {
        $message = "Error updating appointment: " . $conn->error;
    }
}


// Handle appointment deletion
if (isset($_GET['delete_appointment'])) {
    $appointment_id = $conn->real_escape_string($_GET['delete_appointment']);
    
    // Delete from database
    $sql = "DELETE FROM appointments WHERE id = $appointment_id";
    
    if ($conn->query($sql) === TRUE) {
        $message = "Appointment successfully deleted!";
    } else {
        $message = "Error deleting appointment: " . $conn->error;
    }
}

// Add this endpoint to get appointment data for editing
if (isset($_GET['get_appointment'])) {
    $appointment_id = $conn->real_escape_string($_GET['get_appointment']);
    
    $sql = "SELECT * FROM appointments WHERE id = $appointment_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        
        // Format time for display
        $appointment['formatted_time'] = date('g:i A', strtotime($appointment['appointment_time']));
        
        // Return as JSON
        header('Content-Type: application/json');
        echo json_encode($appointment);
        exit;
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Appointment not found']);
        exit;
    }
}
// Auto-cancel past appointments
function autoCancelPastAppointments($conn) {
    // Get current date in YYYY-MM-DD format
    $currentDate = date('Y-m-d');
    
    // Get current time in HH:MM:SS format
    $currentTime = date('H:i:s');
    
    // Update appointments with past dates to "Cancelled" if they're not already completed
    $sql = "UPDATE appointments 
            SET status = 'Cancelled' 
            WHERE (appointment_date < ? OR (appointment_date = ? AND appointment_time < ?)) 
            AND status != 'Completed'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $currentDate, $currentDate, $currentTime);
    $stmt->execute();
    
    return $stmt->affected_rows; // Return number of affected rows
}

// Call this function after database connection and before any HTML output
$cancelledCount = autoCancelPastAppointments($conn);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-cancel past appointments
$cancelledCount = autoCancelPastAppointments($conn);
if ($cancelledCount > 0) {
    $message = "$cancelledCount past appointment(s) automatically marked as cancelled.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc Lenon Veterinary - Appointment Calendar</title>
    <link rel="icon" href="img/LOGO.png" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        
        /* Sidebar & Burger Menu Styles */
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
        
        /* Calendar styles */
        .calendar {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            text-align: center;
        }
        
        .day-name {
            font-weight: bold;
            padding: 10px;
            background-color: #f3f3f3;
            border-radius: 4px;
        }
        
        .calendar-dates {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        
        .calendar-date {
            height: 80px;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 5px;
            position: relative;
            overflow: hidden;
        }
        
        .date-number {
            position: absolute;
            top: 5px;
            right: 5px;
            font-weight: bold;
        }
        
        .appointment {
            background-color: #4DA6FF;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            margin-top: 5px;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        
        .status-confirmed {
            background-color: #28a745; /* green */
            color: #fff;
        }
        .status-completed {
            background-color: #007bff; /* blue */
            color: #fff;
        }
        .status-cancelled {
            background-color: #dc3545; /* red */
            color: #fff;
        }
        .status-pending {
            background-color: #ffc107; /* yellow */
            color: #000;
        }
        
        .calendar-date.today {
            background-color: #f8f9fa;
            border: 2px solid #4DA6FF;
        }
        
        .calendar-date.has-appointments {
            background-color: rgba(77, 166, 255, 0.1);
        }
        
        .text-muted {
            opacity: 0.6;
        }
        
        /* Modal styles */
        .modal-header {
            background-color: #4DA6FF;
            color: white;
        }
        
        .time-slot {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 8px;
            cursor: pointer;
        }
        
        .time-slot:hover {
            background-color: #f8f9fa;
        }
        
        .time-slot.selected {
            background-color: #4DA6FF;
            color: white;
            border-color: #4DA6FF;
        }
        .time-slot.disabled {
        background-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
        pointer-events: none;
        }

        .time-slot.booked {
            background-color: #f8d7da;
            color: #842029;
            cursor: not-allowed;
            pointer-events: none;
        }
        .badge.bg-danger {
            background-color: #dc3545 !important;
        }

        .table tr.cancelled-appointment {
            background-color: #fff3f3;
        }
        .badge.bg-primary {
            background-color: #0d6efd !important; /* Blue color for completed status */
        }

        .table tr.completed-appointment {
            background-color: #f0f8ff; /* Light blue background for completed rows */
        }

    </style>
</head>
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
        <a href="calendar.php"class="active">
            <i class="fas fa-calendar-alt"></i> Appointment Calendar
        </a>
        <a href="history1.php">
            <i class="fas fa-history"></i> Appointment History
        </a>
        <a href="ordermanagement.php">
            <i class="fas fa-box"></i> Order Management
        </a>
        <a href="analytics.php">
            <i class="fas fa-chart-bar"></i> Analytics
        </a>
        <a href="logout.php">
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
            <?php if (isset($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Appointments Calendar Page -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-calendar-alt me-2"></i> Appointment Calendar
                        </div>
                        <div class="card-body">
                            <div class="calendar">
                                <div class="calendar-header">
                                    <a href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                    <h4 class="mb-0"><?php echo $monthName . ' ' . $year; ?></h4>
                                    <a href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                                
                                <div class="calendar-days">
                                    <div class="day-name">Sun</div>
                                    <div class="day-name">Mon</div>
                                    <div class="day-name">Tue</div>
                                    <div class="day-name">Wed</div>
                                    <div class="day-name">Thu</div>
                                    <div class="day-name">Fri</div>
                                    <div class="day-name">Sat</div>
                                </div>
                                
                                <div class="calendar-dates">
                                    <?php
                                    // Output calendar days
                                    
                                    // Add empty cells for days of the week before the first day of the month
                                    for ($i = 0; $i < $dayOfWeek; $i++) {
                                        $lastMonthDay = date('j', strtotime('last day of previous month', $firstDayOfMonth));
                                        $dayNum = $lastMonthDay - $dayOfWeek + $i + 1;
                                        echo '<div class="calendar-date text-muted">';
                                        echo '<div class="date-number">' . $dayNum . '</div>';
                                        echo '</div>';
                                    }
                                    
                                    // Add cells for days of the current month
                                    for ($day = 1; $day <= $numberDays; $day++) {
                                        $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $isToday = ($year == date('Y') && $month == date('n') && $day == date('j'));
                                        $hasAppointments = isset($appointments[$currentDate]) && count($appointments[$currentDate]) > 0;
                                        
                                        $class = 'calendar-date';
                                        if ($isToday) $class .= ' today';
                                        if ($hasAppointments) $class .= ' has-appointments';
                                        
                                        echo '<div class="' . $class . '" data-date="' . $currentDate . '" onclick="showAppointments(\'' . $currentDate . '\')">';
                                        echo '<div class="date-number">' . $day . '</div>';
                                        
                                        if ($hasAppointments) {
    foreach ($appointments[$currentDate] as $index => $apt) {
        if ($index < 2) {
            // assign a class based on status
            $statusClass = '';
            switch ($apt['status']) {
                case 'Confirmed':
                    $statusClass = 'status-confirmed';
                    break;
                case 'Completed':
                    $statusClass = 'status-completed';
                    break;
                case 'Cancelled':
                    $statusClass = 'status-cancelled';
                    break;
                default:
                    $statusClass = 'status-pending';
            }

            // add tooltip so long names are still accessible
            echo '<div class="appointment ' . $statusClass . '" 
                     title="' . htmlspecialchars($apt['pet_name'] . ' - ' . $apt['service']) . '">'
                   . htmlspecialchars($apt['pet_name']) . ' - ' . htmlspecialchars($apt['service']) .
                 '</div>';
        } elseif ($index == 2) {
            $remaining = count($appointments[$currentDate]) - 2;
            echo '<div class="appointment">+' . $remaining . ' more</div>';
            break;
        }
    }
}

                                        
                                        echo '</div>';
                                    }
                                    
                                    // Add empty cells for days of the week after the last day of the month
                                    $totalCells = $dayOfWeek + $numberDays;
                                    $remainingCells = 7 - ($totalCells % 7);
                                    if ($remainingCells < 7) {
                                        for ($i = 1; $i <= $remainingCells; $i++) {
                                            echo '<div class="calendar-date text-muted">';
                                            echo '<div class="date-number">' . $i . '</div>';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
                                        <i class="fas fa-plus"></i> Add Appointment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upcoming Appointments Card -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-clipboard-list me-2"></i> Upcoming Appointments
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <!-- Replace the existing upcoming appointments table with this -->
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Pet Name</th>
                                            <th>Owner</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upcomingAppointments as $appointment): ?>
                                            <tr data-appointment-id="<?php echo $appointment['id']; ?>" 
                                                class="<?php 
                                                    if ($appointment['status'] == 'Cancelled') echo 'cancelled-appointment';
                                                    else if ($appointment['status'] == 'Completed') echo 'completed-appointment';
                                                ?>">
                                                <td><?php echo $appointment['date']; ?></td>
                                                <td><?php echo $appointment['time']; ?></td>
                                                <td><?php echo $appointment['pet_name']; ?></td>
                                                <td><?php echo $appointment['owner']; ?></td>
                                                <td><?php echo $appointment['service']; ?></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        if ($appointment['status'] == 'Confirmed') echo 'bg-success';
                                                        else if ($appointment['status'] == 'Cancelled') echo 'bg-danger';
                                                        else if ($appointment['status'] == 'Completed') echo 'bg-primary';
                                                        else echo 'bg-warning';
                                                    ?>">
                                                        <?php echo $appointment['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-success" title="Save" onclick="saveAppointment(<?php echo $appointment['id']; ?>)" 
                                                        <?php echo ($appointment['status'] == 'Confirmed' || $appointment['status'] == 'Cancelled' || $appointment['status'] == 'Completed') ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary" title="Edit" onclick="editAppointment(<?php echo $appointment['id']; ?>)"
                                                        <?php echo ($appointment['status'] == 'Cancelled' || $appointment['status'] == 'Completed') ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" title="Cancel" onclick="deleteAppointment(<?php echo $appointment['id']; ?>)"
                                                        <?php echo ($appointment['status'] == 'Completed') ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-times"></i>
                                                    </button>
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
        </div>
    </div>
    
    <!-- Add Appointment Modal -->
    
    <div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Schedule New Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="appointmentForm">
                        <!-- Add hidden input for appointment ID (used for editing) -->
                        <input type="hidden" id="appointment_id" name="appointment_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Appointment Date</label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" requiredmin="<?php echo date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Available Time Slots</label>
                                    <div class="d-flex flex-wrap gap-2" id="timeSlotContainer">
                                            <div class="time-slot" onclick="selectTimeSlot(this)" data-time="9:00 AM">8:00AM - 9:00 AM</div>
                                            <div class="time-slot" onclick="selectTimeSlot(this)" data-time="10:00 AM">9:00AM - 10:00 AM</div>
                                            <div class="time-slot" onclick="selectTimeSlot(this)" data-time="11:00 AM">10:00AM - 11:00 AM</div>
                                            <div class="time-slot" onclick="selectTimeSlot(this)" data-time="1:00 PM">11:00AM - 12:00 PM</div>
                                            <div class="time-slot" onclick="selectTimeSlot(this)" data-time="2:00 PM">1:00PM - 2:00 PM</div>
                                            <div class="time-slot" onclick="selectTimeSlot(this)" data-time="3:00 PM">2:00PM - 3:00 PM</div>
                                            <div class="time-slot" onclick="selectTimeSlot(this)" data-time="4:00 PM">3:00AM - 4:00 PM</div>
                                    </div>
                                    <input type="hidden" id="appointment_time" name="appointment_time" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="service_type" class="form-label">Service Type</label>
                                    <select class="form-select" id="service_type" name="service_type" required>
                                        <option value="">Select a service</option>
                                        <option value="Checkup">Regular Checkup</option>
                                        <option value="Vaccination">Vaccination</option>
                                        <option value="Surgery">Surgery</option>
                                        <option value="Dental">Dental Cleaning</option>
                                        <option value="Grooming">Grooming</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pet_name" class="form-label">Pet Name</label>
                                    <input type="text" class="form-control" id="pet_name" name="pet_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pet_type" class="form-label">Pet Type</label>
                                    <select class="form-select" id="pet_type" name="pet_type" required>
                                        <option value="">Select pet type</option>
                                        <option value="Dog">Dog</option>
                                        <option value="Cat">Cat</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="owner_name" class="form-label">Owner Name</label>
                                    <input type="text" class="form-control" id="owner_name" name="owner_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="owner_phone" class="form-label">Owner Phone</label>
                                    <input type="tel" class="form-control" id="owner_phone" name="owner_phone" required>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="appointmentForm" id="submitAppointmentBtn" name="add_appointment" class="btn btn-primary">Schedule Appointment</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Day Appointments Modal -->
    <div class="modal fade" id="dayAppointmentsModal" tabindex="-1" aria-labelledby="dayAppointmentsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dayAppointmentsModalLabel">Appointments for <span id="modalDate"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="appointmentsList">
                        <!-- Appointments will be populated here via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppointmentModal" onclick="$('#dayAppointmentsModal').modal('hide')">
                        Add Appointment
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('appointment_date');
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
            
            // Add event listener for date changes
            dateInput.addEventListener('change', function() {
                checkBookedTimeSlots(this.value);
            });
        });
        document.getElementById('appointment_date').addEventListener('change', function () {
            const date = this.value;
            if (date) {
                checkBookedSlots(date);
            }
        });
        // Function to check booked time slots for a selected date
        function checkBookedTimeSlots(date) {
    // COMPLETELY reset all time slots first - both classes and styles
            document.querySelectorAll('.time-slot').forEach(slot => {
                // Remove classes
                slot.classList.remove('disabled');
                slot.classList.remove('selected');
                slot.classList.remove('booked');
                
                // Reset all inline styles
                slot.style.backgroundColor = '';
                slot.style.color = '';
                slot.style.cursor = 'pointer';
                
                // Re-enable click functionality
                slot.setAttribute('onclick', 'selectTimeSlot(this)');
            });
            
            // Clear the time input
            document.getElementById('appointment_time').value = '';
            
            // Check if selected date is in the past
            const selectedDate = new Date(date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                // If date is in the past, disable all time slots
                document.querySelectorAll('.time-slot').forEach(slot => {
                    disableTimeSlot(slot);
                });
                return;
            }
            
            // If date is today, disable past hours
            if (selectedDate.toDateString() === today.toDateString()) {
                const currentHour = new Date().getHours();
                document.querySelectorAll('.time-slot').forEach(slot => {
                    const slotTime = slot.getAttribute('data-time');
                    const slotHour = convertTo24Hour(slotTime);
                    
                    if (slotHour <= currentHour) {
                        disableTimeSlot(slot);
                    }
                });
            }
            
            // Fetch booked slots from server
            fetch('calendar.php?check_slots=' + date)
                .then(response => response.json())
                .then(data => {
                    // Disable booked time slots
                    const bookedSlots = data.bookedSlots;
                    
                    document.querySelectorAll('.time-slot').forEach(slot => {
                        const slotTime = slot.textContent.trim();
                        if (bookedSlots.includes(slotTime)) {
                            disableTimeSlot(slot);
                        }
                    });
                })
                .catch(error => console.error('Error fetching booked slots:', error));
        }
        
        // Function to disable a time slot
        function disableTimeSlot(slot) {
            slot.classList.add('disabled');
            slot.removeAttribute('onclick');
            slot.style.backgroundColor = '#e9ecef';
            slot.style.color = '#6c757d';
            slot.style.cursor = 'not-allowed';
        }

        function checkBookedSlots(date) {
        fetch('calendar.php?check_slots=' + date)
            .then(response => response.json())
            .then(data => {
                const booked = data.bookedSlots;

                document.querySelectorAll('.time-slot').forEach(slot => {
                    const time = slot.getAttribute('data-time');

                    if (booked.includes(time)) {
                        slot.classList.add('disabled'); // add disabled style
                        slot.onclick = null; // disable click
                    } else {
                        slot.classList.remove('disabled');
                        slot.setAttribute('onclick', 'selectTimeSlot(this)');
                    }
                });
            });
        }
        
        // Convert time from 12-hour format to 24-hour format
        function convertTo24Hour(time12h) {
            const [time, modifier] = time12h.split(' ');
            let [hours, minutes] = time.split(':');

            hours = parseInt(hours, 10);
            minutes = parseInt(minutes || '0', 10);

            if (modifier === 'PM' && hours !== 12) {
                hours += 12;
            }
            if (modifier === 'AM' && hours === 12) {
                hours = 0;
            }

            return hours;
        }
        
        // Modified selectTimeSlot function
        function selectTimeSlot(element) {
            // Don't select if disabled
            if (element.classList.contains('disabled')) {
                return;
            }
            
            // Remove "selected" class from all time slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });

            // Add "selected" class to the clicked one
            element.classList.add('selected');

            // Set the hidden input value
            document.getElementById('appointment_time').value = element.textContent.trim();
        }

        // Function to open modal in "Add" mode
        // Updated addAppointment function
        // Function to open modal in "Add" mode
        function addAppointment(date = null) {
            // Close any open modals first
            closeAllModals();
            
            // Reset the form
            document.getElementById('appointmentForm').reset();
            document.getElementById('appointment_id').value = '';
            
            // Clear selected time slot
            document.querySelectorAll('.time-slot.selected').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Set date if provided
            if (date) {
                document.getElementById('appointment_date').value = date;
                // Check booked slots for this date
                checkBookedTimeSlots(date);
            }
            
            // Set modal title and button text for adding
            document.getElementById('appointmentModalLabel').innerText = 'Schedule New Appointment';
            document.getElementById('submitAppointmentBtn').innerText = 'Schedule Appointment';
            document.getElementById('submitAppointmentBtn').name = 'add_appointment';
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
            modal.show();
        }

        // Function to edit an appointment
        function editAppointment(id) {
            // Close any open modals first
            closeAllModals();
            
            // Fetch appointment data from the server
            fetch('calendar.php?get_appointment=' + id)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Populate the form with existing data
                    document.getElementById('appointment_id').value = data.id;
                    document.getElementById('appointment_date').value = data.appointment_date;
                    
                    // First check booked slots for this date (excluding the current appointment)
                    checkBookedTimeSlots(data.appointment_date);
                    
                    // Then select the correct time slot
                    const timeValue = data.formatted_time;
                    document.querySelectorAll('.time-slot').forEach(slot => {
                        if (slot.innerText === timeValue) {
                            // Remove disabled class from this slot since it's the current appointment's slot
                            slot.classList.remove('disabled');
                            slot.style.backgroundColor = '';
                            slot.style.color = '';
                            slot.style.cursor = 'pointer';
                            slot.setAttribute('onclick', 'selectTimeSlot(this)');
                            
                            // Select it
                            selectTimeSlot(slot);
                        }
                    });
                    
                    document.getElementById('pet_name').value = data.pet_name;
                    document.getElementById('pet_type').value = data.pet_type;
                    document.getElementById('owner_name').value = data.owner_name;
                    document.getElementById('owner_phone').value = data.owner_phone;
                    document.getElementById('service_type').value = data.service_type;
                    document.getElementById('notes').value = data.notes;
                    
                    // Change the modal title and submit button text
                    document.getElementById('appointmentModalLabel').innerText = 'Edit Appointment';
                    document.getElementById('submitAppointmentBtn').innerText = 'Update Appointment';
                    document.getElementById('submitAppointmentBtn').name = 'update_appointment';
                    
                    // Show the modal
                    const modal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error fetching appointment data:', error);
                    alert('Failed to load appointment data. Please try again.');
                });
        }

        // Helper function to close all open modals
        function closeAllModals() {
            // Close day appointments modal if open
            const dayAppointmentsModal = bootstrap.Modal.getInstance(document.getElementById('dayAppointmentsModal'));
            if (dayAppointmentsModal) {
                dayAppointmentsModal.hide();
            }
            
            // Close add/edit appointment modal if open
            const appointmentModal = bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal'));
            if (appointmentModal) {
                appointmentModal.hide();
            }
        }

        // Function to delete an appointment
        function deleteAppointment(id) {
            if (confirm('Are you sure you want to delete this appointment?')) {
                window.location.href = 'calendar.php?delete_appointment=' + id;
            }
        }

        // Updated function to show appointments for a specific day
        function showAppointments(date) {
            // Format date for display
            const dateObj = new Date(date);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const formattedDate = dateObj.toLocaleDateString('en-US', options);
            
            // Set modal title date
            document.getElementById('modalDate').innerText = formattedDate;
            
            // Get appointments for this date
            const appointments = <?php echo json_encode($appointments); ?>[date] || [];
            
            // Populate appointments list
            const appointmentsList = document.getElementById('appointmentsList');
            appointmentsList.innerHTML = '';
            
            if (appointments.length === 0) {
                appointmentsList.innerHTML = '<p class="text-center my-4">No appointments scheduled for this day.</p>';
            } else {
                const list = document.createElement('ul');
                list.className = 'list-group';
                
                appointments.forEach(apt => {
                    // Assuming each appointment has an id property
                    const appointmentId = apt.id || '0'; // Fallback if no ID
                    
                    const item = document.createElement('li');
                        item.className = 'list-group-item';
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${apt.time}</strong> - ${apt.pet_name} 
                                    <span class="badge bg-primary">${apt.service}</span>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-success" title="Save" onclick="saveAppointment(${appointmentId})">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editAppointment(${appointmentId})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteAppointment(${appointmentId})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    list.appendChild(item);
                });
                
                appointmentsList.appendChild(list);
            }
            
            // Add button to create new appointment on this date
            const addButton = document.createElement('div');
            addButton.className = 'text-center mt-3';
            addButton.innerHTML = `
                <button class="btn btn-primary" onclick="addAppointment('${date}')">
                    <i class="fas fa-plus"></i> Add Appointment on This Day
                </button>
            `;
            appointmentsList.appendChild(addButton);
            
            // Open the modal
            const modal = new bootstrap.Modal(document.getElementById('dayAppointmentsModal'));
            modal.show();
        }
        function openNav() {
            document.getElementById("mySidebar").style.width = "250px";
            document.getElementById("main").style.marginLeft = "250px";
        }
        
        function closeNav() {
            document.getElementById("mySidebar").style.width = "0";
            document.getElementById("main").style.marginLeft = "0";
        }
        function saveAppointment(id) {
            fetch('save_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + encodeURIComponent(id)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Find the corresponding status badge and update it
                    const row = document.querySelector(`tr[data-appointment-id="${id}"]`);
                    if (row) {
                        const statusBadge = row.querySelector('.badge');
                        if (statusBadge) {
                            statusBadge.textContent = 'Confirmed';
                            statusBadge.classList.remove('bg-warning');
                            statusBadge.classList.add('bg-success');
                        }
                        // Disable the save button since it's already confirmed
                        const saveBtn = row.querySelector('.btn-success');
                        if (saveBtn) {
                            saveBtn.disabled = true;
                        }
                    }
                    // Show a success message without page reload
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        Appointment successfully confirmed!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.querySelector('.container').prepend(alertDiv);
                    
                    // Remove the alert after 3 seconds
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 3000);
                } else {
                    alert('Failed to confirm appointment: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while confirming the appointment.');
            });
        }
           // Function to select a time slot         
        function selectTimeSlot(slot) {
            // Deselect all time slots
            document.querySelectorAll('.time-slot').forEach(s => {
                s.classList.remove('selected');
            });
                
                // Select the clicked time slot
            slot.classList.add('selected');
                
                // Set the selected time in the hidden input field
            document.getElementById('appointment_time').value = slot.innerText;
        }
        function selectTimeSlot(element) {
            // Remove "selected" class from all time slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });

            // Add "selected" class to the clicked one
            element.classList.add('selected');

            // Set the hidden input value
            document.getElementById('appointment_time').value = element.textContent.trim();
        }

        
    </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>