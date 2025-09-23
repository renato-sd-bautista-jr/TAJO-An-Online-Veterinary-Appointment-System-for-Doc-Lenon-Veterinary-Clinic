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
$upload_dir = "post_images/";
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
$page = 'posts';

// Get all post categories for dropdown
$sql = "SELECT DISTINCT Category FROM posts ORDER BY Category";
$result = $conn->query($sql);
$categories = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['Category'];
    }
}

// Handle post form submission for adding or updating
if (isset($_POST['save_post'])) {
    $post_title = $_POST['post_title'];
    $post_content = $_POST['post_content'];
    $category = $_POST['category'];

    // Initialize image_path with NULL for new posts or the current image for existing posts
    $image_path = isset($_POST['current_image']) ? $_POST['current_image'] : NULL;

    // Handle image upload only if a file was actually selected
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['post_image']['type'];

        if (in_array($file_type, $allowed_types)) {
            $filename = time() . '_' . basename($_FILES['post_image']['name']);
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $target_path)) {
                $image_path = $target_path; // Only override if new upload is successful
            } else {
                $image_error = "Failed to upload image. Please check file permissions.";
            }
        } else {
            $image_error = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
        }
    }

    if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
        // Update existing post
        $post_id = $_POST['post_id'];

        // Check if image_path is NULL and there's a current_image value
        if ($image_path === NULL && !isset($_POST['current_image'])) {
            // If we're here, there's no new image and no current image
            $sql = "UPDATE posts SET Title=?, Content=?, Category=?, Updated_at=NOW() WHERE ID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $post_title, $post_content, $category, $post_id);
        } else {
            // Normal update with image
            $sql = "UPDATE posts SET Title=?, Content=?, Category=?, Image=?, Updated_at=NOW() WHERE ID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $post_title, $post_content, $category, $image_path, $post_id);
        }

        if ($stmt->execute()) {
            $message = "Post updated successfully";
        } else {
            $error = "Error updating post: " . $conn->error;
        }
    } else {
        // Add new post
        $sql = "INSERT INTO posts (Title, Content, Category, Image, Created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $post_title, $post_content, $category, $image_path);

        if ($stmt->execute()) {
            $message = "Post added successfully";
        } else {
            $error = "Error adding post: " . $conn->error;
        }
    }
}

// Handle delete post
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $post_id = $_GET['delete'];

    // First get the image path to delete the file
    $sql = "SELECT Image FROM posts WHERE ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['Image'] && file_exists($row['Image'])) {
            unlink($row['Image']); // Delete the image file
        }
    }

    // Then delete the post
    $sql = "DELETE FROM posts WHERE ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        $message = "Post deleted successfully";
    } else {
        $error = "Error deleting post: " . $conn->error;
    }
}

// Get post to edit
$edit_post = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $post_id = $_GET['edit'];
    $sql = "SELECT * FROM posts WHERE ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $edit_post = $result->fetch_assoc();
    }
}

// Get search term for category filtering
$search_category = isset($_GET['search_category']) ? $_GET['search_category'] : '';

// Fetch posts with optional category filter
if (!empty($search_category)) {
    $sql = "SELECT * FROM posts WHERE Category LIKE ? ORDER BY Created_at DESC";
    $stmt = $conn->prepare($sql);
    $search_param = "%" . $search_category . "%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM posts ORDER BY Created_at DESC";
    $result = $conn->query($sql);
}

