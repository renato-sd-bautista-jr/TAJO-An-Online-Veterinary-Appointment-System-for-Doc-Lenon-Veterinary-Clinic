<style> 
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
  display: flex;
  align-items: center;
}

.nav-items-container {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 0;
}

.nav-link {
  font-weight: 500;
  color: #2c3e50;
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: #3498db;
}

.nav-link.active {
  color: #0d6efd;
  font-weight: bold;
  border-bottom: 2px solid #0d6efd;
}

.btn-primary {
  background-color: #4DA6FF;
  border-color: #4DA6FF;
}

.btn-primary:hover {
  background-color: #3a8fd0;
  border-color: #3a8fd0;
}

footer {
  background-color: #e3f2fd;
  color: #2c3e50;
  padding: 15px 0;
  text-align: center;
  margin-top: 0;
}

</style>
<!-- navbar.php -->
<nav class="navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <img src="img/LOGO.png" alt="Logo" width="45" height="40" class="d-inline-block align-text-top">
      DOC LENON VETERINARY
    </a>
    <ul class="nav nav-tabs nav-items-container">
      <li class="nav-item">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pw.php' ? 'active' : ''; ?>" href="pw.php">Services Post</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'Order-Products.php' ? 'active' : ''; ?>" href="Order-Products.php">Products</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'Contact Us.php' ? 'active' : ''; ?>" href="Contact Us.php">Contact Us</a>
      </li>
      <li class="nav-item">
        <button type="button" class="btn btn-primary" onclick="window.location.href='Appointment.php'">
          Book Appointment
        </button>
      </li>
    </ul>
  </div>
</nav>
