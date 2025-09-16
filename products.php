<?php
// Start session
session_start();

// Check if admin is logged in


// Database connection
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "taho"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from the database
$sql = "SELECT * FROM products ORDER BY Category, Product_Name";
$result = $conn->query($sql);

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products - TAHO</title>
    <link rel="icon" href="img/LOGO.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .card img {
            height: 200px;
            object-fit: cover;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 0;
            background-color: #e3f2fd;
        }
        footer {
            background-color: #e3f2fd;
            color: #2c3e50;
            padding: 15px 0;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar" style="background-color: #e3f2fd;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="img/LOGO.png" alt="Logo" width="45" height="40" class="d-inline-block align-text-top">
                DOC LENON VETERINARY
            </a>
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="Index.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Services</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="pw.php">Pet Wellness</a></li>
                        <li><a class="dropdown-item" href="Consultation.php">Consultation</a></li>
                        <li><a class="dropdown-item" href="Vaccine.php">Vaccination</a></li>
                        <li><a class="dropdown-item" href="deworming.php">Deworming</a></li>
                        <li><a class="dropdown-item" href="laboratory.php">Laboratory</a></li>
                        <li><a class="dropdown-item" href="Surgery.php">Surgery</a></li>
                        <li><a class="dropdown-item" href="Confinement.php">Confinement</a></li>
                        <li><a class="dropdown-item" href="Grooming.php">Grooming</a></li>
                        <li><a class="dropdown-item" href="Pet-Boarding.php">Pet Boarding</a></li>
                        
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="Contact Us.php">Contact Us</a>
                </li>
                <li>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='Appointment.php'">Book Appointment</button>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Products Section -->
    <div class="container mt-5">
        <h1 class="text-center mb-4">Our Products</h1>
        <div class="row">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($product['Image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['Image']); ?>" class="card-img-top" alt="Product Image">
                            <?php else: ?>
                                <img src="img/placeholder.jpg" class="card-img-top" alt="No Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['Product_Name']); ?></h5>
                                <p class="card-text">Category: <?php echo htmlspecialchars($product['Category']); ?></p>
                                <p class="card-text">Price: ₱<?php echo htmlspecialchars(number_format($product['Price'], 2)); ?></p>
                                <p class="card-text">Stock: <?php echo htmlspecialchars($product['Stock']); ?></p>
                            </div>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">No products found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; ALL RIGHTS RESERVED 2025 SE FINAL - TAHO</p>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>