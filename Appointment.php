<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAHO Appointment</title>
    <link rel="icon" href="img/LOGO.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
            color: #2c3e50;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-button:hover {
            color: #3498db;
        }
        
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .form-title {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: 600;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-request {
            background-color: #3498db;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            border: none;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-request:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .character-count {
            text-align: right;
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-button">×</a>
    
    <div class="container">
        <div class="form-container">
            <h1 class="form-title">Request a Service</h1>
            
            <form action="process_form.php" method="POST" id="appointmentForm">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Full Name" required>
                    <label for="fullName">Full Name </label>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
                            <label for="email">Email Address </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Cell Phone" required pattern="[0-9]{10,15}">
                            <label for="phone">Cell Phone (numbers only)</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="petName" name="petName" placeholder="Pet's Name" required>
                    <label for="petName">Pet's Name </label>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select" id="petType" name="petType" required>
                                <option value="" selected disabled>Dog or Cat</option>
                                <option value="dog">Dog</option>
                                <option value="cat">Cat</option>
                            </select>
                            <label for="petType">Pet Type</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="breed" name="breed" placeholder="Breed">
                            <label for="breed">Breed</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="preferredDate" name="preferredDate" placeholder="Preferred Date" required>
                            <label for="preferredDate">Preferred Date </label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <select class="form-select" id="reason" name="reason" required>
                        <option value="" selected disabled></option>
                        <option value="wellness">Pet Wellness</option>
                        <option value="consultation">Consultation</option>
                        <option value="vaccination">Vaccination</option>
                        <option value="deworming">Deworming</option>
                        <option value="laboratory">Laboratory</option>
                        <option value="surgery">Surgery</option>
                        <option value="grooming">Grooming</option>
                    </select>
                    <label for="reason">Reason(s) for Appointment</label>
                </div>
                
                <div class="form-floating mb-3">
                    <textarea class="form-control" id="notes" name="notes" style="height: 100px" maxlength="300" placeholder="Notes"></textarea>
                    <label for="notes">Notes</label>
                    <div class="character-count">0 / 300</div>
                </div>
                
                <button type="submit" class="btn btn-request">REQUEST</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter for notes
        const notesTextarea = document.getElementById('notes');
        const charCount = document.querySelector('.character-count');
        
        // Initialize character count
        charCount.textContent = `0 / 300`;
        
        notesTextarea.addEventListener('input', function() {
            const remaining = this.value.length;
            charCount.textContent = `${remaining} / 300`;
            
            // Optional: Change color when approaching limit
            if (remaining > 250) {
                charCount.style.color = '#dc3545';
            } else {
                charCount.style.color = '#6c757d';
            }
        });
        
        // Form validation
        document.getElementById('appointmentForm').addEventListener('submit', function(event) {
            const phone = document.getElementById('phone').value;
            const phonePattern = /^[0-9]{10,15}$/;
            
            if (!phonePattern.test(phone)) {
                alert('Please enter a valid phone number (numbers only, 10-15 digits)');
                event.preventDefault();
            }
        });
    </script>
</body>
</html>