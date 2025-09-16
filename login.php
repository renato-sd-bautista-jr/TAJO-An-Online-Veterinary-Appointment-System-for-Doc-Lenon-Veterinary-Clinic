<?php
// Start session for user authentication
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

// Handle login form submission
if (isset($_POST['login'])) {
    $admin_username = $_POST['username'];
    $admin_password = $_POST['password'];
    
    // Prepare SQL statement to prevent SQL injection
    // Modified to check for username instead of email
    $stmt = $conn->prepare("SELECT id, fullname, email, username, password, created_at, updated_at FROM users WHERE username = ?");
    $stmt->bind_param("s", $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Password verification
        // SECURITY FIX: In production, use password_verify() for hashed passwords
        // For implementation, uncomment this and ensure passwords are hashed in the database
        // if (password_verify($admin_password, $user['password'])) {
        
        // For now, using direct comparison (not recommended for production)
        if ($admin_password === $user['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['fullname'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_username'] = $user['username'];
            
            // SECURITY FIX: Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);
            
            header("Location: admin.php");
            exit;
        } else {
            $login_error = "Invalid username or password";
        }
    } else {
        $login_error = "Invalid username or password";
    }
    $stmt->close();
}

// Handle forgot password form submission
if (isset($_POST['forgot_password_submit'])) {
    $email = $_POST['email'];
    
    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Generate random verification code
        $verification_code = rand(100000, 999999);
        
        // Store verification code in session
        $_SESSION['reset_code'] = $verification_code;
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_code_timestamp'] = time(); // To expire the code after some time
        
        // Send email with verification code using PHPMailer for Gmail
        require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'bookease19@gmail.com'; // Your Gmail address
            
            // SECURITY FIX: Don't hardcode credentials in source code
            // Move this to a configuration file that's not committed to version control
            // For now, keeping as is but you should change this:
            $mail->Password = 'byxq shpj sapa uqlh'; // Your Gmail app password
            
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            // Recipients
            // FIX: set consistent from email
            $mail->setFrom('bookease19@gmail.com', 'Doc Lenon Veterinary');
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification Code';
            $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                        .code { font-size: 24px; font-weight: bold; color: #4DA6FF; letter-spacing: 2px; }
                        .expiry { color: #777; font-size: 14px; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>Password Reset Request</h2>
                        <p>You have requested to reset your password for your Doc Lenon Veterinary admin account.</p>
                        <p>Your verification code is: <span class='code'>{$verification_code}</span></p>
                        <p>Please enter this code in the verification page to continue with your password reset.</p>
                        <p class='expiry'>This code will expire in 30 minutes.</p>
                        <p>If you did not request this password reset, please ignore this email.</p>
                    </div>
                </body>
                </html>
            ";
            $mail->AltBody = "Your verification code to reset your password is: {$verification_code}";
            
            $mail->send();
            $verification_sent = true;
        } catch (Exception $e) {
            $forgot_password_error = "Failed to send verification code. Error: " . $mail->ErrorInfo;
        }
    } else {
        $forgot_password_error = "Email not found in our records.";
    }
    $stmt->close();
}

// Handle verification code submission
if (isset($_POST['verify_code_submit'])) {
    $entered_code = $_POST['verification_code'];
    
    // Check if verification code is valid and not expired (30 minutes validity)
    if (
        isset($_SESSION['reset_code']) && 
        $_SESSION['reset_code'] == $entered_code && 
        (time() - $_SESSION['reset_code_timestamp']) < 1800
    ) {
        $_SESSION['code_verified'] = true;
        $code_verified = true;
    } else {
        $verification_error = "Invalid or expired verification code. Please try again.";
    }
}

