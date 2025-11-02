<?php
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "taho"; // Change to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

$today = date('Y-m-d');
$nextWeek = date('Y-m-d', strtotime('+7 days'));

// Count pending appointments within the next 7 days
$sql = "SELECT COUNT(*) AS upcoming_count 
        FROM appointments 
        WHERE status = 'Pending' 
          AND appointment_date BETWEEN ? AND ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $today, $nextWeek);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$upcomingCount = $row['upcoming_count'] ?? 0;

$stmt->close();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
?>
<!-- Sidebar Navigation -->
<style>
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

.sidebar-header {
    padding: 0 15px 20px 15px;
    color: #4DA6FF;
    font-size: 22px;
    position: absolute;
    top: 15px;
    left: 15px;
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

.badge {
  font-size: 0.75rem;
  border-radius: 12px;
  padding: 4px 8px;
}

</style>
<div id="mySidebar" class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-paw"></i> Doc Lenon
    </div>
    <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">&times;</a>
    <a href="admin.php?page=products" class="<?= basename($_SERVER['PHP_SELF'])=='admin.php'?'active':'' ?>">
        <i class="fas fa-box"></i> Products Inventory
    </a>
    <a href="post.php" class="<?= basename($_SERVER['PHP_SELF'])=='post.php'?'active':'' ?>">
        <i class="fas fa-blog"></i> Post Management
    </a>
    <a href="calendar.php" class="<?= basename($_SERVER['PHP_SELF'])=='calendar.php'?'active':'' ?>">
    <i class="fas fa-calendar-alt"></i> Appointment Calendar
    <?php if ($upcomingCount > 0): ?>
        <span class="badge bg-danger ms-2"><?= $upcomingCount ?></span>
    <?php endif; ?>
</a>
    <a href="history1.php" class="<?= basename($_SERVER['PHP_SELF'])=='history1.php'?'active':'' ?>">
        <i class="fas fa-history"></i> Appointment History
    </a>
    <a href="ordermanagement.php" class="<?= basename($_SERVER['PHP_SELF'])=='ordermanagement.php'?'active':'' ?>">
        <i class="fas fa-box"></i> Order Management
    </a>
    <a href="analytics.php" class="<?= basename($_SERVER['PHP_SELF'])=='analytics.php'?'active':'' ?>">
        <i class="fas fa-chart-bar"></i> Analytics
    </a>
    
    <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<script>
function openNav(){
  document.getElementById("mySidebar").style.width="250px";
  document.getElementById("main").style.marginLeft="250px";
}
function closeNav(){
  document.getElementById("mySidebar").style.width="0";
  document.getElementById("main").style.marginLeft="0";
}
</script>

<style>
.sidebar{height:100%;width:0;position:fixed;z-index:1000;top:0;left:0;background:#222;overflow-x:hidden;transition:0.5s;padding-top:60px;box-shadow:2px 0 5px rgba(0,0,0,0.2);}
.sidebar a{padding:12px 15px;text-decoration:none;font-size:18px;color:#fff;display:block;transition:0.3s;border-left:3px solid transparent;}
.sidebar a:hover{background:#333;border-left:3px solid #4DA6FF;}
.sidebar a.active{background:#2c2c2c;border-left:3px solid #4DA6FF;font-weight:bold;}
.sidebar .close-btn{position:absolute;top:10px;right:15px;font-size:30px;}
.sidebar-header{padding:0 15px 20px 15px;color:#4DA6FF;font-size:22px;position:absolute;top:15px;left:15px;}
.badge.bg-danger{background:#dc3545!important;}
</style>
