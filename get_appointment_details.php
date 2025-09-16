<?php
// Start session for admin authentication
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "Access denied";
    exit;
}

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

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid appointment ID";
    exit;
}

$appointment_id = (int)$_GET['id'];

// Prepare SQL query with parameter binding for security
$sql = "SELECT * FROM appointments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if appointment exists
if ($result->num_rows === 0) {
    echo "Appointment not found";
    exit;
}

// Fetch appointment details
$appointment = $result->fetch_assoc();

// Format date and time for display
$formatted_date = date('F d, Y', strtotime($appointment['appointment_date']));
$formatted_time = date('g:i A', strtotime($appointment['appointment_time']));
$created_at = date('F d, Y g:i A', strtotime($appointment['created_at']));

// Build status badge
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

// Output appointment details in a formatted way
?>

<div class="appointment-details">
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="border-bottom pb-2">Appointment #<?php echo $appointment['id']; ?></h5>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <p><strong>Date:</strong> <?php echo $formatted_date; ?></p>
            <p><strong>Time:</strong> <?php echo $formatted_time; ?></p>
            <p><strong>Status:</strong> <span class="badge <?php echo $status_class; ?>"><?php echo $appointment['status']; ?></span></p>
            <p><strong>Service Type:</strong> <?php echo htmlspecialchars($appointment['service_type']); ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Created:</strong> <?php echo $created_at; ?></p>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="border-bottom pb-2">Pet Information</h6>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <p><strong>Pet Name:</strong> <?php echo htmlspecialchars($appointment['pet_name']); ?></p>
            <p><strong>Pet Type:</strong> <?php echo htmlspecialchars($appointment['pet_type']); ?></p>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="border-bottom pb-2">Owner Information</h6>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($appointment['owner_name']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['owner_phone']); ?></p>
        </div>
    </div>
    
    <?php if (!empty($appointment['notes'])): ?>
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="border-bottom pb-2">Notes</h6>
            <div class="p-2 bg-light rounded">
                <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Close statement and connection
$stmt->close();
$conn->close();
?>