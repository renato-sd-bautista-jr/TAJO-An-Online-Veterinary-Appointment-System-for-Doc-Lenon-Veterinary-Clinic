<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Feedback</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

    <div class="container mt-5">
        <h2 class="text-center">User Feedback</h2>

        <!-- Feedback Form -->
        <div class="card p-4">
            <h4>Leave Your Feedback</h4>
            <form id="feedback-form" action="submit_feedback.php" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Your Name:</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="feedback" class="form-label">Your Feedback:</label>
                    <textarea class="form-control" id="feedback" name="feedback" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary" onclick="showMessage()">Submit Feedback</button>

<script>
    function showMessage() {
        alert("Thank you for your feedback!");
        window.location.href = "http://localhost/TAHO/"; // Change this to your actual index page URL
    }
</script>

            </form>

            <script>
                document.getElementById("feedback-form").addEventListener("submit", function(event) {
                    event.preventDefault();
                    var form = event.target;
                    var formData = new FormData(form);

                    fetch(form.action, {
                        method: form.method,
                        body: formData
                    })
                    .then(response => response.text())
                    .then(result => {
                        document.getElementById("feedback-list").innerHTML = result;
                        form.reset();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
</script>

        </div>

        <!-- Display Previous Feedback -->
        <div class="mt-5">
            <h4>Previous Customer Feedback</h4>
            <div id="feedback-list" class="mt-3">
                <?php include 'display_feedback.php'; ?>
            </div>
        </div>
    </div>

</body>
</html>
