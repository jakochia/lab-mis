<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missions of Hope International – Namarei Computer Lab</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --mohi-green: #2e7d32;
            --mohi-gold: #ffc107;
            --mohi-dark: #1a2a3a;
            --mohi-light: #f8f9fa;
        }
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
        }
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" fill-opacity="1" d="M0,192L48,197.3C96,203,192,213,288,208C384,203,480,181,576,181.3C672,181,768,203,864,208C960,213,1056,203,1152,186.7C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }
        .hero .container {
            position: relative;
            z-index: 2;
        }
        .hero h1 {
            font-size: 3.2rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        .hero .subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        .btn-custom {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 10px;
        }
        .btn-login {
            background: white;
            color: #2a5298;
            border: 2px solid white;
        }
        .btn-login:hover {
            background: transparent;
            color: white;
            transform: translateY(-2px);
        }
        .btn-register {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        .btn-register:hover {
            background: white;
            color: #2a5298;
            transform: translateY(-2px);
        }
        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .feature-icon {
            font-size: 3rem;
            color: #2a5298;
            margin-bottom: 1rem;
        }
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        /* About Section */
        .about-section {
            background: white;
            padding: 80px 0;
        }
        .about-img {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        /* Footer */
        footer {
            background: #1a2a3a;
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .hero .subtitle { font-size: 1rem; }
            .btn-custom { margin: 10px; }
        }
    </style>
</head>
<body>

<!-- Hero Section (Logo removed) -->
<section class="hero">
    <div class="container text-center">
        <h1>Missions of Hope International</h1>
        <h2 class="h3 mb-3">Namarei Centre</h2>
        <p class="subtitle">Empowering the next generation through technology education.</p>
        <div class="mt-4">
            <a href="login.php" class="btn btn-custom btn-login"><i class="fas fa-sign-in-alt me-2"></i>  Login</a>
            
        
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Modern Computer Lab Facilities</h2>
            <p class="lead text-muted">Empowering students with reliable technology and support</p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-laptop-code"></i></div>
                    <h3>State-of-the-Art Equipment</h3>
                    <p>Access modern computers with high-speed internet for learning, research, and innovation.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                    <h3>Easy Booking System</h3>
                    <p>Reserve a computer in advance to ensure your study time is guaranteed.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <h3>24/7 Support</h3>
                    <p>Our dedicated support team is always ready to assist you with any technical issues.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Namarei Centre -->
<section class="about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://placehold.co/600x400/1e3c72/white?text=Namarei+Centre" alt="Namarei Centre" class="img-fluid about-img">
            </div>
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-3">About Namarei Centre</h2>
                <p class="lead">Missions of Hope International (MOHI) is dedicated to breaking the cycle of poverty through education and holistic development.</p>
                <p>The Namarei Centre computer lab provides students with the tools they need to excel in the digital age. Our mission is to equip young minds with ICT skills that open doors to future opportunities.</p>
                <p>We believe that access to technology is a fundamental right, and we strive to create an environment where every student can thrive.</p>
                <div class="mt-4">
                    <a href="https://mohiafrica.org" target="_blank" class="btn btn-outline-primary btn-custom">Learn More <i class="fas fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white text-center" style="background: linear-gradient(135deg, #1e3c72, #2a5298);">
    <div class="container">
        <h2 class="display-5 fw-bold">Ready to Begin Your Journey?</h2>
        <p class="lead mb-4">Login to access the lab, book computers, and track your progress.</p>
        <a href="login.php" class="btn btn-light btn-lg rounded-pill px-5"><i class="fas fa-sign-in-alt me-2"></i> Login Now</a>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>Missions of Hope International</h5>
                <p>Namarei Centre<br>Nairobi, Kenya</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white-50 text-decoration-none">About Us</a></li>
                    <li><a href="#" class="text-white-50 text-decoration-none">Contact</a></li>
                    <li><a href="#" class="text-white-50 text-decoration-none">Lab Rules</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Connect With Us</h5>
                <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-2x"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-2x"></i></a>
                <a href="#" class="text-white"><i class="fab fa-instagram fa-2x"></i></a>
            </div>
        </div>
        <hr class="bg-white">
        <p class="mb-0">&copy; <?= date('Y') ?> Missions of Hope International – Namarei Centre. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>