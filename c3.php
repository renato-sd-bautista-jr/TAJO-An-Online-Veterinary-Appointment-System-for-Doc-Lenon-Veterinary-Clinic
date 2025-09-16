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

// Fetch appointments from the database
$appointments = [];
$sql = "SELECT * FROM appointments";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['appointment_date'];
        if (!isset($appointments[$date])) {
            $appointments[$date] = [];
        }
        $appointments[$date][] = [
            'id' => $row['id'],
            'pet_name' => htmlspecialchars($row['pet_name']),
            'service' => htmlspecialchars($row['service_type']),
            'time' => date('g:i A', strtotime($row['appointment_time']))
        ];
    }
}

// Fetch upcoming appointments for the table view
$upcomingAppointments = [];
$sql = "SELECT * FROM appointments WHERE appointment_date >= CURDATE() ORDER BY appointment_date, appointment_time LIMIT 10";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $upcomingAppointments[] = [
            'id' => $row['id'],
            'date' => date('M d, Y', strtotime($row['appointment_date'])),
            'time' => date('g:i A', strtotime($row['appointment_time'])),
            'pet_name' => htmlspecialchars($row['pet_name']),
            'owner' => htmlspecialchars($row['owner_name']),
            'service' => htmlspecialchars($row['service_type']),
            'status' => 'Pending'
        ];
    }
}

// Handle appointment form submission
if (isset($_POST['add_appointment'])) {
    // Retrieve and sanitize form data
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $appointment_time = $conn->real_escape_string($_POST['appointment_time']);
    $service_type = $conn->real_escape_string($_POST['service_type']);
    $pet_name = $conn->real_escape_string($_POST['pet_name']);
    $pet_type = $conn->real_escape_string($_POST['pet_type']);
    $owner_name = $conn->real_escape_string($_POST['owner_name']);
    $owner_phone = $conn->real_escape_string($_POST['owner_phone']);
    $notes = $conn->real_escape_string($_POST['notes']);

    // Check if the time slot is already reserved
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM appointments WHERE appointment_date = ? AND appointment_time = ?");
    $stmt->bind_param("ss", $appointment_date, $appointment_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Time slot is already reserved
        $message = "The selected time slot is already reserved. Please choose another time.";
        echo $message; // Return message for AJAX
        exit;
    } else {
        // Insert appointment into the database
        $stmt = $conn->prepare("INSERT INTO appointments (appointment_date, appointment_time, service_type, pet_name, pet_type, owner_name, owner_phone, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $appointment_date, $appointment_time, $service_type, $pet_name, $pet_type, $owner_name, $owner_phone, $notes);

        if ($stmt->execute()) {
            $message = "Appointment successfully added!";
            echo $message; // Return message for AJAX
        } else {
            $message = "Error: " . $stmt->error;
            echo $message; // Return message for AJAX
        }

        $stmt->close();
        exit;
    }
}

// Handle appointment update
if (isset($_POST['update_appointment'])) {
    // Retrieve and sanitize form data
    $appointment_id = intval($_POST['appointment_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $appointment_time = $conn->real_escape_string($_POST['appointment_time']);
    $service_type = $conn->real_escape_string($_POST['service_type']);
    $pet_name = $conn->real_escape_string($_POST['pet_name']);
    $pet_type = $conn->real_escape_string($_POST['pet_type']);
    $owner_name = $conn->real_escape_string($_POST['owner_name']);
    $owner_phone = $conn->real_escape_string($_POST['owner_phone']);
    $notes = $conn->real_escape_string($_POST['notes']);

    // Check if the time slot is already reserved (excluding this appointment)
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND id != ?");
    $stmt->bind_param("ssi", $appointment_date, $appointment_time, $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Time slot is already reserved
        $message = "The selected time slot is already reserved. Please choose another time.";
        echo $message; // Return message for AJAX
        exit;
    } else {
        // Update appointment in the database
        $stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ?, service_type = ?, pet_name = ?, pet_type = ?, owner_name = ?, owner_phone = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("ssssssssi", $appointment_date, $appointment_time, $service_type, $pet_name, $pet_type, $owner_name, $owner_phone, $notes, $appointment_id);

        if ($stmt->execute()) {
            $message = "Appointment successfully updated!";
            echo $message; // Return message for AJAX
        } else {
            $message = "Error: " . $stmt->error;
            echo $message; // Return message for AJAX
        }

        $stmt->close();
        exit;
    }
}

// Handle appointment cancellation
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = intval($_POST['appointment_id']);
    
    // Delete the appointment
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        echo "Appointment successfully cancelled!";
    } else {
        echo "Error cancelling appointment: " . $stmt->error;
    }
    
    $stmt->close();
    exit;
}

// AJAX endpoint to fetch reserved slots
if (isset($_GET['action']) && $_GET['action'] === 'fetch_reserved_slots') {
    $date = $conn->real_escape_string($_GET['date']);
    $reservedSlots = [];

    $stmt = $conn->prepare("SELECT appointment_time FROM appointments WHERE appointment_date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $reservedSlots[] = date('g:i A', strtotime($row['appointment_time']));
    }

    echo json_encode($reservedSlots);
    exit;
}

// AJAX endpoint to fetch appointments
if (isset($_GET['action']) && $_GET['action'] === 'fetch_appointments') {
    $result = $conn->query("SELECT * FROM appointments");
    $appointments = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $date = $row['appointment_date'];
            if (!isset($appointments[$date])) {
                $appointments[$date] = [];
            }
            $appointments[$date][] = [
                'id' => $row['id'],
                'pet_name' => htmlspecialchars($row['pet_name']),
                'service' => htmlspecialchars($row['service_type']),
                'time' => date('g:i A', strtotime($row['appointment_time']))
            ];
        }
    }

    echo json_encode($appointments);
    exit;
}

