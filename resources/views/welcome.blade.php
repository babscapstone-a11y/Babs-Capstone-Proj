<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            <!doctype html>
            <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
            <head>
                <meta charset="utf-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <title>{{ config('app.name', "BAB'S RESTO") }} — Order Smarter. Serve Faster.</title>

                <!-- Fonts + Icons -->
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

                <!-- Bootstrap 5 -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

                <!-- AOS -->
                <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

                <style>
                    :root{
                        --brand-red: #E30613;
                        --brand-black: #000000;
                        --accent: #F8B803;
                        --glass: rgba(255,255,255,0.7);
                        --glass-strong: rgba(255,255,255,0.85);
                        --shadow: 0 10px 30px rgba(16,24,40,0.08);
                        --radius: 14px;
                        --page-padding: 1rem;
                    }
                    html,body{height:100%;font-family:'Poppins',system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial; background:linear-gradient(180deg,#ffffff 0%, #fffbf9 100%);color:#0b0b0b}
                    .navbar-backdrop{backdrop-filter: blur(6px); background: linear-gradient(90deg, rgba(255,255,255,0.45), rgba(255,255,255,0.2)); border-bottom:1px solid rgba(0,0,0,0.04)}
                    .brand-logo{font-weight:700;color:var(--brand-red);letter-spacing:0.6px}
                    .hero{min-height:72vh;padding:4rem 1rem;display:flex;align-items:center}
                    .hero-card{background:linear-gradient(135deg, rgba(227,6,19,0.03), rgba(248,184,3,0.03));border-radius:20px;padding:2rem;box-shadow:var(--shadow)}
                    .glass-card{background:linear-gradient(180deg, rgba(255,255,255,0.8), rgba(255,255,255,0.65));backdrop-filter: blur(8px);border-radius:16px;padding:1.25rem;box-shadow:0 8px 30px rgba(17,24,39,0.06);border:1px solid rgba(255,255,255,0.6)}
                    .btn-primary-custom{background:var(--brand-red);border:none;padding:0.9rem 1.3rem;border-radius:12px;font-weight:600}
                    .btn-outline-custom{border:1px solid rgba(11,11,11,0.08);background:transparent;padding:0.8rem 1.2rem;border-radius:12px}
                    .feature-card{border-radius:12px;padding:1rem;background:linear-gradient(180deg,#fff,#fff);box-shadow:var(--shadow);border:1px solid rgba(11,11,11,0.03)}
                    .menu-card{border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.06);transition:transform .28s ease, box-shadow .28s ease}
                    .menu-card:hover{transform:translateY(-8px);box-shadow:0 18px 40px rgba(0,0,0,0.12)}
                    .floating-shape{position:absolute;border-radius:50%;filter:blur(36px);opacity:.55;mix-blend-mode:screen}
                    .stat{font-weight:700;font-size:1.75rem}
                    .rating {color:#FFB703}
                    .cta-banner{border-radius:18px;padding:2rem;background:linear-gradient(90deg, rgba(227,6,19,0.06), rgba(248,184,3,0.04));display:flex;align-items:center;justify-content:space-between}
                    footer{padding:2.5rem 1rem;background:#0b0b0b;color:#fff}
                    .testimonial{background:linear-gradient(180deg,rgba(255,255,255,0.92),#fff);padding:1rem;border-radius:12px;box-shadow:var(--shadow)}

                    /* Responsive tweaks */
                    @media (max-width: 767px){
                        .hero{padding:3rem 1rem}
                        .stat{font-size:1.3rem}
                    }
                </style>

            </head>
            <body>
                <!-- NAV -->
                <nav class="navbar navbar-expand-lg navbar-light fixed-top navbar-backdrop">
                    <div class="container">
                        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                            <span class="brand-logo">BAB'S RESTO</span>
                            <small class="text-muted" style="font-size:0.8rem">Platform</small>
                        </a>

                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navMenu">
                            <ul class="navbar-nav ms-auto align-items-lg-center">
                                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                                <li class="nav-item"><a class="nav-link" href="#menu">Menu</a></li>
                                <li class="nav-item"><a class="nav-link" href="#why">About</a></li>
                                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                                @guest
                                    <li class="nav-item ms-3"><a href="{{ route('login') }}" class="btn btn-outline-custom">Login</a></li>
                                    @if (Route::has('register'))
                                        <li class="nav-item ms-2"><a href="{{ route('register') }}" class="btn btn-primary-custom text-white">Register</a></li>
                                    @endif
                                @else
                                    <li class="nav-item ms-3"><a href="{{ url('/dashboard') }}" class="btn btn-outline-custom">Dashboard</a></li>
                                @endguest
                            </ul>
                        </div>
                    </div>
                </nav>

                <main>
                    <!-- HERO -->
                    <section id="home" class="hero">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-6" data-aos="fade-up">
                                    <div class="hero-card">
                                        <h1 class="display-5 fw-bold">Order Smarter. Serve Faster.</h1>
                                        <p class="lead text-muted">Online Ordering, POS, and Inventory Management in One Platform.</p>

                                        <div class="d-flex gap-3 mt-4">
                                            <a href="#menu" class="btn btn-primary-custom btn-lg">Order Now <i class="fas fa-arrow-right ms-2"></i></a>
                                            <a href="#menu" class="btn btn-outline-custom btn-lg">Explore Menu</a>
                                        </div>

                                        <div class="d-flex gap-3 mt-4 flex-wrap">
                                            <div class="glass-card d-flex align-items-center gap-3">
                                                <div class="avatar rounded-circle bg-white p-2" style="width:56px;height:56px;display:flex;align-items:center;justify-content:center">
                                                    <i class="fas fa-utensils fa-lg" style="color:var(--brand-red)"></i>
                                                </div>
                                                <div>
                                                    <div class="small text-muted">Trusted by</div>
                                                    <div class="fw-semibold">2,400+ restaurants</div>
                                                </div>
                                            </div>
                                            <div class="glass-card d-flex align-items-center gap-3">
                                                <div class="avatar rounded-circle bg-white p-2" style="width:56px;height:56px;display:flex;align-items:center;justify-content:center">
                                                    <i class="fas fa-shield-alt fa-lg" style="color:var(--accent)"></i>
                                                </div>
                                                <div>
                                                    <div class="small text-muted">Secure</div>
                                                    <div class="fw-semibold">PCI-ready payments</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 position-relative d-none d-lg-block" data-aos="zoom-in">
                                    <!-- Floating shapes -->
                                    <div class="floating-shape" style="width:220px;height:220px;right:-60px;top:-40px;background:linear-gradient(135deg,#E30613,#F8B803);opacity:.12"></div>
                                    <div class="floating-shape" style="width:140px;height:140px;right:20px;bottom:20px;background:linear-gradient(135deg,#F8B803,#E30613);opacity:.08"></div>

                                    <!-- Mockup / Illustration placeholder -->
                                    <div class="card menu-card p-4" style="border-radius:22px;">
                                        <div style="display:flex;gap:1rem;align-items:center">
                                            <div style="width:140px;border-radius:14px;overflow:hidden;flex-shrink:0">
                                                <img src="https://images.unsplash.com/photo-1604908177540-9d8b3a32b1a6?q=80&w=640&auto=format&fit=crop&ixlib=rb-4.0.3&s=placeholder" alt="food mockup" style="width:100%;height:100%;object-fit:cover">
                                            </div>
                                            <div style="flex:1">
                                                <h5 class="mb-1">Signature Burger</h5>
                                                <div class="d-flex align-items-center gap-2 small text-muted">
                                                    <div class="rating"><i class="fas fa-star"></i> 4.9</div>
                                                    <div>•</div>
                                                    <div>₱199</div>
                                                </div>
                                                <div class="mt-3">
                                                    <button class="btn btn-sm btn-primary-custom">Quick Add</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- LIVE STATS -->
                    <section class="py-5">
                        <div class="container" data-aos="fade-up">
                            <div class="row text-center gy-3">
                                <div class="col-6 col-md-3">
                                    <div class="feature-card">
                                        <div class="stat" data-counter data-target="12845">0</div>
                                        <div class="text-muted">Total Orders</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="feature-card">
                                        <div class="stat" data-counter data-target="3540">0</div>
                                        <div class="text-muted">Active Customers</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="feature-card">
                                        <div class="stat" data-counter data-target="1200">0</div>
                                        <div class="text-muted">Products Available</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="feature-card">
                                        <div class="stat" data-counter data-target="98">0</div>
                                        <div class="text-muted">Customer Satisfaction (%)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- FEATURED MENU -->
                    <section id="menu" class="py-5 bg-white">
                        <div class="container" data-aos="fade-up">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="mb-0">Featured Menu</h3>
                                <a href="#menu" class="small text-muted">View full menu <i class="fas fa-chevron-right ms-1"></i></a>
                            </div>

                            <div class="row g-4">
                                @php
                                    $items = [
                                        ['name'=>'Signature Burger','price'=>'199','rating'=>'4.9','img'=>'https://images.unsplash.com/photo-1604908177540-9d8b3a32b1a6?q=80&w=800&auto=format&fit=crop&ixlib=rb-4.0.3&s=placeholder'],
                                        ['name'=>'Spicy Ramen','price'=>'249','rating'=>'4.8','img'=>'https://images.unsplash.com/photo-1604908177600-8d9f3a1e1b8a?q=80&w=800&auto=format&fit=crop&ixlib=rb-4.0.3&s=placeholder'],
                                        ['name'=>'Sizzling Sisig','price'=>'179','rating'=>'4.7','img'=>'https://images.unsplash.com/photo-1617191511467-8f63a6e1b2d9?q=80&w=800&auto=format&fit=crop&ixlib=rb-4.0.3&s=placeholder']
                                    ];
                                @endphp

                                @foreach($items as $it)
                                    <div class="col-12 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="50">
                                        <div class="menu-card p-3">
                                            <div class="row g-0 align-items-center">
                                                <div class="col-4">
                                                    <img src="{{ $it['img'] }}" alt="{{ $it['name'] }}" style="width:100%;height:100%;object-fit:cover;border-radius:10px">
                                                </div>
                                                <div class="col-8 ps-3">
                                                    <h5 class="mb-1">{{ $it['name'] }}</h5>
                                                    <div class="small text-muted">₱{{ $it['price'] }} • <span class="rating"><i class="fas fa-star"></i> {{ $it['rating'] }}</span></div>
                                                    <div class="mt-3 d-flex gap-2">
                                                        <button class="btn btn-sm btn-outline-custom">Details</button>
                                                        <button class="btn btn-sm btn-primary-custom">Quick Add</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <!-- WHY CHOOSE -->
                    <section id="why" class="py-5">
                        <div class="container" data-aos="fade-up">
                            <h3 class="mb-4">Why Choose BAB'S RESTO</h3>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="feature-card d-flex gap-3 align-items-start">
                                        <div class="bg-white p-3 rounded" style="width:56px;height:56px;display:flex;align-items:center;justify-content:center"><i class="fas fa-bolt" style="color:var(--brand-red)"></i></div>
                                        <div>
                                            <h6>Fast Ordering</h6>
                                            <p class="small text-muted mb-0">Minimal steps from order to kitchen.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-card d-flex gap-3 align-items-start">
                                        <div class="bg-white p-3 rounded" style="width:56px;height:56px;display:flex;align-items:center;justify-content:center"><i class="fas fa-boxes" style="color:var(--accent)"></i></div>
                                        <div>
                                            <h6>Real-Time Inventory</h6>
                                            <p class="small text-muted mb-0">Sync stock and avoid outages.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-card d-flex gap-3 align-items-start">
                                        <div class="bg-white p-3 rounded" style="width:56px;height:56px;display:flex;align-items:center;justify-content:center"><i class="fas fa-shield-alt" style="color:#2d9cdb"></i></div>
                                        <div>
                                            <h6>Secure Transactions</h6>
                                            <p class="small text-muted mb-0">Encrypted payments and user controls.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SYSTEM FEATURES -->
                    <section class="py-5 bg-white">
                        <div class="container" data-aos="fade-up">
                            <h3 class="mb-4">Platform Features</h3>
                            <div class="row g-3">
                                @php
                                    $features = ['Online Ordering','POS System','Inventory Management','Sales Reports','User Management','Dashboard Analytics'];
                                @endphp
                                @foreach($features as $f)
                                    <div class="col-6 col-md-4">
                                        <div class="feature-card text-center p-3">
                                            <div class="mb-2"><i class="fas fa-check-circle fa-2x" style="color:var(--brand-red)"></i></div>
                                            <div class="fw-semibold">{{ $f }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <!-- TESTIMONIALS -->
                    <section class="py-5">
                        <div class="container" data-aos="fade-up">
                            <h3 class="mb-4">What Customers Say</h3>
                            <div id="testimonials" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <div class="testimonial">
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <div class="rounded-circle bg-light" style="width:56px;height:56px"></div>
                                                <div>
                                                    <div class="fw-semibold">Jenny R.</div>
                                                    <div class="small text-muted">Restaurant Owner</div>
                                                </div>
                                            </div>
                                            <p class="mb-0">"BAB'S RESTO streamlined our kitchen operations — orders fly straight to the POS and inventory updates automatically."</p>
                                        </div>
                                    </div>
                                    <div class="carousel-item">
                                        <div class="testimonial">
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <div class="rounded-circle bg-light" style="width:56px;height:56px"></div>
                                                <div>
                                                    <div class="fw-semibold">Miguel D.</div>
                                                    <div class="small text-muted">Customer</div>
                                                </div>
                                            </div>
                                            <p class="mb-0">"Fast checkout and great UX. I love ordering from my local spots using the app."</p>
                                        </div>
                                    </div>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#testimonials" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                                <button class="carousel-control-next" type="button" data-bs-target="#testimonials" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                            </div>
                        </div>
                    </section>

                    <!-- CTA BANNER -->
                    <section class="py-5">
                        <div class="container" data-aos="zoom-in">
                            <div class="cta-banner">
                                <div>
                                    <h4 class="mb-1">Ready to Order?</h4>
                                    <p class="mb-0 text-muted">Get started with BAB'S RESTO today and grow your business.</p>
                                </div>
                                <div>
                                    <a href="#menu" class="btn btn-primary-custom btn-lg">Order Now</a>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>

                <!-- FOOTER -->
                <footer>
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h5 class="text-white">BAB'S RESTO</h5>
                                <p class="small text-muted">Web-based Online Ordering, POS, and Inventory Management.</p>
                            </div>
                            <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-3">
                                <a href="#" class="text-white small">Privacy</a>
                                <a href="#" class="text-white small">Terms</a>
                                <div class="ms-3">
                                    <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                                    <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="text-center text-muted small mt-3">© {{ date('Y') }} BAB'S RESTO · All rights reserved</div>
                    </div>
                </footer>

                <!-- Scripts -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
                <script>
                    // Initialize AOS
                    document.addEventListener('DOMContentLoaded', function(){
                        AOS.init({duration:700, once:true, anchorPlacement:'top-bottom'});

                        // Animated counters
                        const counters = document.querySelectorAll('[data-counter]');
                        const options = {threshold:0.6};
                        const runCounter = (el, target) => {
                            let start = 0; const dur = 1500; const step = Math.ceil(target / (dur/16));
                            const id = setInterval(()=>{ start += step; if(start >= target){ el.textContent = target; clearInterval(id);} else el.textContent = start; },16);
                        };
                        const observer = new IntersectionObserver((entries, obs)=>{
                            entries.forEach(entry=>{
                                if(entry.isIntersecting){ const el = entry.target; const target = parseInt(el.getAttribute('data-target')); runCounter(el,target); obs.unobserve(el); }
                            });
                        },options);
                        counters.forEach(c=>observer.observe(c));
                    });

                    // Smooth scroll for internal links
                    document.querySelectorAll('a[href^="#"]').forEach(a=>{
                        a.addEventListener('click', function(e){
                            const href = this.getAttribute('href');
                            if(href.length>1){ e.preventDefault(); document.querySelector(href).scrollIntoView({behavior:'smooth', block:'start'}); }
                        });
                    });
                </script>
            </body>
            </html>
                            <path d="M52.5357 167.6H27.3357V105.2H120.336V400.2H52.5357V167.6Z" fill="currentColor"/>
                            <path d="M260.6 400.8C229.8 400.8 204.6 392.4 185 375.6C165.8 358.8 156.2 337 156.2 310.2H226.4C226.4 318.2 229.4 324.8 235.4 330C241.4 335.2 249.4 337.8 259.4 337.8C269 337.8 276.8 335 282.8 329.4C289.2 323.8 292.4 316.6 292.4 307.8C292.4 299.8 289.6 293.2 284 288C278.4 282.8 271.2 280.2 262.4 280.2H225.2V218.4H262.4C269.2 218.4 275 216 279.8 211.2C284.6 206.4 287 200.4 287 193.2C287 184.8 284.4 178.2 279.2 173.4C274 168.6 267.4 166.2 259.4 166.2C252.2 166.2 246 168.4 240.8 172.8C236 177.2 233.6 182.8 233.6 189.6H167C167 164.8 175.8 144.6 193.4 129C211 113 233.6 105 261.2 105C288.8 105 311.2 112.2 328.4 126.6C346 141 354.8 160 354.8 183.6C354.8 200.8 350.2 214.8 341 225.6C331.8 236 320 243.2 305.6 247.2C322.8 252 336.4 260.2 346.4 271.8C356.8 283.4 362 298 362 315.6C362 340.4 352.6 360.8 333.8 376.8C315 392.8 290.6 400.8 260.6 400.8Z" stroke="var(--stroke-color)" stroke-width="2.4" mask="url(#path-2-mask)"/>
                            <path d="M52.5357 167.6H27.3357V105.2H120.336V400.2H52.5357V167.6Z" stroke="var(--stroke-color)" stroke-width="2.4" mask="url(#path-2-mask)"/>
                        </g>

                        <g class="mix-blend-color dark:mix-blend-hard-light transition-all delay-400 opacity-100 duration-750 starting:opacity-0 motion-safe:starting:-translate-x-[51px] text-[#F8B803] dark:text-[#391800]">
                            <mask id="path-3-mask" maskUnits="userSpaceOnUse" x="51" y="103" width="338" height="299" fill="black">
                                <rect fill="white" x="51" y="103" width="338" height="299"/>
                                <path d="M286.264 400.8C255.464 400.8 230.264 392.4 210.664 375.6C191.464 358.8 181.864 337 181.864 310.2H252.064C252.064 318.2 255.064 324.8 261.064 330C267.064 335.2 275.064 337.8 285.064 337.8C294.664 337.8 302.464 335 308.464 329.4C314.864 323.8 318.064 316.6 318.064 307.8C318.064 299.8 315.264 293.2 309.664 288C304.064 282.8 296.864 280.2 288.064 280.2H250.864V218.4H288.064C294.864 218.4 300.664 216 305.464 211.2C310.264 206.4 312.664 200.4 312.664 193.2C312.664 184.8 310.064 178.2 304.864 173.4C299.664 168.6 293.064 166.2 285.064 166.2C277.864 166.2 271.664 168.4 266.464 172.8C261.664 177.2 259.264 182.8 259.264 189.6H192.664C192.664 164.8 201.464 144.6 219.064 129C236.664 113 259.264 105 286.864 105C314.464 105 336.864 112.2 354.064 126.6C371.664 141 380.464 160 380.464 183.6C380.464 200.8 375.864 214.8 366.664 225.6C357.464 236 345.664 243.2 331.264 247.2C348.464 252 362.064 260.2 372.064 271.8C382.464 283.4 387.664 298 387.664 315.6C387.664 340.4 378.264 360.8 359.464 376.8C340.664 392.8 316.264 400.8 286.264 400.8Z"/>
                                <path d="M78.2 167.6H53V105.2H146V400.2H78.2V167.6Z"/>
                            </mask>
                            <path d="M286.264 400.8C255.464 400.8 230.264 392.4 210.664 375.6C191.464 358.8 181.864 337 181.864 310.2H252.064C252.064 318.2 255.064 324.8 261.064 330C267.064 335.2 275.064 337.8 285.064 337.8C294.664 337.8 302.464 335 308.464 329.4C314.864 323.8 318.064 316.6 318.064 307.8C318.064 299.8 315.264 293.2 309.664 288C304.064 282.8 296.864 280.2 288.064 280.2H250.864V218.4H288.064C294.864 218.4 300.664 216 305.464 211.2C310.264 206.4 312.664 200.4 312.664 193.2C312.664 184.8 310.064 178.2 304.864 173.4C299.664 168.6 293.064 166.2 285.064 166.2C277.864 166.2 271.664 168.4 266.464 172.8C261.664 177.2 259.264 182.8 259.264 189.6H192.664C192.664 164.8 201.464 144.6 219.064 129C236.664 113 259.264 105 286.864 105C314.464 105 336.864 112.2 354.064 126.6C371.664 141 380.464 160 380.464 183.6C380.464 200.8 375.864 214.8 366.664 225.6C357.464 236 345.664 243.2 331.264 247.2C348.464 252 362.064 260.2 372.064 271.8C382.464 283.4 387.664 298 387.664 315.6C387.664 340.4 378.264 360.8 359.464 376.8C340.664 392.8 316.264 400.8 286.264 400.8Z" fill="currentColor"/>
                            <path d="M78.2 167.6H53V105.2H146V400.2H78.2V167.6Z" fill="currentColor"/>
                            <path d="M286.264 400.8C255.464 400.8 230.264 392.4 210.664 375.6C191.464 358.8 181.864 337 181.864 310.2H252.064C252.064 318.2 255.064 324.8 261.064 330C267.064 335.2 275.064 337.8 285.064 337.8C294.664 337.8 302.464 335 308.464 329.4C314.864 323.8 318.064 316.6 318.064 307.8C318.064 299.8 315.264 293.2 309.664 288C304.064 282.8 296.864 280.2 288.064 280.2H250.864V218.4H288.064C294.864 218.4 300.664 216 305.464 211.2C310.264 206.4 312.664 200.4 312.664 193.2C312.664 184.8 310.064 178.2 304.864 173.4C299.664 168.6 293.064 166.2 285.064 166.2C277.864 166.2 271.664 168.4 266.464 172.8C261.664 177.2 259.264 182.8 259.264 189.6H192.664C192.664 164.8 201.464 144.6 219.064 129C236.664 113 259.264 105 286.864 105C314.464 105 336.864 112.2 354.064 126.6C371.664 141 380.464 160 380.464 183.6C380.464 200.8 375.864 214.8 366.664 225.6C357.464 236 345.664 243.2 331.264 247.2C348.464 252 362.064 260.2 372.064 271.8C382.464 283.4 387.664 298 387.664 315.6C387.664 340.4 378.264 360.8 359.464 376.8C340.664 392.8 316.264 400.8 286.264 400.8Z" stroke="var(--stroke-color)" stroke-width="2.4" mask="url(#path-3-mask)"/>
                            <path d="M78.2 167.6H53V105.2H146V400.2H78.2V167.6Z" stroke="var(--stroke-color)" stroke-width="2.4" mask="url(#path-3-mask)"/>
                        </g>

                        <g class="mix-blend-multiply dark:mix-blend-normal transition-all delay-400 opacity-100 duration-750 starting:opacity-0 motion-safe:starting:-translate-x-[78px] text-[#F3BEC7] dark:text-[#733000]">
                            <mask id="path-4-mask" maskUnits="userSpaceOnUse" x="76.6643" y="103" width="338" height="299" fill="black">
                                <rect fill="white" x="76.6643" y="103" width="338" height="299"/>
                                <path d="M311.929 400.8C281.129 400.8 255.929 392.4 236.329 375.6C217.129 358.8 207.529 337 207.529 310.2H277.729C277.729 318.2 280.729 324.8 286.729 330C292.729 335.2 300.729 337.8 310.729 337.8C320.329 337.8 328.129 335 334.129 329.4C340.529 323.8 343.729 316.6 343.729 307.8C343.729 299.8 340.929 293.2 335.329 288C329.729 282.8 322.529 280.2 313.729 280.2H276.529V218.4H313.729C320.529 218.4 326.329 216 331.129 211.2C335.929 206.4 338.329 200.4 338.329 193.2C338.329 184.8 335.729 178.2 330.529 173.4C325.329 168.6 318.729 166.2 310.729 166.2C303.529 166.2 297.329 168.4 292.129 172.8C287.329 177.2 284.929 182.8 284.929 189.6H218.329C218.329 164.8 227.129 144.6 244.729 129C262.329 113 284.929 105 312.529 105C340.129 105 362.529 112.2 379.729 126.6C397.329 141 406.129 160 406.129 183.6C406.129 200.8 401.529 214.8 392.329 225.6C383.129 236 371.329 243.2 356.929 247.2C374.129 252 387.729 260.2 397.729 271.8C408.129 283.4 413.329 298 413.329 315.6C413.329 340.4 403.929 360.8 385.129 376.8C366.329 392.8 341.929 400.8 311.929 400.8Z"/>
                                <path d="M103.864 167.6H78.6643V105.2H171.664V400.2H103.864V167.6Z"/>
                            </mask>
                            <path d="M311.929 400.8C281.129 400.8 255.929 392.4 236.329 375.6C217.129 358.8 207.529 337 207.529 310.2H277.729C277.729 318.2 280.729 324.8 286.729 330C292.729 335.2 300.729 337.8 310.729 337.8C320.329 337.8 328.129 335 334.129 329.4C340.529 323.8 343.729 316.6 343.729 307.8C343.729 299.8 340.929 293.2 335.329 288C329.729 282.8 322.529 280.2 313.729 280.2H276.529V218.4H313.729C320.529 218.4 326.329 216 331.129 211.2C335.929 206.4 338.329 200.4 338.329 193.2C338.329 184.8 335.729 178.2 330.529 173.4C325.329 168.6 318.729 166.2 310.729 166.2C303.529 166.2 297.329 168.4 292.129 172.8C287.329 177.2 284.929 182.8 284.929 189.6H218.329C218.329 164.8 227.129 144.6 244.729 129C262.329 113 284.929 105 312.529 105C340.129 105 362.529 112.2 379.729 126.6C397.329 141 406.129 160 406.129 183.6C406.129 200.8 401.529 214.8 392.329 225.6C383.129 236 371.329 243.2 356.929 247.2C374.129 252 387.729 260.2 397.729 271.8C408.129 283.4 413.329 298 413.329 315.6C413.329 340.4 403.929 360.8 385.129 376.8C366.329 392.8 341.929 400.8 311.929 400.8Z" fill="currentColor"/>
                            <path d="M103.864 167.6H78.6643V105.2H171.664V400.2H103.864V167.6Z" fill="currentColor"/>
                            <path d="M311.929 400.8C281.129 400.8 255.929 392.4 236.329 375.6C217.129 358.8 207.529 337 207.529 310.2H277.729C277.729 318.2 280.729 324.8 286.729 330C292.729 335.2 300.729 337.8 310.729 337.8C320.329 337.8 328.129 335 334.129 329.4C340.529 323.8 343.729 316.6 343.729 307.8C343.729 299.8 340.929 293.2 335.329 288C329.729 282.8 322.529 280.2 313.729 280.2H276.529V218.4H313.729C320.529 218.4 326.329 216 331.129 211.2C335.929 206.4 338.329 200.4 338.329 193.2C338.329 184.8 335.729 178.2 330.529 173.4C325.329 168.6 318.729 166.2 310.729 166.2C303.529 166.2 297.329 168.4 292.129 172.8C287.329 177.2 284.929 182.8 284.929 189.6H218.329C218.329 164.8 227.129 144.6 244.729 129C262.329 113 284.929 105 312.529 105C340.129 105 362.529 112.2 379.729 126.6C397.329 141 406.129 160 406.129 183.6C406.129 200.8 401.529 214.8 392.329 225.6C383.129 236 371.329 243.2 356.929 247.2C374.129 252 387.729 260.2 397.729 271.8C408.129 283.4 413.329 298 413.329 315.6C413.329 340.4 403.929 360.8 385.129 376.8C366.329 392.8 341.929 400.8 311.929 400.8Z" stroke="var(--stroke-color)" stroke-width="2.4" mask="url(#path-4-mask)"/>
                            <path d="M103.864 167.6H78.6643V105.2H171.664V400.2H103.864V167.6Z" stroke="var(--stroke-color)" stroke-width="2.4" mask="url(#path-4-mask)"/>
                        </g>

                        <g class="mix-blend-hard-light transition-all delay-400 opacity-100 duration-750 starting:opacity-0 motion-safe:starting:-translate-x-[102px] text-[#F3BEC7] dark:text-[#4B0600]">
                            <mask id="path-5-mask" maskUnits="userSpaceOnUse" x="102.329" y="103" width="338" height="299" fill="black">
                                <rect fill="white" x="102.329" y="103" width="338" height="299"/>
                                <path d="M337.593 400.8C306.793 400.8 281.593 392.4 261.993 375.6C242.793 358.8 233.193 337 233.193 310.2H303.393C303.393 318.2 306.393 324.8 312.393 330C318.393 335.2 326.393 337.8 336.393 337.8C345.993 337.8 353.793 335 359.793 329.4C366.193 323.8 369.393 316.6 369.393 307.8C369.393 299.8 366.593 293.2 360.993 288C355.393 282.8 348.193 280.2 339.393 280.2H302.193V218.4H339.393C346.193 218.4 351.993 216 356.793 211.2C361.593 206.4 363.993 200.4 363.993 193.2C363.993 184.8 361.393 178.2 356.193 173.4C350.993 168.6 344.393 166.2 336.393 166.2C329.193 166.2 322.993 168.4 317.793 172.8C312.993 177.2 310.593 182.8 310.593 189.6H243.993C243.993 164.8 252.793 144.6 270.393 129C287.993 113 310.593 105 338.193 105C365.793 105 388.193 112.2 405.393 126.6C422.993 141 431.793 160 431.793 183.6C431.793 200.8 427.193 214.8 417.993 225.6C408.793 236 396.993 243.2 382.593 247.2C399.793 252 413.393 260.2 423.393 271.8C433.793 283.4 438.993 298 438.993 315.6C438.993 340.4 429.593 360.8 410.793 376.8C391.993 392.8 367.593 400.8 337.593 400.8Z"/>
                                <path d="M129.529 167.6H104.329V105.2H197.329V400.2H129.529V167.6Z"/>
                            </mask>
                            <path d="M337.593 400.8C306.793 400.8 281.593 392.4 261.993 375.6C242.793 358.8 233.193 337 233.193 310.2H303.393C303.393 318.2 306.393 324.8 312.393 330C318.393 335.2 326.393 337.8 336.393 337.8C345.993 337.8 353.793 335 359.793 329.4C366.193 323.8 369.393 316.6 369.393 307.8C369.393 299.8 366.593 293.2 360.993 288C355.393 282.8 348.193 280.2 339.393 280.2H302.193V218.4H339.393C346.193 218.4 351.993 216 356.793 211.2C361.593 206.4 363.993 200.4 363.993 193.2C363.993 184.8 361.393 178.2 356.193 173.4C350.993 168.6 344.393 166.2 336.393 166.2C329.193 166.2 322.993 168.4 317.793 172.8C312.993 177.2 310.593 182.8 310.593 189.6H243.993C243.993 164.8 252.793 144.6 270.393 129C287.993 113 310.593 105 338.193 105C365.793 105 388.193 112.2 405.393 126.6C422.993 141 431.793 160 431.793 183.6C431.793 200.8 427.193 214.8 417.993 225.6C408.793 236 396.993 243.2 382.593 247.2C399.793 252 413.393 260.2 423.393 271.8C433.793 283.4 438.993 298 438.993 315.6C438.993 340.4 429.593 360.8 410.793 376.8C391.993 392.8 367.593 400.8 337.593 400.8Z" fill="currentColor"/>
                            <path d="M129.529 167.6H104.329V105.2H197.329V400.2H129.529V167.6Z" fill="currentColor"/>
                            <path d="M337.593 400.8C306.793 400.8 281.593 392.4 261.993 375.6C242.793 358.8 233.193 337 233.193 310.2H303.393C303.393 318.2 306.393 324.8 312.393 330C318.393 335.2 326.393 337.8 336.393 337.8C345.993 337.8 353.793 335 359.793 329.4C366.193 323.8 369.393 316.6 369.393 307.8C369.393 299.8 366.593 293.2 360.993 288C355.393 282.8 348.193 280.2 339.393 280.2H302.193V218.4H339.393C346.193 218.4 351.993 216 356.793 211.2C361.593 206.4 363.993 200.4 363.993 193.2C363.993 184.8 361.393 178.2 356.193 173.4C350.993 168.6 344.393 166.2 336.393 166.2C329.193 166.2 322.993 168.4 317.793 172.8C312.993 177.2 310.593 182.8 310.593 189.6H243.993C243.993 164.8 252.793 144.6 270.393 129C287.993 113 310.593 105 338.193 105C365.793 105 388.193 112.2 405.393 126.6C422.993 141 431.793 160 431.793 183.6C431.793 200.8 427.193 214.8 417.993 225.6C408.793 236 396.993 243.2 382.593 247.2C399.793 252 413.393 260.2 423.393 271.8C433.793 283.4 438.993 298 438.993 315.6C438.993 340.4 429.593 360.8 410.793 376.8C391.993 392.8 367.593 400.8 337.593 400.8Z" stroke="var(--stroke-color)" stroke-width="2.4" mask="url(#path-5-mask)"/>
                            <path d="M129.529 167.6H104.329V105.2H197.329V400.2H129.529V167.6Z" stroke="var(--stroke-color)" stroke-width="2.4" mask="url(#path-5-mask)"/>
                        </g>
                    </svg>
                    <div class="absolute inset-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"></div>
                </div>
            </main>
        </div>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
