<?php
// Start session for admin authentication
session_start();

// Check if admin is not logged in, redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
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

// Create image uploads directory if it doesn't exist
$upload_dir = "product_images/";
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        die("Failed to create upload directory.");
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Determine which page to show
$page = isset($_GET['page']) ? $_GET['page'] : 'products';

// Include common admin functions for products
if ($page == 'products') {
    // Handle product form submission for adding or updating
    if (isset($_POST['save_product'])) {
        $product_name = $_POST['product_name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $stock = (int)$_POST['stock'];
    if ($stock < 0) $stock = 0;

        // Initialize image_path with NULL for new products or the current image for existing products
        $image_path = isset($_POST['current_image']) ? $_POST['current_image'] : NULL;

        // Handle image upload only if a file was actually selected
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['product_image']['type'];

            if (in_array($file_type, $allowed_types)) {
                $filename = time() . '_' . basename($_FILES['product_image']['name']);
                $target_path = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                    $image_path = $target_path; // Only override if new upload is successful
                } else {
                    $image_error = "Failed to upload image. Please check file permissions.";
                }
            } else {
                $image_error = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
        }

        if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
            // Update existing product
            $product_id = $_POST['product_id'];

            // Check if image_path is NULL and there's a current_image value
            if ($image_path === NULL && !isset($_POST['current_image'])) {
            $sql = "UPDATE products 
            SET Product_Name=?, Category=?, Price=?, Stock=? 
            WHERE ID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdii", $product_name, $category, $price, $stock, $product_id);
        } else {
            $sql = "UPDATE products 
            SET Product_Name=?, Category=?, Price=?, Stock=?, Image=? 
            WHERE ID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdisi", $product_name, $category, $price, $stock, $image_path, $product_id);

        }


            if ($stmt->execute()) {
                $message = "Product updated successfully";
            } else {
                $error = "Error updating product: " . $conn->error;
            }
        } else {
            // Add new product
            $sql = "INSERT INTO products 
            (Product_Name, Category, Price, Stock, Image) 
            VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdis", $product_name, $category, $price, $stock, $image_path);
            if ($stmt->execute()) {
                $message = "Product added successfully";
            } else {
                $error = "Error adding product: " . $conn->error;
            }
        }
    }

    // Handle delete product
    if (isset($_GET['delete']) && !empty($_GET['delete'])) {
        $product_id = $_GET['delete'];

        // First get the image path to delete the file
        $sql = "SELECT Image FROM products WHERE ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['Image'] && file_exists($row['Image'])) {
                unlink($row['Image']); // Delete the image file
            }
        }

        // Then delete the product
        $sql = "DELETE FROM products WHERE ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            // Check if there are any products left
            $check_sql = "SELECT COUNT(*) as count FROM products";
            $count_result = $conn->query($check_sql);
            $count_row = $count_result->fetch_assoc();
            
            if ($count_row['count'] == 0) {
                // If no products left, reset auto-increment to 1
                $reset_sql = "ALTER TABLE products AUTO_INCREMENT = 1";
                $conn->query($reset_sql);
            } else {
                // Find the highest ID and set auto-increment to next value
                $max_sql = "SELECT MAX(ID) as max_id FROM products";
                $max_result = $conn->query($max_sql);
                $max_row = $max_result->fetch_assoc();
                $next_id = $max_row['max_id'] + 1;
                
                $reset_sql = "ALTER TABLE products AUTO_INCREMENT = $next_id";
                $conn->query($reset_sql);
            }
            
            $message = "Product deleted successfully";
        } else {
            $error = "Error deleting product: " . $conn->error;
        }
    }

    // Get product to edit
    $edit_product = null;
    if (isset($_GET['edit']) && !empty($_GET['edit'])) {
        $product_id = $_GET['edit'];
        $sql = "SELECT * FROM products WHERE ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $edit_product = $result->fetch_assoc();
        }
    }

    // Get search term for category filtering
    $search_name = $_GET['search_name'] ?? '';
    $filter_category = $_GET['filter_category'] ?? '';

    $query = "SELECT * FROM products WHERE 1=1";

    if (!empty($search_name)) {
        $safe_name = "%" . $conn->real_escape_string($search_name) . "%";
        $query .= " AND Product_Name LIKE '$safe_name'";
    }

    if (!empty($filter_category)) {
        $safe_category = $conn->real_escape_string($filter_category);
        $query .= " AND Category = '$safe_category'";
    }

    $query .= " ORDER BY Category, Product_Name";
    $result = $conn->query($query);

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    // Get all unique categories
    $sql = "SELECT DISTINCT Category FROM products ORDER BY Category";
    $result = $conn->query($sql);
    $categories = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['Category'];
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc Lenon Veterinary - Admin Panel</title>
    <link rel="icon" href="img/LOGO.png" type="image/png" alt="Doc Lenon Veterinary Logo">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            overflow-x: hidden;
        }
        .admin-header {
            background-color: #4DA6FF;
            color: white;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
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
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(77, 166, 255, 0.1);
        }
        .paw-logo {
            color: #4DA6FF;
            margin-right: 10px;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 4px;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .search-container {
            margin-bottom: 20px;
        }
        
        /* Sidebar & Burger Menu Styles */
        .sidebar {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            background-color: #222;
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.2);
        }
        
        .sidebar a {
            padding: 12px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #fff;
            display: block;
            transition: 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover {
            background-color: #333;
            border-left: 3px solid #4DA6FF;
        }
        
        .sidebar a.active {
            background-color: #2c2c2c;
            border-left: 3px solid #4DA6FF;
            font-weight: bold;
        }
        
        .sidebar .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 30px;
            margin-left: 50px;
        }
        
        .main-content {
            transition: margin-left .5s;
            padding: 16px;
        }
        
        .burger-menu {
            font-size: 24px;
            cursor: pointer;
            color: white;
            margin-right: 15px;
        }
        
        .sidebar-header {
            padding: 0 15px 20px 15px;
            color: #4DA6FF;
            font-size: 22px;
            position: absolute;
            top: 15px;
            left: 15px;
        }
            .table thead th {
            white-space: nowrap;
        }

        .img-thumbnail {
            border-radius: 8px;
        }

        .alert-info {
            font-size: 0.9rem;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
   <div id="mySidebar" class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-paw"></i> Doc Lenon
        </div>
        <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">&times;</a>
        <a href="admin.php?page=products" class="active">
            <i class="fas fa-box"></i> Products Inventory
        </a>
        <a href="post.php" >
            <i class="fas fa-blog"></i> Post Management
        </a>
        <a href="calendar.php">
            <i class="fas fa-calendar-alt"></i> Appointment Calendar
        </a>
        <a href="history1.php">
            <i class="fas fa-history"></i> Appointment History
        </a>
        <a href="ordermanagement.php">
            <i class="fas fa-box"></i> Order Management
        </a>
        <a href="analytics.php">
            <i class="fas fa-chart-bar"></i> Analytics
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>



    <!-- Main Content -->
    <div id="main" class="main-content">
        <div class="admin-header">
            <div class="d-flex align-items-center">
                <span class="burger-menu" onclick="openNav()">
                    <i class="fas fa-bars"></i>
                </span>
                <h2><i class="fas fa-paw paw-logo"></i> Doc Lenon Veterinary</h2>
            </div>
            <div>
                <span class="text-light me-3">Welcome, Admin</span>
                <a href="?logout=1" class="btn btn-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="container">
            <?php if (isset($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($image_error)): ?>
                <div class="alert alert-warning"><?php echo $image_error; ?></div>
            <?php endif; ?>
            
            <?php if ($page == 'products'): ?>
                <!-- Products Page -->
                <div class="row">
                    <div class="col-md-4">
                        <!-- Product Form -->
                        <div class="card">
                            <div class="card-header">
                                <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
                            </div>
                            <div class="card-body">
                                <form method="post" enctype="multipart/form-data">
                                    <?php if ($edit_product): ?>
                                        <input type="hidden" name="product_id" value="<?php echo $edit_product['ID']; ?>">
                                        <?php if ($edit_product['Image']): ?>
                                            <input type="hidden" name="current_image" value="<?php echo $edit_product['Image']; ?>">
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="product_name" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="product_name" name="product_name" 
                                               value="<?php echo $edit_product ? $edit_product['Product_Name'] : ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">-- Select Category --</option>
                                            <?php
                                            // Fetch categories from DB
                                            $catQuery = $conn->query("SELECT name FROM category ORDER BY name ASC");
                                            if ($catQuery && $catQuery->num_rows > 0) {
                                                while ($cat = $catQuery->fetch_assoc()) {
                                                    $selected = ($edit_product && $edit_product['Category'] == $cat['name']) ? 'selected' : '';
                                                    echo "<option value='{$cat['name']}' $selected>{$cat['name']}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                                   value="<?php echo $edit_product ? $edit_product['Price'] : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stock / Quantity</label>
                                        <input type="number" class="form-control" id="stock" name="stock"
                                            min="0"
                                            value="<?php echo $edit_product ? $edit_product['Stock'] : '0'; ?>" required>
                                    </div>
                                    
                                    
                                    <div class="mb-3">
                                        <label for="product_image" class="form-label">Product Image</label>
                                        <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*" 
                                               <?php echo $edit_product ? '' : 'required'; ?>>
                                        <div class="form-text">Accepted formats: JPG, PNG, GIF, WEBP</div>
                                        
                                        <?php if ($edit_product && $edit_product['Image']): ?>
                                            <div class="mt-2">
                                                <p>Current image:</p>
                                                <img src="<?php echo $edit_product['Image']; ?>" alt="<?php echo $edit_product['Product_Name']; ?>" class="image-preview">
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div id="imagePreviewContainer" class="mt-2 d-none">
                                            <p>New image preview:</p>
                                            <img id="imagePreview" class="image-preview">
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="save_product" class="btn btn-primary">
                                            <i class="fas fa-save"></i> <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                                        </button>
                                        
                                        <?php if ($edit_product): ?>
                                            <a href="?page=products" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
    <!-- Product List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product List</h5>
        </div>
        <div class="card-body">
            <!-- Search and Filter -->
            <form method="get" class="mb-3">
                <input type="hidden" name="page" value="products">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="search_name" placeholder="Search by product name..."
                               value="<?php echo htmlspecialchars($_GET['search_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="filter_category">
                            <option value="">-- Filter by Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo (isset($_GET['filter_category']) && $_GET['filter_category'] === $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-50">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="?page=products" class="btn btn-secondary w-50">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>

            <?php if (!empty($_GET['search_name']) || !empty($_GET['filter_category'])): ?>
                <div class="alert alert-info">
                    Showing products 
                    <?php if (!empty($_GET['search_name'])): ?>
                        with name containing <strong><?php echo htmlspecialchars($_GET['search_name']); ?></strong>
                    <?php endif; ?>
                    <?php if (!empty($_GET['filter_category'])): ?>
                        in category <strong><?php echo htmlspecialchars($_GET['filter_category']); ?></strong>
                    <?php endif; ?>
                    (<?php echo count($products); ?> results found)
                </div>
            <?php endif; ?>

            <!-- Product Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td style="width: 70px;">
                                        <?php if (!empty($product['Image'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['Image']); ?>" 
                                                 alt="Product Image" class="img-thumbnail" style="height: 60px; width: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="text-muted text-center small">No Image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['Product_Name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['Category']); ?></td>
                                    <td>₱<?php echo number_format($product['Price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['Stock']); ?></td>
                                    <td class="text-center">
                                        <a href="?page=products&edit=<?php echo $product['ID']; ?>" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                           <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);" 
                                           onclick="confirmDelete(<?php echo $product['ID']; ?>)" 
                                           class="btn btn-sm btn-danger" title="Delete">
                                           <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript for Image Preview -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('product_image');
            const imagePreview = document.getElementById('imagePreview');
            const previewContainer = document.getElementById('imagePreviewContainer');
            
            if (imageInput && imagePreview && previewContainer) {
                imageInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                            previewContainer.classList.remove('d-none');
                        }
                        
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        previewContainer.classList.add('d-none');
                    }
                });
            }
        });
        
        function confirmDelete(id, searchCategory) {
            if (confirm('Are you sure you want to delete this product?')) {
                let url = `?page=products&delete=${id}`;
                if (searchCategory) {
                    url += `&search_category=${searchCategory}`;
                }
                window.location.href = url;
            }
        }
        
        function openNav() {
            document.getElementById("mySidebar").style.width = "250px";
            document.getElementById("main").style.marginLeft = "250px";
        }
        
        function closeNav() {
            document.getElementById("mySidebar").style.width = "0";
            document.getElementById("main").style.marginLeft = "0";
        }
    </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>