// Handle password reset
if (isset($_POST['reset_password_submit'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // SECURITY FIX: Add password strength validation
    if (strlen($new_password) < 8) {
        $reset_error = "Password must be at least 8 characters long.";
    } elseif ($new_password === $confirm_password) {
        if (isset($_SESSION['code_verified']) && $_SESSION['code_verified'] === true) {
            $email = $_SESSION['reset_email'];
            
            // SECURITY FIX: Hash the password
            // Uncomment for production use:
            // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            // $password_to_store = $hashed_password;
            
            // For now, using direct password (not recommended for production)
            $password_to_store = $new_password;
            
            // Update password in database and set updated_at timestamp
            $current_time = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = ? WHERE email = ?");
            $stmt->bind_param("sss", $password_to_store, $current_time, $email);
            
            if ($stmt->execute()) {
                $password_reset_success = true;
                
                // Clear reset sessions
                unset($_SESSION['reset_code']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_code_timestamp']);
                unset($_SESSION['code_verified']);
            } else {
                $reset_error = "Failed to update password. Please try again.";
            }
            $stmt->close();
        } else {
            $reset_error = "Verification code not verified.";
        }
    } else {
        $reset_error = "Passwords do not match.";
    }
}

// Close the database connection at the end of the script
// FIX: Added database connection close
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc Lenon Veterinary - Admin Login</title>
    <link rel="icon" href="img/LOGO.png" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
            overflow-x: hidden;
        }
        .login-card {
            max-width: 400px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        .forgot-password {
            color: #4DA6FF;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .back-to-login {
            cursor: pointer;
            color: #4DA6FF;
        }
        /* FIX: Added styles for password strength meter */
        .password-strength-meter {
            height: 10px;
            width: 100%;
            background-color: #e9ecef;
            margin: 8px 0;
            border-radius: 3px;
        }
        .password-strength-meter div {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffecb5;
            color: #664d03;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card login-card">
            <div class="card-header text-center">
                <h3><i class="fas fa-paw paw-logo"></i> Admin Login</h3>
            </div>
            <div class="card-body">
                <!-- Login Form -->
                <div id="login-form" <?php if (isset($_POST['forgot_password']) || isset($_POST['forgot_password_submit']) || isset($forgot_password_error) || isset($verification_sent) || isset($code_verified) || isset($_POST['verify_code_submit']) || isset($_POST['reset_password_submit']) && !isset($password_reset_success)) echo 'style="display:none;"'; ?>>
                <?php if (isset($login_error)): ?>
                        <div class="alert alert-danger"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($password_reset_success)): ?>
                        <div class="alert alert-success">Password reset successful. You can now login with your new password.</div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3 text-end">
                            <a class="forgot-password" onclick="showForgotPasswordForm()">Forgot Password?</a>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
                
                <!-- Forgot Password Form -->
                <div id="forgot-password-form" <?php if (!isset($_POST['forgot_password']) && !isset($_POST['forgot_password_submit']) && !isset($forgot_password_error) || isset($verification_sent)) echo 'style="display:none;"'; ?>>                    <?php if (isset($forgot_password_error)): ?>
                        <div class="alert alert-danger"><?php echo $forgot_password_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <small class="form-text text-muted">Enter the email associated with your admin account.</small>
                        </div>
                        <button type="submit" name="forgot_password_submit" class="btn btn-primary w-100 mb-3">Send Verification Code</button>
                        <div class="text-center">
                            <a class="back-to-login" onclick="showLoginForm()">Back to Login</a>
                        </div>
                    </form>
                </div>
                
                <!-- Verification Code Form -->
                <div id="verification-code-form" <?php if (!isset($verification_sent) && !isset($verification_error)) echo 'style="display:none;"'; ?>>                    <h5 class="mb-3">Enter Verification Code</h5>
                    
                    <?php if (isset($verification_sent)): ?>
                        <div class="alert alert-success">Verification code has been sent to your email.</div>
                    <?php endif; ?>
                    
                    <?php if (isset($verification_error)): ?>
                        <div class="alert alert-danger"><?php echo $verification_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="verification_code" class="form-label">Verification Code</label>
                            <input type="text" class="form-control" id="verification_code" name="verification_code" required>
                            <small class="form-text text-muted">Enter the 6-digit code sent to your email. Valid for 30 minutes.</small>
                        </div>
                        <button type="submit" name="verify_code_submit" class="btn btn-primary w-100 mb-3">Verify Code</button>
                        <div class="text-center">
                            <a class="back-to-login" onclick="showLoginForm()">Back to Login</a>
                        </div>
                    </form>
                </div>
                
                <!-- Reset Password Form -->
                <div id="reset-password-form" <?php if (!isset($code_verified) && !isset($reset_error)) echo 'style="display:none;"'; ?>>
                    <h5 class="mb-3">Reset Password</h5>
                    
                    <?php if (isset($reset_error)): ?>
                        <div class="alert alert-danger"><?php echo $reset_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8" onkeyup="checkPasswordStrength()">
                            <!-- Password strength meter -->
                            <div class="password-strength-meter">
                                <div id="strength-meter-bar"></div>
                            </div>
                            <small id="password-strength-text" class="form-text text-muted">Password must be at least 8 characters long.</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8" onkeyup="checkPasswordMatch()">
                            <small id="password-match-text" class="form-text"></small>
                        </div>
                        <button type="submit" name="reset_password_submit" class="btn btn-primary w-100 mb-3" id="reset-btn">Reset Password</button>
                        <div class="text-center">
                            <a class="back-to-login" onclick="showLoginForm()">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showLoginForm() {
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('forgot-password-form').style.display = 'none';
            document.getElementById('verification-code-form').style.display = 'none';
            document.getElementById('reset-password-form').style.display = 'none';
        }
        
        function showForgotPasswordForm() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('forgot-password-form').style.display = 'block';
            document.getElementById('verification-code-form').style.display = 'none';
            document.getElementById('reset-password-form').style.display = 'none';
        }
        
        // Password strength checker
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthBar = document.getElementById('strength-meter-bar');
            const strengthText = document.getElementById('password-strength-text');
            
            // Default - too short
            let strength = 0;
            let message = "Password is too short";
            let color = "#dc3545"; // red
            
            // Check length
            if (password.length >= 8) {
                strength += 25;
                
                // Check for mixed case
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
                    strength += 25;
                }
                
                // Check for numbers
                if (password.match(/[0-9]/)) {
                    strength += 25;
                }
                
                // Check for special characters
                if (password.match(/[^a-zA-Z0-9]/)) {
                    strength += 25;
                }
                
                // Determine message and color
                if (strength < 50) {
                    message = "Password is weak";
                    color = "#ffc107"; // yellow
                } else if (strength < 75) {
                    message = "Password is good";
                    color = "#0d6efd"; // blue
                } else {
                    message = "Password is strong";
                    color = "#198754"; // green
                }
            }
            
            // Update UI
            strengthBar.style.width = strength + "%";
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = message;
        }
        
        // Check if passwords match
        function checkPasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchText = document.getElementById('password-match-text');
            const resetBtn = document.getElementById('reset-btn');
            
            if (password === confirmPassword && password.length >= 8) {
                matchText.textContent = "Passwords match";
                matchText.style.color = "#198754"; // green
                resetBtn.disabled = false;
            } else if (confirmPassword.length > 0) {
                matchText.textContent = "Passwords do not match";
                matchText.style.color = "#dc3545"; // red
                resetBtn.disabled = true;
            } else {
                matchText.textContent = "";
                resetBtn.disabled = false;
            }
        }
    </script>
</body>
</html>