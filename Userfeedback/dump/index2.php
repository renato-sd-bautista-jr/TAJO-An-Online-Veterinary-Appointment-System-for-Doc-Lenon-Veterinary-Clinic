<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doc Lenon Veterinary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
      .navbar {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 15px 0;
      }
      
      .navbar-brand {
        font-size: 1.5rem;
        font-weight: bold;
        color: #2c3e50;
      }
      
      .nav-link {
        font-weight: 500;
        color: #2c3e50;
        margin: 0 10px;
        transition: color 0.3s ease;
      }
      
      .nav-link:hover {
        color: #3498db;
      }
      
      .btn-primary {
        background-color: #3498db;
        border: none;
        padding: 8px 20px;
        border-radius: 20px;
        transition: all 0.3s ease;
      }
      
      .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      }
      
      .carousel-item img {
        height: 600px;
        object-fit: cover;
      }
      
      .carousel-caption {
        background-color: rgba(0, 0, 0, 0.7);
        border-radius: 10px;
        padding: 20px;
        bottom: 50px;
        max-width: 600px;
        margin: 0 auto;
        left: 50%;
        transform: translateX(-50%);
      }
      
      .carousel-caption h5 {
        font-size: 2rem;
        font-weight: bold;
        color: white;
        margin-bottom: 10px;
      }
      
      .carousel-caption p {
        font-size: 1.1rem;
        color: white;
      }
      
      .carousel-control-prev, .carousel-control-next {
        width: 5%;
        opacity: 1;
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
        background-color: #2c3e50;
        color: white;
        padding: 20px 0;
        text-align: center;
        margin-top: 30px;
      }
      
      .dropdown-menu {
        border: none;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-radius: 10px;
      }
      
      .dropdown-item {
        padding: 10px 20px;
        transition: all 0.3s ease;
      }
      
      .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #3498db;
      }
    </style>
  </head>
  <body>
    <nav class="navbar" style="background-color: #e3f2fd;">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="img/LOGO.png" alt="Logo" width="45" height="40" class="d-inline-block align-text-top me-2">
          DOC LENON VETERINARY
        </a>
        <ul class="nav nav-tabs">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Services</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Pet Wellness</a></li>
              <li><a class="dropdown-item" href="#">Consultation</a></li>
              <li><a class="dropdown-item" href="#">Vaccination</a></li>
              <li><a class="dropdown-item" href="#">Deworming</a></li>
              <li><a class="dropdown-item" href="#">Laboratory</a></li>
              <li><a class="dropdown-item" href="#">Surgery</a></li>
              <li><a class="dropdown-item" href="#">Confinement</a></li>
              <li><a class="dropdown-item" href="#">Grooming</a></li>
              <li><a class="dropdown-item" href="#">Pet Boarding</a></li>
              <li><a class="dropdown-item" href="#">Supplies</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Products</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">User Feedback</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Contact Us</a>
          </li>
          <li class="nav-item ms-2">
            <button type="button" class="btn btn-primary">Book Appointment</button>
          </li>
        </ul>
      </div>
    </nav>

    <div id="carouselExampleFade" class="carousel slide carousel-fade">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="img/s1.jpg" class="d-block w-100 border rounded" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5>Welcome to Doc Lenon Veterinary</h5>
            <p>Providing expert, compassionate care for your beloved pets.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="img/s2.jpg" class="d-block w-100 border rounded" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5>Professional Pet Care Services</h5>
            <p>Complete veterinary solutions for all your pet's needs.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="img/3.jpg" class="d-block w-100 border rounded" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5>Modern Facilities & Equipment</h5>
            <p>State-of-the-art technology for the best pet healthcare.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="img/3.jpg" class="d-block w-100 border rounded" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5>Modern Facilities & Equipment</h5>
            <p>State-of-the-art technology for the best pet healthcare.</p>
          </div>
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

    <footer class="mt-4">
      <div class="container">
        <p class="mb-0">&copy; ALL RIGHTS RESERVED 2025 SE FINAL - TAHO</p>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>