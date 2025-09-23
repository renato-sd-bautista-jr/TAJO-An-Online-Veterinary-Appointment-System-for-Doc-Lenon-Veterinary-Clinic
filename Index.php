<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TAHO</title>
    <link rel="icon" href="img/LOGO.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
      }
      
      .nav-items-container {
        display: flex;
        align-items: center;
        gap: 10px;
      }
      
      .nav-link {
        font-weight: 500;
        color: #2c3e50;
        transition: color 0.3s ease;
      }
      
      .nav-link:hover {
        color: #3498db;
      }
      
      .carousel-control-prev, .carousel-control-next {
        width: 5%;
        opacity: 5;
      }
      
      .carousel-control-prev-icon, .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.7);
        padding: 30px;
        border-radius: 50%;
        transition: all 0.3s ease;
      }
      
      .carousel-control-prev-icon:hover, .carousel-control-next-icon:hover {
        background-color: rgba(0, 0, 0, 0.9);
      }
      
      footer {
        background-color: #e3f2fd;
        color: #2c3e50;
        padding: 15px 0;
        text-align: center;
        margin-top: 0px;
      }
      
      </style>
  </head>
  <body>
  <nav class="navbar" style="background-color: #e3f2fd;">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
        <img src="img/LOGO.png" alt="Logo" width="45" height="40" class="d-inline-block align-text-top">
        DOC LENON VETERINARY
        </a>
        <ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="#">Home</a>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Services</a>
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
      <!--<li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="#">Separated link</a></li>-->
    </ul>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="products.php">Products</a>
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
    <div id="carouselExampleFade" class="carousel slide carousel-fade">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="img/HOME.png" class="d-block w-100 object-fit-cover border rounded" alt="...">
      
    </div>
    <div class="carousel-item">
      <img src="img/Desktop - 1.png" class="d-block w-100 object-fit-cover border rounded" alt="...">
      
    </div>
    <div class="carousel-item">
      <img src="img/Desktop - 5.png" class="d-block w-100 object-fit-cover border rounded" alt="...">
      
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
  <footer>
    <p>&copy; ALL RIGHTS RESERVED 2025 SE FINAL - TAHO</p>
</footer>

</html>