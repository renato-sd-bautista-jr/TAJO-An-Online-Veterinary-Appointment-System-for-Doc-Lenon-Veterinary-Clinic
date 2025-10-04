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


if (isset($_POST['add_appointment'])) {
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $pet_name = $_POST['pet_name'];
    $pet_type = $_POST['pet_type'];
    $owner_name = $_POST['owner_name'];
    $owner_phone = $_POST['owner_phone'];
    $service_type = $_POST['service_type'];
    $notes = $_POST['notes'];

    // Basic validation
    if (!empty($appointment_date) && !empty($appointment_time) && !empty($owner_name)) {
        $query = "INSERT INTO appointments 
            (appointment_date, appointment_time, pet_name, pet_type, owner_name, owner_phone, service_type, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssss", 
            $appointment_date, 
            $appointment_time, 
            $pet_name, 
            $pet_type, 
            $owner_name, 
            $owner_phone, 
            $service_type, 
            $notes
        );

        if ($stmt->execute()) {
            echo "<script>
                alert('Appointment successfully scheduled!');
                window.location.href = 'appointments.php';
            </script>";
        } else {
            echo "<script>alert('Error: Could not save appointment.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - TAHO</title>
    <link rel="icon" href="img/LOGO.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            padding-top: 80px;
        }

        .btn-book {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 500;
            transition: 0.3s ease;
        }

        .btn-book:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .time-slot {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            user-select: none;
        }

        .time-slot.selected {
            background-color: #3498db;
            color: white;
            border-color: #2980b9;
        }

        .time-slot.disabled {
            background-color: #e9ecef;
            color: #adb5bd;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-light bg-light fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <img src="img/LOGO.png" alt="Logo" width="40" height="35" class="me-2"> DOC LENON VETERINARY
            </a>
        </div>
    </nav>

    <div class="container text-center">
        <h1 class="fw-bold mb-4">Book an Appointment</h1>
        <p class="text-muted mb-5">Schedule your pet’s appointment conveniently online.</p>
        <button class="btn btn-book" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
            Schedule Now
        </button>
    </div>

    <!-- Add Appointment Modal -->
    <div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="appointmentModalLabel">Schedule New Appointment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <form method="post" id="appointmentForm">
                        <input type="hidden" id="appointment_id" name="appointment_id">

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Appointment Date -->
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Appointment Date</label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                                </div>

                                <!-- Time Slots -->
                                <div class="mb-3">
                                    <label class="form-label">Available Time Slots</label>
                                    <div class="d-flex flex-wrap gap-2" id="timeSlotContainer">
                                        <div class="time-slot" data-time="9:00 AM">9:00 AM</div>
                                        <div class="time-slot" data-time="10:00 AM">10:00 AM</div>
                                        <div class="time-slot" data-time="11:00 AM">11:00 AM</div>
                                        <div class="time-slot" data-time="1:00 PM">1:00 PM</div>
                                        <div class="time-slot" data-time="2:00 PM">2:00 PM</div>
                                        <div class="time-slot" data-time="3:00 PM">3:00 PM</div>
                                        <div class="time-slot" data-time="4:00 PM">4:00 PM</div>
                                    </div>
                                    <input type="hidden" id="appointment_time" name="appointment_time" required>
                                </div>

                                <!-- Service Type -->
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
                                <!-- Pet Info -->
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

                                <!-- Owner Info -->
                                <div class="mb-3">
                                    <label for="owner_name" class="form-label">Owner Name</label>
                                    <input type="text" class="form-control" id="owner_name" name="owner_name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="owner_phone" class="form-label">Owner Phone</label>
                                    <input type="tel" class="form-control" id="owner_phone" name="owner_phone" required pattern="[0-9]{10,15}" placeholder="09123456789">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="300" placeholder="Additional information..."></textarea>
                                    <div class="text-end text-muted small mt-1" id="charCount">0 / 300</div>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Disable past dates
        const dateInput = document.getElementById('appointment_date');
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);

        // Time slot selection
        const slots = document.querySelectorAll('.time-slot');
        const timeInput = document.getElementById('appointment_time');

        slots.forEach(slot => {
            slot.addEventListener('click', function() {
                if (slot.classList.contains('disabled')) return;

                slots.forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                timeInput.value = this.dataset.time;
            });
        });

        // Character counter for notes
        const notes = document.getElementById('notes');
        const charCount = document.getElementById('charCount');
        notes.addEventListener('input', () => {
            const len = notes.value.length;
            charCount.textContent = `${len} / 300`;
            charCount.style.color = len > 250 ? '#dc3545' : '#6c757d';
        });

        function selectTimeSlot(element) {
    document.querySelectorAll('.time-slot').forEach(slot => slot.classList.remove('selected'));
    element.classList.add('selected');
    document.getElementById('appointment_time').value = element.dataset.time;
}
    </script>
</body>
</html>
