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
    $sql = "INSERT INTO appointments (appointment_date, appointment_time, pet_name, pet_type, owner_name, owner_phone, service_type, notes)
            VALUES ('$appointment_date', '$appointment_time', '$pet_name', '$pet_type', '$owner_name', '$owner_phone', '$service_type', '$notes')";

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

// Also update the calendar appointments query to include ID
$appointments = [];
$sql = "SELECT id, appointment_date, appointment_time, pet_name, service_type FROM appointments";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['appointment_date'];
        $appointments[$date][] = [
            'id' => $row['id'],
            'time' => date('g:i A', strtotime($row['appointment_time'])),
            'pet_name' => $row['pet_name'],
            'service' => $row['service_type']
        ];
    }
}
// Add these functions at the beginning of calendar.php, after database connection

// Handle appointment editing
if (isset($_POST['update_appointment'])) {
    $appointment_id = $conn->real_escape_string($_POST['appointment_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $appointment_time = $conn->real_escape_string($_POST['appointment_time']);
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
        <a href="post.php" class="active">
            <i class="fas fa-blog"></i> Post Management
        </a>
        <a href="calendar.php">
            <i class="fas fa-calendar-alt"></i> Appointment Calendar
        </a>
        <a href="history1.php" class="active">
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
                                                    <button class="btn btn-sm btn-primary" title="Edit" onclick="editAppointment(<?php echo $appointment['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" title="Cancel" onclick="deleteAppointment(<?php echo $appointment['id']; ?>)">
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
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Available Time Slots</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="time-slot" onclick="selectTimeSlot(this)">9:00 AM</div>
                                        <div class="time-slot" onclick="selectTimeSlot(this)">10:00 AM</div>
                                        <div class="time-slot" onclick="selectTimeSlot(this)">11:00 AM</div>
                                        <div class="time-slot" onclick="selectTimeSlot(this)">1:00 PM</div>
                                        <div class="time-slot" onclick="selectTimeSlot(this)">2:00 PM</div>
                                        <div class="time-slot" onclick="selectTimeSlot(this)">3:00 PM</div>
                                        <div class="time-slot" onclick="selectTimeSlot(this)">4:00 PM</div>
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
                                        <option value="Bird">Bird</option>
                                        <option value="Rabbit">Rabbit</option>
                                        <option value="Other">Other</option>
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
        // Add these functions to the existing script section

// Function to open modal in "Add" mode
        function addAppointment(date = null) {
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
                    
                    // Select the correct time slot
                    const timeValue = data.formatted_time;
                    document.querySelectorAll('.time-slot').forEach(slot => {
                        if (slot.innerText === timeValue) {
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
    </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>