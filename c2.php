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
            'pet_name' => $row['pet_name'],
            'service' => $row['service_type'],
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
            'date' => date('M d, Y', strtotime($row['appointment_date'])),
            'time' => date('g:i A', strtotime($row['appointment_time'])),
            'pet_name' => $row['pet_name'],
            'owner' => $row['owner_name'],
            'service' => $row['service_type'],
            'status' => 'Pending'
        ];
    }
}

// Handle appointment form submission
if (isset($_POST['add_appointment'])) {
    // Retrieve form data
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $service_type = $_POST['service_type'];
    $pet_name = $_POST['pet_name'];
    $pet_type = $_POST['pet_type'];
    $owner_name = $_POST['owner_name'];
    $owner_phone = $_POST['owner_phone'];
    $notes = $_POST['notes'];

    // Check if the time slot is already reserved
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM appointments WHERE appointment_date = ? AND appointment_time = ?");
    $stmt->bind_param("ss", $appointment_date, $appointment_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Time slot is already reserved
        $message = "The selected time slot is already reserved. Please choose another time.";
    } else {
        // Insert appointment into the database
        $stmt = $conn->prepare("INSERT INTO appointments (appointment_date, appointment_time, service_type, pet_name, pet_type, owner_name, owner_phone, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $appointment_date, $appointment_time, $service_type, $pet_name, $pet_type, $owner_name, $owner_phone, $notes);

        if ($stmt->execute()) {
            $message = "Appointment successfully added!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    // Refresh appointments data after adding a new one
    header("Location: calendar.php?year=$year&month=$month&message=" . urlencode($message));
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'fetch_reserved_slots') {
    $date = $_GET['date'];
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
                'pet_name' => $row['pet_name'],
                'service' => $row['service_type'],
                'time' => date('g:i A', strtotime($row['appointment_time']))
            ];
        }
    }

    echo json_encode($appointments);
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
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($_GET['message']); ?></div>
    <?php endif; ?>
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
                                                    <button class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" title="Cancel">
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
                    <form method="post" id="appointmentForm">
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
                    <button type="submit" form="appointmentForm" name="add_appointment" class="btn btn-primary">Schedule Appointment</button>
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
                    <button type="button" class="btn btn-primary" onclick="openAddAppointmentModal()">
                        Add Appointment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap and JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables to store calendar data
        const year = <?php echo $year; ?>;
        const month = <?php echo $month; ?>;
        const dayOfWeek = <?php echo $dayOfWeek; ?>;
        const numberDays = <?php echo $numberDays; ?>;
        let dayAppointmentsModal;
        let addAppointmentModal;
        
        // Initialize modals
        document.addEventListener('DOMContentLoaded', function() {
            dayAppointmentsModal = new bootstrap.Modal(document.getElementById('dayAppointmentsModal'));
            addAppointmentModal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
            
            // Store the appointments data as a JavaScript object
            const appointments = <?php echo json_encode($appointments); ?>;
            
            // Add click handlers to calendar dates
            document.querySelectorAll('.calendar-date').forEach(date => {
                const dateStr = date.getAttribute('data-date');
                if (dateStr) {
                    date.addEventListener('click', function() {
                        showAppointments(dateStr);
                    });
                }
            });
        });

        // Time slot selection
        function selectTimeSlot(element) {
    // Remove previous selection
            document.querySelectorAll('.time-slot.selected').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Select this time slot
            element.classList.add('selected');
            
            // Set the hidden input value (convert to standard time format for database)
            const timeText = element.innerText;
            document.getElementById('appointment_time').value = timeText;
            console.log("Selected time: " + timeText); // Debug log
        }
        
        // Open add appointment modal and close day appointments modal
        function openAddAppointmentModal() {
            dayAppointmentsModal.hide();
            addAppointmentModal.show();
        }
        
        // Show appointments for a specific day
        // Show appointments for a specific day
        function showAppointments(date) {
    // Format date for display
    const dateObj = new Date(date);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = dateObj.toLocaleDateString('en-US', options);

    // Set modal title date
    document.getElementById('modalDate').innerText = formattedDate;

    // Set appointment date in the add appointment form
    document.getElementById('appointment_date').value = date;

    // Get appointments for this date via AJAX
    fetch(`calendar.php?action=fetch_appointments`)
        .then(response => response.json())
        .then(data => {
            // Get appointments for this specific date
            const appointments = data[date] || [];

            // Populate appointments list
            const appointmentsList = document.getElementById('appointmentsList');

            // Clear previous appointments
            appointmentsList.innerHTML = '';

            if (appointments.length === 0) {
                appointmentsList.innerHTML = '<p class="text-center">No appointments for this day.</p>';
            } else {
                // Create a list of appointments
                const ul = document.createElement('ul');
                ul.className = 'list-group';

                appointments.forEach(apt => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${apt.pet_name}</strong> - ${apt.service}
                                <div class="text-muted">${apt.time}</div>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Cancel">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    ul.appendChild(li);
                });

                appointmentsList.appendChild(ul);
            }

            // Show the modal
            dayAppointmentsModal.show();
        })
        .catch(error => {
            console.error('Error fetching appointments:', error);
            alert('Failed to load appointments. Please try again.');
        });
}
function fetchAppointmentsAndUpdateCalendar() {
    fetch('calendar.php?action=fetch_appointments')
        .then(response => response.json())
        .then(data => {
            // Update the global appointments variable
            const appointments = data;

            // Re-render the calendar
            const calendarDates = document.querySelector('.calendar-dates');
            calendarDates.innerHTML = ''; // Clear existing dates

            // Add empty cells for days before the first day of the month
            for (let i = 0; i < dayOfWeek; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'calendar-date text-muted';
                calendarDates.appendChild(emptyCell);
            }

            // Add cells for each day of the current month
            for (let day = 1; day <= numberDays; day++) {
                const currentDate = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isToday = (year === new Date().getFullYear() && month === new Date().getMonth() + 1 && day === new Date().getDate());
                const hasAppointments = appointments[currentDate] && appointments[currentDate].length > 0;

                const dateCell = document.createElement('div');
                dateCell.className = 'calendar-date';
                if (isToday) dateCell.classList.add('today');
                if (hasAppointments) dateCell.classList.add('has-appointments');
                dateCell.dataset.date = currentDate;

                const dateNumber = document.createElement('div');
                dateNumber.className = 'date-number';
                dateNumber.textContent = day;
                dateCell.appendChild(dateNumber);

                // Add appointments to the cell
                if (hasAppointments) {
                    appointments[currentDate].forEach((apt, index) => {
                        if (index < 2) {
                            const appointmentDiv = document.createElement('div');
                            appointmentDiv.className = 'appointment';
                            appointmentDiv.textContent = `${apt.pet_name} - ${apt.service}`;
                            dateCell.appendChild(appointmentDiv);
                        } else if (index === 2) {
                            const moreDiv = document.createElement('div');
                            moreDiv.className = 'appointment';
                            moreDiv.textContent = `+${appointments[currentDate].length - 2} more`;
                            dateCell.appendChild(moreDiv);
                        }
                    });
                }

                calendarDates.appendChild(dateCell);
            }

            // Add empty cells for days after the last day of the month
            const totalCells = dayOfWeek + numberDays;
            const remainingCells = 7 - (totalCells % 7);
            if (remainingCells < 7) {
                for (let i = 0; i < remainingCells; i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'calendar-date text-muted';
                    calendarDates.appendChild(emptyCell);
                }
            }
        })
        .catch(error => console.error('Error fetching appointments:', error));
}
document.getElementById('appointmentForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this);

    fetch('calendar.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(result => {
            // Show success message
            alert('Appointment successfully added!');

            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal'));
            modal.hide();

            // Reset the form
            this.reset();
            document.querySelectorAll('.time-slot.selected').forEach(slot => slot.classList.remove('selected'));

            // Fetch updated appointments and update the calendar
            fetchAppointmentsAndUpdateCalendar();
        })
        .catch(error => console.error('Error adding appointment:', error));
});
document.addEventListener('DOMContentLoaded', () => {
    fetchAppointmentsAndUpdateCalendar();
});
// Add these JavaScript functions at the end of your existing script section

