    <?php
// Start session for any potential user authentication needs
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

// Set the category we want to display
$category = "Confinement";

// Fetch posts from the database with the specified category
$sql = "SELECT * FROM posts WHERE Category = ? ORDER BY Created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Get recent posts for sidebar (limited to 5)
$sql_recent = "SELECT ID, Title, Created_at FROM posts ORDER BY Created_at DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);
$recent_posts = [];
if ($result_recent && $result_recent->num_rows > 0) {
    while ($row = $result_recent->fetch_assoc()) {
        $recent_posts[] = $row;
    }
}

// Get all categories for sidebar
$sql_categories = "SELECT DISTINCT Category, COUNT(*) as count FROM posts GROUP BY Category ORDER BY Category";
$result_categories = $conn->query($sql_categories);
$categories = [];
if ($result_categories && $result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get full post content if an ID is provided via AJAX
if(isset($_GET['get_content']) && isset($_GET['id'])) {
    $post_id = $_GET['id'];
    $sql = "SELECT Title, Content, Image, Created_at FROM posts WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0) {
        $post = $result->fetch_assoc();
        echo json_encode($post);
        exit;
    } else {
        echo json_encode(["error" => "Content not found"]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confinement - Doc Lenon Veterinary</title>
    <link rel="icon" href="img/LOGO.png" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Navbar styles */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 0;
            background-color: #e3f2fd;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-right: 2rem;
        }
        
        .nav-link {
            font-weight: 500;
            color: #2c3e50;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: #3498db;
        }
        
        /* Header styles */
        .page-header {
            background-color: #e3f2fd;
            padding: 30px 0;
            margin-bottom: 40px;
        }
        
        .page-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .page-description {
            color: #555;
            font-size: 1.1rem;
            max-width: 800px;
        }
        
        /* Post styles - COMPLETELY REDESIGNED */
        .post-item {
            background-color: #fff;
            border-radius: 10px;
            margin-bottom: 60px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .post-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        /* Image container is now separate and full-width */
        .post-image-container {
            width: 100%;
            height: 500px; /* Significantly increased height for larger images */
            overflow: hidden;
            position: relative;
        }
        
        .post-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s;
        }
        
        .post-image-container:hover .post-img {
            transform: scale(1.05);
        }
        
        /* Image overlay with category */
        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            z-index: 2;
        }
        
        /* Text container is now separate */
        .post-content-container {
            padding: 35px;
        }
        
        .post-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 2rem;
        }
        
        .post-meta {
            font-size: 0.95rem;
            color: #777;
            margin-bottom: 25px;
        }
        
        .post-excerpt {
            color: #555;
            margin-bottom: 30px;
            line-height: 1.8;
            font-size: 1.15rem;
        }
        
        .category-tag {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .read-more {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 14px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1.05rem;
        }
        
        .read-more:hover {
            background-color: #2980b9;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* No posts message */
        .no-posts {
            text-align: center;
            padding: 70px 0;
            color: #777;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .no-posts i {
            color: #3498db;
            margin-bottom: 20px;
        }
        
        /* Footer styles */
        footer {
            background-color: #e3f2fd;
            color: #2c3e50;
            padding: 25px 0;
            text-align: center;
            margin-top: 60px;
        }
        
        /* Modal Styles */
        .content-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            z-index: 1000;
            overflow-y: auto;
            animation: fadeIn 0.3s ease;
        }
        
        .modal-content-container {
            background-color: white;
            width: 90%;
            max-width: 900px;
            margin: 30px auto;
            border-radius: 10px;
            padding: 0;
            position: relative;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.4s ease-out;
            overflow: hidden;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            color: white;
            background-color: rgba(0, 0, 0, 0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }
        
        .modal-close:hover {
            background-color: rgba(0, 0, 0, 0.8);
            transform: rotate(90deg);
        }
        
        .modal-header {
            padding: 0;
            position: relative;
        }
        
        .modal-header-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        .modal-header-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            color: white;
        }
        
        .modal-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .modal-meta {
            font-size: 1rem;
            opacity: 0.8;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .modal-content-text {
            font-size: 1.15rem;
            line-height: 1.8;
            color: #333;
        }
        
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1001;
        }
        
        .spinner-lg {
            width: 3rem;
            height: 3rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @media (max-width: 767.98px) {
            .post-image-container {
                height: 300px;
            }
            
            .post-content-container {
                padding: 25px;
            }
            
            .post-title {
                font-size: 1.6rem;
            }
            
            .modal-title {
                font-size: 1.8rem;
            }
            
            .modal-content-container {
                width: 95%;
                margin: 15px auto;
            }
            
            .modal-header-image {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="img/LOGO.png" alt="Logo" width="45" height="40" class="d-inline-block align-text-top">
                DOC LENON VETERINARY
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Services</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="pw.php">Pet Wellness</a></li>
                            <li><a class="dropdown-item" href="Consultation.php">Consultation</a></li>
                            <li><a class="dropdown-item" href="Vaccine.php">Vaccination</a></li>
                            <li><a class="dropdown-item" href="deworming.php">Deworming</a></li>
                            <li><a class="dropdown-item" href="laboratory.php">Laboratory</a></li>
                            <li><a class="dropdown-item" href="Surgery.php">Surgery</a></li>
                            <li><a class="dropdown-item" href="Confinement.php">Confinement</a></li>
                            <li><a class="dropdown-item" href="Grooming.php">Grooming</a></li>
                            <li><a class="dropdown-item" href="Pet-Boarding.php">Pet Boarding</a></li>
                            <li><a class="dropdown-item" href="Order-Products.php">Order Products</a></li>
                            
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Contact Us.php">Contact Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="Appointment.php">Book Appointment</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
        <!-- Posts Section -->
        <div class="row">
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="col-12 mb-5">
                        <div class="post-item">
                            <!-- Image container - Now separate and full width -->
                            <div class="post-image-container">
                                <?php if (!empty($post['Image'])): ?>
                                    <img src="<?php echo $post['Image']; ?>" class="post-img" alt="<?php echo htmlspecialchars($post['Title']); ?>">
                                <?php else: ?>
                                    <img src="img/default-post.jpg" class="post-img" alt="Default Image">
                                <?php endif; ?>
                                
                                <!-- Category overlay on the image -->
                                <div class="image-overlay">
                                    <span class="category-tag"><?php echo htmlspecialchars($post['Category']); ?></span>
                                </div>
                            </div>
                            
                            <!-- Text content container - Now separate -->
                            <div class="post-content-container">
                                <h2 class="post-title"><?php echo htmlspecialchars($post['Title']); ?></h2>
                                <div class="post-meta">
                                    <i class="far fa-calendar-alt"></i> <?php echo date('F d, Y', strtotime($post['Created_at'])); ?>
                                    <span class="ms-3"><i class="far fa-user"></i> Doc Lenon Veterinary</span>
                                </div>
                                <div class="post-excerpt">
                                    <?php 
                                    // Strip HTML and limit content preview
                                    $content = strip_tags($post['Content']);
                                    echo strlen($content) > 250 ? substr($content, 0, 250) . '...' : $content; 
                                    ?>
                                </div>
                                <button class="read-more" data-id="<?php echo $post['ID']; ?>">
                                    Read More <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="no-posts">
                        <i class="fas fa-paw fa-3x mb-3"></i>
                        <h3>No posts found in this category</h3>
                        <p>Please check back later for updates on Pet Wellness.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="mb-0">&copy; ALL RIGHTS RESERVED 2025 SE FINAL - TAHO</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border text-light spinner-lg" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- Full Content Modal -->
    <div class="content-modal" id="contentModal">
        <div class="modal-content-container">
            <div class="modal-close" id="modalClose">
                <i class="fas fa-times"></i>
            </div>
            <div class="modal-header">
                <img src="img/default-post.jpg" class="modal-header-image" id="modalImage" alt="Post Image">
                <div class="modal-header-overlay">
                    <h2 class="modal-title" id="modalTitle">Post Title</h2>
                    <div class="modal-meta" id="modalMeta">
                        <i class="far fa-calendar-alt"></i> <span id="modalDate">January 1, 2025</span>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="modal-content-text" id="modalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            const $modal = $('#contentModal');
            const $spinner = $('.loading-spinner');
            const $modalImage = $('#modalImage');
            const $modalTitle = $('#modalTitle');
            const $modalDate = $('#modalDate');
            const $modalContent = $('#modalContent');
            
            // Cache for loaded content
            const contentCache = {};
            
            // Read More button click
            $('.read-more').on('click', function() {
                const postId = $(this).data('id');
                
                // Check if content is already cached
                if (contentCache[postId]) {
                    displayContent(contentCache[postId]);
                    return;
                }
                
                // Show loading spinner
                $spinner.show();
                
                // Ajax request to get the content
                $.ajax({
                    url: 'pw.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        get_content: true,
                        id: postId
                    },
                    success: function(response) {
                        // Hide spinner
                        $spinner.hide();
                        
                        // Cache the response
                        contentCache[postId] = response;
                        
                        // Display content
                        displayContent(response);
                    },
                    error: function() {
                        $spinner.hide();
                        alert('Error loading content. Please try again.');
                    }
                });
            });
            
            // Function to display content in modal
            function displayContent(data) {
                // Set modal content
                $modalTitle.text(data.Title);
                $modalDate.text(formatDate(data.Created_at));
                $modalContent.html(data.Content);
                
                // Set image or default
                if (data.Image && data.Image.trim() !== '') {
                    $modalImage.attr('src', data.Image);
                } else {
                    $modalImage.attr('src', 'img/default-post.jpg');
                }
                
                // Show modal
                $modal.fadeIn(300);
                
                // Prevent body scrolling when modal is open
                $('body').css('overflow', 'hidden');
            }
            
            // Format date function
            function formatDate(dateString) {
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateString).toLocaleDateString('en-US', options);
            }
            
            // Close modal on click
            $('#modalClose').on('click', function() {
                $modal.fadeOut(300);
                $('body').css('overflow', 'auto');
            });
            
            // Close modal when clicking outside content
            $modal.on('click', function(e) {
                if ($(e.target).is($modal)) {
                    $modal.fadeOut(300);
                    $('body').css('overflow', 'auto');
                }
            });
            
            // Close modal with ESC key
            $(document).keydown(function(e) {
                if (e.keyCode === 27 && $modal.is(':visible')) {
                    $modal.fadeOut(300);
                    $('body').css('overflow', 'auto');
                }
            });
        });
    </script>
</body>
</html>