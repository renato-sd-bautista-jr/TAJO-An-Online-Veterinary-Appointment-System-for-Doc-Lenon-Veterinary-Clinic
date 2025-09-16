<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Section</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .feedback-card {
            border-left: 5px solid #007bff; /* Accent border */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Soft shadow */
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4 text-center">User Feedback</h2>
    <div class="card p-4">
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "feedback_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
        }

        $sql = "SELECT name, comment, created_at FROM feedback ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='card feedback-card p-3 mb-3'>";
                echo "<div class='d-flex align-items-center'>";
                echo "<img src='https://via.placeholder.com/50' alt='User' class='user-avatar'>"; // Placeholder image
                echo "<div>";
                echo "<h5 class='mb-0'>" . htmlspecialchars($row['name']) . "</h5>";
                echo "<small class='text-muted'>" . date("F j, Y, g:i a", strtotime($row['created_at'])) . "</small>";
                echo "</div></div>";
                echo "<p class='mt-2'>" . nl2br(htmlspecialchars($row['comment'])) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p class='text-muted text-center'>No feedback available.</p>";
        }

        $conn->close();
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