// Sidebar toggle functions
function openNav() {
    document.getElementById("mySidebar").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
}

function closeNav() {
    document.getElementById("mySidebar").style.width = "0";
    document.getElementById("main").style.marginLeft = "0";
}

// Refresh function to update appointments periodically
function refreshAppointments() {
    fetchAppointmentsAndUpdateCalendar();
    
    // Update the upcoming appointments table as well
    fetch('calendar.php?action=fetch_upcoming')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('.table tbody');
            tableBody.innerHTML = '';
            
            data.forEach(apt => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${apt.date}</td>
                    <td>${apt.time}</td>
                    <td>${apt.pet_name}</td>
                    <td>${apt.owner}</td>
                    <td>${apt.service}</td>
                    <td><span class="badge bg-warning">${apt.status}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-apt" data-id="${apt.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger cancel-apt" data-id="${apt.id}" title="Cancel">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Add event listeners to new buttons
            addActionButtonListeners();
        })
        .catch(error => console.error('Error fetching upcoming appointments:', error));
}

// Add event listeners to action buttons
function addActionButtonListeners() {
    // Edit appointment buttons
    document.querySelectorAll('.edit-apt').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.dataset.id;
            editAppointment(appointmentId);
        });
    });
    
    // Cancel appointment buttons
    document.querySelectorAll('.cancel-apt').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.dataset.id;
            if (confirm('Are you sure you want to cancel this appointment?')) {
                cancelAppointment(appointmentId);
            }
        });
    });
}