// AJAX endpoint to fetch upcoming appointments
if (isset($_GET['action']) && $_GET['action'] === 'fetch_upcoming') {
    $sql = "SELECT * FROM appointments WHERE appointment_date >= CURDATE() ORDER BY appointment_date, appointment_time LIMIT 10";
    $result = $conn->query($sql);
    $upcomingAppointments = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $upcomingAppointments[] = [
                'id' => $row['id'],
                'date' => date('M d, Y', strtotime($row['appointment_date'])),
                'time' => date('g:i A', strtotime($row['appointment_time'])),
                'pet_name' => htmlspecialchars($row['pet_name']),
                'owner' => htmlspecialchars($row['owner_name']),
                'service' => htmlspecialchars($row['service_type']),
                'status' => 'Pending'
            ];
        }
    }

    echo json_encode($upcomingAppointments);
    exit;
}

// AJAX endpoint to get appointment details for editing
if (isset($_GET['action']) && $_GET['action'] === 'get_appointment') {
    $appointment_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Format the time for display
        $row['appointment_time'] = date('g:i A', strtotime($row['appointment_time']));
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Appointment not found']);
    }
    
    exit;
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
            cursor: pointer;
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
            pointer-events: none;
            cursor: not-allowed;
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
        <a href="calendar.php" class="active">
            <i class="fas fa-calendar-alt"></i> Appointment Calendar
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
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($_GET['message']); ?></div>
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
                                        
                                        // Display appointments for this day
                                        if ($hasAppointments) {
                                            foreach ($appointments[$currentDate] as $index => $apt) {
                                                if ($index < 2) { // Limit to showing 2 appointments
                                                    echo '<div class="appointment">' . $apt['pet_name'] . ' - ' . $apt['service'] . '</div>';
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
                                            <tr>
                                                <td><?php echo $appointment['date']; ?></td>
                                                <td><?php echo $appointment['time']; ?></td>
                                                <td><?php echo $appointment['pet_name']; ?></td>
                                                <td><?php echo $appointment['owner']; ?></td>
                                                <td><?php echo $appointment['service']; ?></td>
                                                <td>
                                                    <span class="badge bg-warning"><?php echo $appointment['status']; ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-apt" data-id="<?php echo $appointment['id']; ?>" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger cancel-apt" data-id="<?php echo $appointment['id']; ?>" title="Cancel">
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
    <div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAppointmentModalLabel">Schedule New Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="appointmentForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Appointment Date</label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Available Time Slots</label>
                                    <div class="d-flex flex-wrap gap-2" id="timeSlots">
                                        <div class="time-slot" data-time="9:00 AM" onclick="selectTimeSlot(this)">9:00 AM</div>
                                        <div class="time-slot" data-time="10:00 AM" onclick="selectTimeSlot(this)">10:00 AM</div>
                                        <div class="time-slot" data-time="11:00 AM" onclick="selectTimeSlot(this)">11:00 AM</div>
                                        <div class="time-slot" data-time="1:00 PM" onclick="selectTimeSlot(this)">1:00 PM</div>
                                        <div class="time-slot" data-time="2:00 PM" onclick="selectTimeSlot(this)">2:00 PM</div>
                                        <div class="time-slot" data-time="3:00 PM" onclick="selectTimeSlot(this)">3:00 PM</div>
                                        <div class="time-slot" data-time="4:00 PM" onclick="selectTimeSlot(this)">4:00 PM</div>
                                    </div>
                                    <input type="hidden" id="appointment_time" name="appointment_time" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="service_type" class="form-label">Service Type</label>
                                    <select class="form-select" id="service_type" name="service_type" required>
                                        <option value="" disabled selected>Select a service</option>
                                        <option value="Vaccination">Vaccination</option>
                                        <option value="Check-up">Check-up</option>
                                        <option value="Surgery">Surgery</option>
                                        <option value="Grooming">Grooming</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="pet_name" class="form-label">Pet Name</label>
                                    <input type="text" class="form-control" id="pet_name" name="pet_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pet_type" class="form-label">Pet Type</label>
                                    <input type="text" class="form-control" id="pet_type" name="pet_type" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="owner_name" class="form-label">Owner Name</label>
                                    <input type="text" class="form-control" id="owner_name" name="owner_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="owner_phone" class="form-label">Owner Phone</label>
                                    <input type="text" class="form-control" id="owner_phone" name="owner_phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Sidebar Functions
        function openNav() {
            document.getElementById("mySidebar").style.width = "250px";
            document.getElementById("main").style.marginLeft = "250px";
        }

        function closeNav() {
            document.getElementById("mySidebar").style.width = "0";
            document.getElementById("main").style.marginLeft = "0";
        }

        // Time Slot Selection
        function selectTimeSlot(element) {
            // Remove selected class from all time slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Add selected class to clicked time slot
            element.classList.add('selected');
            
            // Update hidden input with selected time
            document.getElementById('appointment_time').value = element.getAttribute('data-time');
        }

        // Check available time slots when date is selected
        document.getElementById('appointment_date').addEventListener('change', function() {
            const selectedDate = this.value;
            
            // Reset all time slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('disabled');
                slot.classList.remove('selected');
            });
            
            // Clear selected time
            document.getElementById('appointment_time').value = '';
            
            // Fetch reserved slots for this date
            fetch(`?action=fetch_reserved_slots&date=${selectedDate}`)
                .then(response => response.json())
                .then(reservedSlots => {
                    // Disable reserved time slots
                    document.querySelectorAll('.time-slot').forEach(slot => {
                        const timeStr = slot.getAttribute('data-time');
                        if (reservedSlots.includes(timeStr)) {
                            slot.classList.add('disabled');
                        }
                    });
                });
        });

        // Show Appointments for a specific date
        function showAppointments(date) {
            // Fetch appointments for this date
            fetch(`?action=fetch_appointments`)
                .then(response => response.json())
                .then(allAppointments => {
                    const dateAppointments = allAppointments[date] || [];
                    
                    // Create modal HTML
                    const modalHTML = `
                        <div class="modal fade" id="dayAppointmentsModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Appointments for ${formatDate(date)}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        ${renderAppointmentsList(dateAppointments, date)}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Remove existing modal if present
                    const existingModal = document.getElementById('dayAppointmentsModal');
                    if (existingModal) {
                        existingModal.remove();
                    }
                    
                    // Add modal to DOM
                    const modalContainer = document.createElement('div');
                    modalContainer.innerHTML = modalHTML;
                    document.body.appendChild(modalContainer.firstChild);
                    
                    // Initialize and show modal
                    const modal = new bootstrap.Modal(document.getElementById('dayAppointmentsModal'));
                    modal.show();
                });
                .catch(error => console.error('Error fetching appointments:', error));
        }

        // Render appointments list for modal
        function renderAppointmentsList(appointments, date) {
            if (appointments.length === 0) {
                return `
                    <p>No appointments scheduled for this date.</p>
                    <button class="btn btn-primary" onclick="addAppointmentForDate('${date}')">Add Appointment</button>
                `;
            }
            
            let content = '<h5>Appointments for ' + formatDate(date) + '</h5>';
            content += '<ul class="list-group">';
            
            appointments.forEach(apt => {
                content += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${apt.pet_name}</strong> - ${apt.service}
                            <div class="text-muted">${apt.time}</div>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-primary" onclick="editAppointment(${apt.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="cancelAppointment(${apt.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </li>
                `;
            });
            
            content += '</ul>';
            content += `<button class="btn btn-primary mt-3" onclick="addAppointmentForDate('${date}')">Add Appointment</button>`;
            
            return content;
        }

        // Format date for display
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        // Add appointment for a specific date
        function addAppointmentForDate(date) {
            // Set the date in the appointment form
            document.getElementById('appointment_date').value = date;
            
            // Trigger the change event to check available time slots
            const event = new Event('change');
            document.getElementById('appointment_date').dispatchEvent(event);
            
            // Close current modal if open
            const currentModal = document.getElementById('dayAppointmentsModal');
            if (currentModal) {
                const bsModal = bootstrap.Modal.getInstance(currentModal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
            
            // Show the appointment modal
            const appointmentModal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
            appointmentModal.show();
        }

        // Edit Appointment
        function editAppointment(id) {
            // Fetch appointment details
            fetch(`?action=get_appointment&id=${id}`)
                .then(response => response.json())
                .then(appointment => {
                    if (appointment.error) {
                        alert('Error: ' + appointment.error);
                        return;
                    }
                    
                    // Remove existing modal if present
                    const existingModal = document.getElementById('editAppointmentModal');
                    if (existingModal) {
                        existingModal.remove();
                    }
                    
                    // Create modal HTML
                    const modalHTML = `
                        <div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editAppointmentModalLabel">Edit Appointment</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="editAppointmentForm">
                                            <input type="hidden" name="appointment_id" value="${appointment.id}">
                                            <input type="hidden" name="update_appointment" value="1">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="edit_appointment_date" class="form-label">Appointment Date</label>
                                                        <input type="date" class="form-control" id="edit_appointment_date" name="appointment_date" value="${appointment.appointment_date}" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Time Slot</label>
                                                        <div class="d-flex flex-wrap gap-2" id="editTimeSlots">
                                                            <div class="time-slot ${appointment.appointment_time === '9:00 AM' ? 'selected' : ''}" data-time="9:00 AM" onclick="selectEditTimeSlot(this)">9:00 AM</div>
                                                            <div class="time-slot ${appointment.appointment_time === '10:00 AM' ? 'selected' : ''}" data-time="10:00 AM" onclick="selectEditTimeSlot(this)">10:00 AM</div>
                                                            <div class="time-slot ${appointment.appointment_time === '11:00 AM' ? 'selected' : ''}" data-time="11:00 AM" onclick="selectEditTimeSlot(this)">11:00 AM</div>
                                                            <div class="time-slot ${appointment.appointment_time === '1:00 PM' ? 'selected' : ''}" data-time="1:00 PM" onclick="selectEditTimeSlot(this)">1:00 PM</div>
                                                            <div class="time-slot ${appointment.appointment_time === '2:00 PM' ? 'selected' : ''}" data-time="2:00 PM" onclick="selectEditTimeSlot(this)">2:00 PM</div>
                                                            <div class="time-slot ${appointment.appointment_time === '3:00 PM' ? 'selected' : ''}" data-time="3:00 PM" onclick="selectEditTimeSlot(this)">3:00 PM</div>
                                                            <div class="time-slot ${appointment.appointment_time === '4:00 PM' ? 'selected' : ''}" data-time="4:00 PM" onclick="selectEditTimeSlot(this)">4:00 PM</div>
                                                        </div>
                                                        <input type="hidden" id="edit_appointment_time" name="appointment_time" value="${appointment.appointment_time}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="edit_service_type" class="form-label">Service Type</label>
                                                        <select class="form-select" id="edit_service_type" name="service_type" required>
                                                            <option value="Vaccination" ${appointment.service_type === 'Vaccination' ? 'selected' : ''}>Vaccination</option>
                                                            <option value="Check-up" ${appointment.service_type === 'Check-up' ? 'selected' : ''}>Check-up</option>
                                                            <option value="Surgery" ${appointment.service_type === 'Surgery' ? 'selected' : ''}>Surgery</option>
                                                            <option value="Grooming" ${appointment.service_type === 'Grooming' ? 'selected' : ''}>Grooming</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_pet_name" class="form-label">Pet Name</label>
                                                        <input type="text" class="form-control" id="edit_pet_name" name="pet_name" value="${appointment.pet_name}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_pet_type" class="form-label">Pet Type</label>
                                                        <input type="text" class="form-control" id="edit_pet_type" name="pet_type" value="${appointment.pet_type}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="edit_owner_name" class="form-label">Owner Name</label>
                                                        <input type="text" class="form-control" id="edit_owner_name" name="owner_name" value="${appointment.owner_name}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_owner_phone" class="form-label">Owner Phone</label>
                                                        <input type="text" class="form-control" id="edit_owner_phone" name="owner_phone" value="${appointment.owner_phone}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="edit_notes" class="form-label">Notes</label>
                                                        <textarea class="form-control" id="edit_notes" name="notes" rows="4">${appointment.notes || ''}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary">Update Appointment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Add modal to DOM
                    const modalContainer = document.createElement('div');
                    modalContainer.innerHTML = modalHTML;
                    document.body.appendChild(modalContainer.firstChild);
                    
                    // Initialize and show modal
                    const modal = new bootstrap.Modal(document.getElementById('editAppointmentModal'));
                    modal.show();
                    
                    // Check available time slots for the selected date
                    checkEditTimeSlots();
                    
                    // Attach event listener for form submission
                    document.getElementById('editAppointmentForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(this);
                        
                        fetch('', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(result => {
                            alert(result);
                            modal.hide();
                            
                            // Refresh calendar and appointments
                            refreshCalendar();
                            refreshUpcomingAppointments();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while updating the appointment.');
                        });
                    });
                });
        }

        // Time slot selection for edit form
        function selectEditTimeSlot(element) {
            // Remove selected class from all time slots
            document.querySelectorAll('#editTimeSlots .time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Add selected class to clicked time slot
            element.classList.add('selected');
            
            // Update hidden input with selected time
            document.getElementById('edit_appointment_time').value = element.getAttribute('data-time');
        }

        // Check available time slots for edit form
        function checkEditTimeSlots() {
            const selectedDate = document.getElementById('edit_appointment_date').value;
            const appointmentId = document.querySelector('[name="appointment_id"]').value;
            
            // Reset all time slots
            document.querySelectorAll('#editTimeSlots .time-slot').forEach(slot => {
                slot.classList.remove('disabled');
            });
            
            // Fetch reserved slots for this date
            fetch(`?action=fetch_reserved_slots&date=${selectedDate}`)
                .then(response => response.json())
                .then(reservedSlots => {
                    // Get current selected time
                    const currentTime = document.getElementById('edit_appointment_time').value;
                    
                    // Disable reserved time slots (except current time)
                    document.querySelectorAll('#editTimeSlots .time-slot').forEach(slot => {
                        const timeStr = slot.getAttribute('data-time');
                        if (reservedSlots.includes(timeStr) && timeStr !== currentTime) {
                            slot.classList.add('disabled');
                        }
                    });
                });
        }

        // Cancel an appointment
        function cancelAppointment(id) {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                const formData = new FormData();
                formData.append('cancel_appointment', '1');
                formData.append('appointment_id', id);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    alert(result);
                    
                    // Close any open modals
                    const dayModal = document.getElementById('dayAppointmentsModal');
                    if (dayModal) {
                        const bsModal = bootstrap.Modal.getInstance(dayModal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                    
                    // Refresh calendar and appointments
                    refreshCalendar();
                    refreshUpcomingAppointments();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while cancelling the appointment.');
                });
            }
        }

        // Refresh calendar data
        function refreshCalendar() {
            fetch('?action=fetch_appointments')
                .then(response => response.json())
                .then(appointments => {
                    // Clear all appointment indicators
                    document.querySelectorAll('.calendar-date').forEach(cell => {
                        cell.classList.remove('has-appointments');
                        
                        // Remove all appointment divs but keep the date number
                        const dateNumber = cell.querySelector('.date-number');
                        cell.innerHTML = '';
                        cell.appendChild(dateNumber);
                    });
                    
                    // Add the new appointments
                    for (const date in appointments) {
                        const dateCell = document.querySelector(`.calendar-date[data-date="${date}"]`);
                        if (dateCell) {
                            dateCell.classList.add('has-appointments');
                            
                            // Add appointment indicators
                            appointments[date].forEach((apt, index) => {
                                if (index < 2) {
                                    const appointmentDiv = document.createElement('div');
                                    appointmentDiv.className = 'appointment';
                                    appointmentDiv.textContent = apt.pet_name + ' - ' + apt.service;
                                    dateCell.appendChild(appointmentDiv);
                                } else if (index === 2) {
                                    const appointmentDiv = document.createElement('div');
                                    appointmentDiv.className = 'appointment';
                                    appointmentDiv.textContent = '+' + (appointments[date].length - 2) + ' more';
                                    dateCell.appendChild(appointmentDiv);
                                }
                            });
                        }
                    }
                });
        }

        // Refresh upcoming appointments table
        function refreshUpcomingAppointments() {
            fetch('?action=fetch_upcoming')
                .then(response => response.json())
                .then(appointments => {
                    const tbody = document.querySelector('table tbody');
                    tbody.innerHTML = '';
                    
                    appointments.forEach(apt => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${apt.date}</td>
                            <td>${apt.time}</td>
                            <td>${apt.pet_name}</td>
                            <td>${apt.owner}</td>
                            <td>${apt.service}</td>
                            <td><span class="badge bg-warning">${apt.status}</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editAppointment(${apt.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="cancelAppointment(${apt.id})" title="Cancel">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                });
        }

        // Form submission for adding new appointment
        document.addEventListener('DOMContentLoaded', function() {
            const appointmentForm = document.getElementById('appointmentForm');
            if (appointmentForm) {
                appointmentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    formData.append('add_appointment', '1');
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(result => {
                        alert(result);
                        
                        // Reset form
                        this.reset();
                        document.querySelectorAll('.time-slot').forEach(slot => {
                            slot.classList.remove('selected');
                        });
                        
                        // Hide modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal'));
                        modal.hide();
                        
                        // Refresh calendar and appointments
                        refreshCalendar();
                        refreshUpcomingAppointments();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while adding the appointment.');
                    });
                });
            }
            
            // Initialize Calendar
            refreshCalendar();
            
            // Load date change event listener
            const dateInput = document.getElementById('edit_appointment_date');
            if (dateInput) {
                dateInput.addEventListener('change', checkEditTimeSlots);
            }
        });
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('calendar-date')) {
                const date = e.target.getAttribute('data-date');
                showAppointments(date);
            }
        });
    </script>
    <!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>