$posts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc Lenon Veterinary - Post Management</title>
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
        .post-image {
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

        /* Post content preview styles */
        .post-content-preview {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
        <a href="admin.php?page=products">
            <i class="fas fa-box"></i> Products Inventory
        </a>
        <a href="post.php"class="active" >
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
            
            <!-- Posts Management -->
            <div class="row">
                <div class="col-md-4">
                    <!-- Post Form -->
                    <div class="card">
                        <div class="card-header">
                            <?php echo $edit_post ? 'Edit Post' : 'Add New Post'; ?>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <?php if ($edit_post): ?>
                                    <input type="hidden" name="post_id" value="<?php echo $edit_post['ID']; ?>">
                                    <?php if (isset($edit_post['Image']) && $edit_post['Image']): ?>
                                        <input type="hidden" name="current_image" value="<?php echo $edit_post['Image']; ?>">
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="post_title" class="form-label">Post Title</label>
                                    <input type="text" class="form-control" id="post_title" name="post_title" 
                                           value="<?php echo $edit_post ? htmlspecialchars($edit_post['Title']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="category" name="category" list="category-list"
                                           value="<?php echo $edit_post ? htmlspecialchars($edit_post['Category']) : ''; ?>" required>
                                    <datalist id="category-list">
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="post_content" class="form-label">Post Content</label>
                                    <textarea class="form-control" id="post_content" name="post_content" rows="8" required><?php echo $edit_post ? htmlspecialchars($edit_post['Content']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="post_image" class="form-label">Featured Image</label>
                                    <input type="file" class="form-control" id="post_image" name="post_image" accept="image/*">
                                    <div class="form-text">Accepted formats: JPG, PNG, GIF, WEBP</div>
                                    
                                    <?php if ($edit_post && isset($edit_post['Image']) && $edit_post['Image']): ?>
                                        <div class="mt-2">
                                            <p>Current image:</p>
                                            <img src="<?php echo $edit_post['Image']; ?>" alt="Featured Image" class="image-preview">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div id="imagePreviewContainer" class="mt-2 d-none">
                                        <p>New image preview:</p>
                                        <img id="imagePreview" class="image-preview">
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="save_post" class="btn btn-primary">
                                        <i class="fas fa-save"></i> <?php echo $edit_post ? 'Update Post' : 'Publish Post'; ?>
                                    </button>
                                    
                                    <?php if ($edit_post): ?>
                                        <a href="post.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <!-- Post List -->
                    <div class="card">
                        <div class="card-header">
                            Post List
                        </div>
                        <div class="card-body">
                            <!-- Category Search Bar -->
                            <div class="search-container">
                                <form method="get" class="mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Search by category..." 
                                               name="search_category" value="<?php echo htmlspecialchars($search_category); ?>" 
                                               list="search-category-list">
                                        <datalist id="search-category-list">
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <?php if (!empty($search_category)): ?>
                                            <a href="post.php" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Clear
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                                
                                <?php if (!empty($search_category)): ?>
                                    <div class="alert alert-info">
                                        Showing posts with category matching: <strong><?php echo htmlspecialchars($search_category); ?></strong>
                                        (<?php echo count($posts); ?> results found)
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($posts) > 0): ?>
                                            <?php foreach ($posts as $post): ?>
                                                <tr>
                                                    <td><?php echo $post['ID']; ?></td>
                                                    <td>
                                                        <?php if (!empty($post['Image'])): ?>
                                                            <img src="<?php echo $post['Image']; ?>" alt="Featured Image" class="post-image">
                                                        <?php else: ?>
                                                            <div class="text-center text-muted">No image</div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($post['Title']); ?></td>
                                                    <td><?php echo htmlspecialchars($post['Category']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($post['Created_at'])); ?></td>
                                                    <td class="action-buttons">
                                                        <a href="?edit=<?php echo $post['ID']; ?><?php echo !empty($search_category) ? '&search_category=' . urlencode($search_category) : ''; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $post['ID']; ?>, '<?php echo !empty($search_category) ? urlencode($search_category) : ''; ?>')" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" onclick="previewPost(<?php echo $post['ID']; ?>)" class="btn btn-sm btn-info" title="Preview">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No posts found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Post Preview Modal -->
    <div class="modal fade" id="postPreviewModal" tabindex="-1" aria-labelledby="postPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="postPreviewModalLabel">Post Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="postPreviewContent">
                        <!-- Post preview content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Image Preview -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('post_image');
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
            
            // Initialize rich text editor for post content
            if ($('#post_content').length) {
                $('#post_content').summernote({
                    placeholder: 'Write your post content here...',
                    height: 300,
                    toolbar: [] // Empty toolbar array removes all buttons
                });
            }
        });
        
        function confirmDelete(id, searchCategory) {
            if (confirm('Are you sure you want to delete this post?')) {
                let url = `?delete=${id}`;
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
        
        function previewPost(postId) {
            // In a real implementation, this would fetch post details from the server
            // For this example, we'll just show the modal with placeholder content
            // You would typically implement this with an AJAX call to get the post data
            
            // Simple mock preview for demonstration
            $('#postPreviewContent').html('<div class="text-center">Loading post preview...</div>');
            $('#postPreviewModal').modal('show');
            
            // Simulating an AJAX call (in a real implementation, this would be a real request)
            setTimeout(function() {
                // Find the post in the table
                const postTitle = $('tr').filter(function() {
                    return $(this).find('td:first').text() == postId;
                }).find('td:nth-child(3)').text();
                
                const postCategory = $('tr').filter(function() {
                    return $(this).find('td:first').text() == postId;
                }).find('td:nth-child(4)').text();
                
                const postImage = $('tr').filter(function() {
                    return $(this).find('td:first').text() == postId;
                }).find('img').attr('src');
                
                let previewHTML = `
                    <h3>${postTitle}</h3>
                    <div class="badge bg-secondary mb-3">${postCategory}</div>
                `;
                
                if (postImage) {
                    previewHTML += `<div class="mb-3"><img src="${postImage}" alt="Featured Image" style="max-width: 100%;"></div>`;
                }
                
                previewHTML += `
                    <div class="post-content">
                        <p>This is where the full post content would appear. In a real implementation, 
                        this would be fetched from the database. The current code simply shows a preview 
                        with the post title, category, and image if available.</p>
                    </div>
                `;
                
                $('#postPreviewContent').html(previewHTML);
            }, 500);
        }
    </script>
</body>
</html>