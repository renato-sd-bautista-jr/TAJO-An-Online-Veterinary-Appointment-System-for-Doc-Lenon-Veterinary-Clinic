<?php
// Start session
session_start();

// Check if confirmed logout
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <style>
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .confirmation-box {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            z-index: 1001;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-secondary {
            background-color: #f44336;
            color: white;
        }
        .background-frame {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            z-index: 1;
        }
    </style>
</head>
<body>
    <!-- Load the index page as background -->
    
    <!-- Overlay with logout confirmation -->
    <div class="overlay">
        <div class="confirmation-box">
            <h2>Logout Confirmation</h2>
            <p>Are you sure you want to logout?</p>
            <div>
                <a href="?confirm=yes" class="btn btn-primary">Yes, Logout</a>
                <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
</body>
</html>