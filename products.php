<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "taho";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch categories for filter
$catQuery = $conn->query("SELECT name FROM category ORDER BY name ASC");
$categories = [];
while ($cat = $catQuery->fetch_assoc()) {
    $categories[] = $cat['name'];
}

// Fetch products
$sql = "SELECT * FROM products ORDER BY Category, Product_Name";
$result = $conn->query($sql);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Products - Doc Lenon Veterinary</title>
  <link rel="icon" href="img/LOGO.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
      body {
          background-color: #f8f9fa;
      }
      .navbar {
          background-color: #e3f2fd;
          box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      .product-card {
          border: none;
          border-radius: 12px;
          overflow: hidden;
          transition: transform 0.3s ease, box-shadow 0.3s ease;
          box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      }
      .product-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 4px 16px rgba(0,0,0,0.15);
      }
      .product-card img {
          height: 200px;
          object-fit: cover;
          width: 100%;
      }
      .card-body h5 {
          color: #007bff;
          font-weight: 600;
      }
      .filter-bar {
          background: white;
          padding: 15px 20px;
          border-radius: 10px;
          box-shadow: 0 2px 8px rgba(0,0,0,0.05);
          margin-bottom: 30px;
      }
      footer {
          background-color: #e3f2fd;
          color: #2c3e50;
          padding: 15px 0;
          text-align: center;
          margin-top: 40px;
      }
  </style>
</head>
<body>

<!-- Navigation Bar -->
    <nav class="navbar" style="background-color: #e3f2fd;">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">
          <img src="img/LOGO.png" alt="Logo" width="45" height="40" class="d-inline-block align-text-top">
          DOC LENON VETERINARY
        </a>
        <ul class="nav nav-tabs">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="pw.php">Services Post</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="products.php">Products</a>
          </li>
          <li class="nav-item">
            <a class="nav-link " aria-current="page" href="contact.php">Contact Us</a>
          </li>
            <button type="button" class="btn btn-primary" onclick="window.location.href='Appointment.php'">Book Appointment</button>
          </li>
        </ul>
      </div>
    </nav>

<!-- Main Section -->
<div class="container mt-5">
  <h1 class="text-center mb-4 text-primary fw-bold">Our Products</h1>

  <!-- Filter & Search -->
  <div class="filter-bar d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex align-items-center gap-2">
      <label for="categoryFilter" class="fw-semibold text-secondary">Category:</label>
      <select id="categoryFilter" class="form-select w-auto">
        <option value="all">All</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="d-flex align-items-center gap-2">
      <input type="text" id="searchInput" class="form-control" placeholder="Search product name..." style="min-width:250px;">
    </div>
  </div>

  <!-- Product Cards -->
  <div class="row" id="productGrid">
    <?php if (!empty($products)): ?>
      <?php foreach ($products as $product): ?>
        <div class="col-md-4 mb-4 product-item" 
             data-category="<?php echo strtolower($product['Category']); ?>" 
             data-name="<?php echo strtolower($product['Product_Name']); ?>">
          <div class="card product-card h-100">
            <img src="<?php echo !empty($product['Image']) ? htmlspecialchars($product['Image']) : 'img/placeholder.jpg'; ?>" alt="Product Image">
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($product['Product_Name']); ?></h5>
              <p class="mb-1 text-muted"><strong>Category:</strong> <?php echo htmlspecialchars($product['Category']); ?></p>
              <p class="mb-1"><strong>Price:</strong> ₱<?php echo number_format($product['Price'], 2); ?></p>
              <p class="mb-1"><strong>Stock:</strong> <?php echo htmlspecialchars($product['Stock']); ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <p>No products found.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Footer -->
<footer>
  <p>&copy; ALL RIGHTS RESERVED 2025 SE FINAL - TAHO</p>
</footer>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const categoryFilter = document.getElementById('categoryFilter');
  const searchInput = document.getElementById('searchInput');
  const products = document.querySelectorAll('.product-item');

  function filterProducts() {
    const category = categoryFilter.value.toLowerCase();
    const search = searchInput.value.toLowerCase();

    products.forEach(prod => {
      const matchesCategory = (category === 'all' || prod.dataset.category === category);
      const matchesSearch = prod.dataset.name.includes(search);
      prod.style.display = (matchesCategory && matchesSearch) ? '' : 'none';
    });
  }

  categoryFilter.addEventListener('change', filterProducts);
  searchInput.addEventListener('keyup', filterProducts);
});
</script>
</body>
</html>
