<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAB'S RESTO — Delicious Food Delivered Fresh</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/BabsLogoFinal.jpg') }}">

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
            padding: 3rem 0 2rem;
        }
        .hero .hero-logo{width:120px;height:120px;object-fit:contain;}
        .card-product{transition: transform .18s ease, box-shadow .18s ease;border:0}
        .card-product:hover{transform: translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.12)}
        .price{font-weight:700;color:var(--primary-red)}
        .feature-icon{font-size:1.5rem;color:var(--primary-red)}
        .footer{background:#0b0b0b;color:#cfcfcf;padding:2.5rem 0}
        .footer .text-muted{color:#9a9a9a !important}
        a.social{color:#cfcfcf;margin-right:.5rem}
        @media (max-width:576px){
            .hero{padding:2rem 1rem}
            .hero .hero-logo{width:90px;height:90px}
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <img src="{{ asset('images/BabsLogoFinal.jpg') }}" alt="BAB'S RESTO" style="width:44px;height:44px;object-fit:contain;">
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
        <img src="{{ asset('images/BabsLogoFinal.jpg') }}" alt="BAB'S RESTO" class="hero-logo mb-2">
        <h1 class="mt-2 mb-1 fw-bold h3">Delicious Food Delivered Fresh</h1>
        <p class="lead text-muted mb-2">Order your favorite meals online and enjoy fast service.</p>
        <div class="d-flex justify-content-center gap-2 mt-2">
            <a href="#menu" class="btn btn-primary-custom shadow-sm">Order Now <i class="fas fa-shopping-cart ms-2"></i></a>
            <a href="#menu" class="btn btn-outline-custom">View Menu <i class="fas fa-utensils ms-2"></i></a>
        </div>
    </div>
</header>

<!-- Featured Products -->
<section id="menu" class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Featured Meals</h2>
            <a href="{{ route('login') }}" class="text-decoration-none text-muted">View full menu <i class="fas fa-angle-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            @forelse ($featuredMeals as $meal)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card card-product h-100 shadow-sm">
                        <img src="{{ $meal->image_url }}" class="card-img-top" alt="{{ $meal->menu_name }}" style="height:200px;object-fit:cover">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">{{ $meal->menu_name }}</h5>
                            <p class="text-muted small mb-2">{{ $meal->description }}</p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div class="price">₱{{ number_format($meal->price, 2) }}</div>
                                <a href="{{ route('login') }}" class="btn btn-outline-custom btn-sm">Order <i class="fas fa-shopping-bag ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">No menu items available yet.</div>
            @endforelse
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

<!-- Footer -->
<footer id="contact" class="footer mt-5">
    <div class="container">
        <div class="row gy-4">
            <div class="col-md-3">
                <div class="fw-bold text-white mb-1">BAB'S RESTO</div>
                <small class="text-muted">&copy; {{ date('Y') }} BAB'S RESTO. All rights reserved.</small>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1"><i class="fas fa-phone-alt me-2"></i>Contact Number</div>
                <div class="text-white">0910 741 2539</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1"><i class="fas fa-envelope me-2"></i>Email</div>
                <div class="text-white">babsrestofficial@gmail.com</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1"><i class="fab fa-facebook-messenger me-2"></i>Messenger</div>
                <div class="text-white">BABS RESTO II</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1"><i class="fas fa-clock me-2"></i>Business Hours</div>
                <div class="text-white">11:00 AM — 9:00 PM</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted mb-1"><i class="fas fa-map-marker-alt me-2"></i>Address</div>
                <div class="text-white">Aguirre Subd, Purok Roamsciville, Agan Rd., Brgy. Sta. Cruz, Koronadal, Philippines, 9506</div>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <small class="text-muted mb-2 mb-md-0">Taste the Love</small>
            <div>
                <a href="#" class="social"><i class="fab fa-facebook fa-lg"></i></a>
                <a href="#" class="social"><i class="fab fa-instagram fa-lg"></i></a>
                <a href="#" class="social"><i class="fab fa-twitter fa-lg"></i></a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
