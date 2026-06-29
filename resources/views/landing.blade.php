<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAB'S RESTO — Delicious Food Delivered Fresh</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root{
            --primary-red: #E30613;
            --black: #000000;
            --white: #ffffff;
            --muted: #6c757d;
            --max-width: 1200px;
        }
        html,body{height:100%;font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;}
        .brand{font-weight:700;color:var(--primary-red);letter-spacing:0.6px}
        .navbar{background:var(--white);}
        .nav-link{color:var(--black) !important;font-weight:600}
        .btn-primary-custom{background:var(--primary-red);border:none}
        .btn-outline-custom{color:var(--primary-red);border-color:var(--primary-red)}
        .hero{
            background: linear-gradient(180deg, rgba(227,6,19,0.06), rgba(0,0,0,0.02));
            padding: 6rem 0 4rem;
        }
        .hero .logo-large{font-size:3.25rem; font-weight:800; color:var(--primary-red);}
        .card-product{transition: transform .18s ease, box-shadow .18s ease;border:0}
        .card-product:hover{transform: translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.12)}
        .price{font-weight:700;color:var(--primary-red)}
        .feature-icon{font-size:1.5rem;color:var(--primary-red)}
        .footer{background:#0b0b0b;color:#cfcfcf;padding:2.5rem 0}
        a.social{color:#cfcfcf;margin-right:.5rem}
        .contact-item{min-height:64px}
        @media (max-width:576px){
            .hero{padding:4rem 1rem}
            .hero .logo-large{font-size:2.25rem}
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                <span class="text-white fw-bold">BR</span>
            </div>
            <div>
                <div class="brand">BAB'S RESTO</div>
                <small class="text-muted" style="font-size:.7rem">Taste the Love</small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#menu">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                <li class="nav-item ms-3"><a class="btn btn-outline-custom btn-sm" href="{{ route('login') }}">Login</a></li>
                <li class="nav-item ms-2"><a class="btn btn-primary-custom btn-sm text-white" href="{{ route('register') }}">Register</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero -->
<header id="home" class="hero">
    <div class="container text-center">
        <div class="logo-large">BAB'S RESTO</div>
        <h1 class="mt-3 mb-2 fw-bold">Delicious Food Delivered Fresh</h1>
        <p class="lead text-muted">Order your favorite meals online and enjoy fast service.</p>
        <div class="d-flex justify-content-center gap-2 mt-4">
            <a href="#menu" class="btn btn-primary-custom btn-lg shadow-sm">Order Now <i class="fas fa-shopping-cart ms-2"></i></a>
            <a href="#menu" class="btn btn-outline-custom btn-lg">View Menu <i class="fas fa-utensils ms-2"></i></a>
        </div>
    </div>
</header>

<!-- Featured Products -->
<section id="menu" class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Featured Meals</h2>
            <a href="#" class="text-decoration-none text-muted">View full menu <i class="fas fa-angle-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <!-- Product Card -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card card-product h-100 shadow-sm">
                    <img src="https://via.placeholder.com/600x400?text=Grilled+Chicken" class="card-img-top" alt="Grilled Chicken">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1">Grilled Chicken Plate</h5>
                        <p class="text-muted small mb-2">Comes with rice and side salad</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div class="price">₱199</div>
                            <a href="#" class="btn btn-outline-custom btn-sm">Order <i class="fas fa-shopping-bag ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Repeat sample cards -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card card-product h-100 shadow-sm">
                    <img src="https://via.placeholder.com/600x400?text=Beef+Steak" class="card-img-top" alt="Beef Steak">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1">Sizzling Beef Steak</h5>
                        <p class="text-muted small mb-2">Served hot with garlic butter</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div class="price">₱289</div>
                            <a href="#" class="btn btn-outline-custom btn-sm">Order <i class="fas fa-shopping-bag ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card card-product h-100 shadow-sm">
                    <img src="https://via.placeholder.com/600x400?text=Pasta" class="card-img-top" alt="Creamy Pasta">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1">Creamy Garlic Pasta</h5>
                        <p class="text-muted small mb-2">Rich and creamy with herbs</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div class="price">₱169</div>
                            <a href="#" class="btn btn-outline-custom btn-sm">Order <i class="fas fa-shopping-bag ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card card-product h-100 shadow-sm">
                    <img src="https://via.placeholder.com/600x400?text=Salad" class="card-img-top" alt="Fresh Salad">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1">Fresh Garden Salad</h5>
                        <p class="text-muted small mb-2">Crisp vegetables with house dressing</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div class="price">₱129</div>
                            <a href="#" class="btn btn-outline-custom btn-sm">Order <i class="fas fa-shopping-bag ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- About Us -->
<section id="about" class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <h3>About BAB'S RESTO</h3>
                <p class="text-muted">BAB'S RESTO is a family-owned restaurant focused on serving delicious, freshly prepared meals using the finest ingredients. Our menu blends classic comfort dishes with modern flavors to offer something for every palate.</p>
                <p class="mb-1"><strong>Mission:</strong> To deliver high-quality, tasty meals quickly, delighting every customer at every order.</p>
                <p class="mb-0"><strong>Vision:</strong> To be the community's favorite choice for comfort food and convenient online ordering.</p>
            </div>
            <div class="col-md-6">
                <img src="https://via.placeholder.com/800x500?text=Restaurant+Interior" alt="Restaurant" class="img-fluid rounded shadow-sm">
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5">
    <div class="container">
        <h4 class="mb-4">Why Choose Us</h4>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded text-center h-100">
                    <div class="feature-icon mb-2"><i class="fas fa-bolt"></i></div>
                    <h6 class="mb-0">Fast Service</h6>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded text-center h-100">
                    <div class="feature-icon mb-2"><i class="fas fa-leaf"></i></div>
                    <h6 class="mb-0">Fresh Ingredients</h6>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded text-center h-100">
                    <div class="feature-icon mb-2"><i class="fas fa-tags"></i></div>
                    <h6 class="mb-0">Affordable Prices</h6>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded text-center h-100">
                    <div class="feature-icon mb-2"><i class="fas fa-star"></i></div>
                    <h6 class="mb-0">Quality Food</h6>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact -->
<section id="contact" class="py-5 bg-light">
    <div class="container">
        <h4 class="mb-4">Contact Us</h4>
        <div class="row gy-3">
            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm contact-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-4 text-danger"><i class="fas fa-phone-alt"></i></div>
                        <div>
                            <div class="small text-muted">Telephone</div>
                            <div class="fw-bold">(02) 1234-5678</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm contact-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-4 text-danger"><i class="fas fa-mobile-alt"></i></div>
                        <div>
                            <div class="small text-muted">Mobile</div>
                            <div class="fw-bold">+63 912 345 6789</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm contact-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-4 text-danger"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <div class="small text-muted">Address</div>
                            <div class="fw-bold">123 Mabuhay St., Quezon City</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <div class="p-3 bg-white rounded shadow-sm">
                    <div class="small text-muted">Business Hours</div>
                    <div class="fw-bold">Mon - Sun: 9:00 AM — 9:00 PM</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer mt-5">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="mb-3 mb-md-0">
            <div class="fw-bold">BAB'S RESTO</div>
            <small class="text-muted">&copy; {{ date('Y') }} BAB'S RESTO. All rights reserved.</small>
        </div>
        <div>
            <a href="#" class="social"><i class="fab fa-facebook fa-lg"></i></a>
            <a href="#" class="social"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="#" class="social"><i class="fab fa-twitter fa-lg"></i></a>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
