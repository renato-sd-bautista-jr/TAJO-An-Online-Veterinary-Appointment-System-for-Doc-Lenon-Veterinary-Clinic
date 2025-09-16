<?php
$servername = "localhost";
$username = "root";  // Change this if needed
$password = "";  // Change this if needed
$dbname = "feedback_db";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$name = htmlspecialchars($_POST['name']);
$feedback = htmlspecialchars($_POST['feedback']);

// Insert into database
$sql = "INSERT INTO feedback (name, comment) VALUES ('$name', '$feedback')";
if ($conn->query($sql) === TRUE) {
    echo "Feedback submitted successfully!";
} else {
    echo "Error: " . $conn->error;
}

// Close connection
$conn->close();

// Redirect back to the feedback page
header("Location: user_feedback.html");
exit();
?>