// Edit appointment function
function editAppointment(id) {
    // Fetch appointment details
    fetch(`calendar.php?action=get_appointment&id=${id}`)
        .then(response => response.json())
        .then(data => {
            // Populate form with appointment data
            document.getElementById('appointment_date').value = data.appointment_date;
            
            // Select the correct time slot
            document.querySelectorAll('.time-slot').forEach(slot => {
                if (slot.innerText === data.appointment_time) {
                    selectTimeSlot(slot);
                }
            });
            
            document.getElementById('service_type').value = data.service_type;
            document.getElementById('pet_name').value = data.pet_name;
            document.getElementById('pet_type').value = data.pet_type;
            document.getElementById('owner_name').value = data.owner_name;
            document.getElementById('owner_phone').value = data.owner_phone;
            document.getElementById('notes').value = data.notes;
            
            // Add the appointment ID to the form for update
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'appointment_id';
            idInput.value = id;
            document.getElementById('appointmentForm').appendChild(idInput);
            
            // Change the form submission to update
            const submitButton = document.querySelector('[form="appointmentForm"]');
            submitButton.name = 'update_appointment';
            submitButton.textContent = 'Update Appointment';
            
            // Show the modal
            addAppointmentModal.show();
        })
        .catch(error => console.error('Error fetching appointment details:', error));
}

// Cancel appointment function
function cancelAppointment(id) {
    fetch('calendar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cancel_appointment=1&appointment_id=${id}`
    })
        .then(response => response.text())
        .then(result => {
            alert('Appointment successfully cancelled!');
            refreshAppointments();
        })
        .catch(error => console.error('Error cancelling appointment:', error));
}

// Initialize the page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modals
    dayAppointmentsModal = new bootstrap.Modal(document.getElementById('dayAppointmentsModal'));
    addAppointmentModal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
    
    // Initial fetch of appointments
    fetchAppointmentsAndUpdateCalendar();
    
    // Add event listeners to calendar dates after they're created
    setTimeout(() => {
        document.querySelectorAll('.calendar-date').forEach(date => {
            const dateStr = date.getAttribute('data-date');
            if (dateStr) {
                date.addEventListener('click', function() {
                    showAppointments(dateStr);
                });
            }
        });
    }, 500);
    
    // Add action button listeners for upcoming appointments
    addActionButtonListeners();
    
    // Set up auto-refresh every 5 minutes
    setInterval(refreshAppointments, 300000);
    
    // Reset form when modal is closed
    document.getElementById('addAppointmentModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('appointmentForm').reset();
        document.querySelectorAll('.time-slot.selected').forEach(slot => {
            slot.classList.remove('selected');
        });
        
        // Reset form to add mode
        const submitButton = document.querySelector('[form="appointmentForm"]');
        submitButton.name = 'add_appointment';
        submitButton.textContent = 'Schedule Appointment';
        
        // Remove appointment ID if present
        const idInput = document.querySelector('input[name="appointment_id"]');
        if (idInput) idInput.remove();
    });
});

// Handle form submission through AJAX to avoid page reload
document.getElementById('appointmentForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    // Check if time slot is selected
    if (!document.getElementById('appointment_time').value) {
        alert('Please select a time slot');
        return false;
    }
    
    const formData = new FormData(this);
    
    // Add the add_appointment parameter
    formData.append('add_appointment', '1');
    
    fetch('calendar.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(result => {
            // Show success message
            alert('Appointment successfully added!');
            
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('addAppointmentModal')).hide();
            
            // Reset the form
            this.reset();
            document.querySelectorAll('.time-slot.selected').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Refresh the calendar
            fetchAppointmentsAndUpdateCalendar();
        })
        .catch(error => {
            console.error('Error adding appointment:', error);
            alert('Failed to add appointment. Please try again.');
        });
});
document.getElementById('appointment_date').addEventListener('change', function () {
    const selectedDate = this.value;

    // Fetch reserved time slots for the selected date
    fetch(`calendar.php?action=fetch_reserved_slots&date=${selectedDate}`)
        .then(response => response.json())
        .then(reservedSlots => {
            const timeSlots = document.querySelectorAll('.time-slot');
            let availableSlots = 0;

            timeSlots.forEach(slot => {
                const time = slot.innerText;
                if (reservedSlots.includes(time)) {
                    slot.classList.add('disabled');
                    slot.classList.remove('selected');
                } else {
                    slot.classList.remove('disabled');
                    availableSlots++;
                }
            });

            // If no slots are available, show a message and disable the form
            if (availableSlots === 0) {
                alert('No available time slots for the selected date.');
                document.querySelector('[form="appointmentForm"]').disabled = true;
            } else {
                document.querySelector('[form="appointmentForm"]').disabled = false;
            }
        })
        .catch(error => console.error('Error fetching reserved slots:', error));
});
// Update the selectTimeSlot function to prevent selecting disabled slots
function selectTimeSlot(element) {
    if (element.classList.contains('disabled')) {
        return; // Do nothing if the slot is disabled
    }

    // Remove previous selection
    document.querySelectorAll('.time-slot.selected').forEach(slot => {
        slot.classList.remove('selected');
    });

    // Select this time slot
    element.classList.add('selected');

    // Set the hidden input value
    const timeText = element.innerText;
    document.getElementById('appointment_time').value = timeText;
}
</script>
</body>
</html>