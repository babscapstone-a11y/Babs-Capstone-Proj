<x-guest-layout full>

<!-- BAB'S RESTO – Premium Register Page (matches Login design) -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

    :root {
        --primary:      #DC2626;
        --primary-dark: #B91C1C;
        --accent:       #F59E0B;
        --dark:         #111827;
        --white:        #ffffff;
        --glass:        rgba(255,255,255,0.82);
        --muted:        #6B7280;
        --bg:           #F8FAFC;
        --error:        #EF4444;
        --success:      #16A34A;
        --warn:         #D97706;
    }

    *, *::before, *::after { box-sizing: border-box; }
    body {
        font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        margin: 0;
        background: var(--bg);
    }

    /* ── Keyframes ── */
    @keyframes pageFadeIn  { from { opacity: 0 } to { opacity: 1 } }
    @keyframes cardSlideUp { from { opacity: 0; transform: translateY(36px) } to { opacity: 1; transform: translateY(0) } }
    @keyframes wordReveal  { from { opacity: 0; transform: translateY(14px) } to { opacity: 1; transform: translateY(0) } }
    @keyframes floatUp     { 0%,100% { transform: translateY(0) rotate(0deg) } 50% { transform: translateY(-18px) rotate(6deg) } }
    @keyframes spin        { to { transform: rotate(360deg) } }
    @keyframes pulse       {
        0%,100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.5) }
        50%     { box-shadow: 0 0 0 8px rgba(245,158,11,0) }
    }
    @keyframes barGrow     { from { transform: scaleX(0) } to { transform: scaleX(1) } }

    /* ── Wrapper ── */
    .auth-wrap {
        min-height: 100vh;
        display: flex;
        align-items: stretch;
        animation: pageFadeIn .45s ease both;
    }

    /* ════════════════════════════════
       LEFT SIDE — Dark restaurant hero
       ════════════════════════════════ */
    .left-side {
        flex: 1;
        position: relative;
        overflow: hidden;
        background: linear-gradient(145deg, #1a0505 0%, #2a0808 35%, #111827 70%, #0d1117 100%);
        display: flex;
        align-items: center;
        padding: 3.5rem;
    }

    .left-side::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 55% at 15% 25%, rgba(220,38,38,0.22) 0%, transparent 60%),
            radial-gradient(ellipse 55% 45% at 85% 75%, rgba(245,158,11,0.09) 0%, transparent 55%),
            radial-gradient(ellipse 40% 40% at 55% 55%, rgba(220,38,38,0.07) 0%, transparent 65%);
        pointer-events: none;
    }

    /* Glow blobs */
    .glow-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(65px);
        pointer-events: none;
        transition: transform .1s linear;
    }
    .glow-blob.g1 { width: 320px; height: 320px; background: rgba(220,38,38,0.18); top: -90px; left: -90px; }
    .glow-blob.g2 { width: 220px; height: 220px; background: rgba(245,158,11,0.11); bottom: -70px; right: -70px; }
    .glow-blob.g3 { width: 160px; height: 160px; background: rgba(220,38,38,0.09); top: 45%; left: 48%; }

    /* Floating food icons */
    .food-icon {
        position: absolute;
        opacity: .065;
        animation: floatUp linear infinite;
        pointer-events: none;
        user-select: none;
        filter: grayscale(30%);
        line-height: 1;
    }
    .food-icon:nth-child(1)  { font-size: 2.6rem; top:  7%; left:  8%; animation-duration: 7.2s; animation-delay: 0s; }
    .food-icon:nth-child(2)  { font-size: 1.9rem; top: 18%; right: 10%; animation-duration: 5.8s; animation-delay: 1.1s; }
    .food-icon:nth-child(3)  { font-size: 2.3rem; top: 52%; left:  6%; animation-duration: 8.1s; animation-delay: 0.4s; }
    .food-icon:nth-child(4)  { font-size: 1.6rem; top: 74%; right: 13%; animation-duration: 6.3s; animation-delay: 2.2s; }
    .food-icon:nth-child(5)  { font-size: 2.0rem; top: 38%; right:  5%; animation-duration: 7.7s; animation-delay: 3.0s; }
    .food-icon:nth-child(6)  { font-size: 1.4rem; bottom: 12%; left: 22%; animation-duration: 5.4s; animation-delay: 1.7s; }
    .food-icon:nth-child(7)  { font-size: 1.3rem; top:  9%; right: 28%; animation-duration: 9.0s; animation-delay: 0.6s; }
    .food-icon:nth-child(8)  { font-size: 1.7rem; top: 64%; left: 33%; animation-duration: 6.8s; animation-delay: 2.6s; }

    /* Left inner content */
    .left-inner {
        max-width: 500px;
        position: relative;
        z-index: 1;
        margin-left: 5rem;
    }

    /* Logo */
    .logo-wrap { display: flex; align-items: center; gap: .9rem; }
    .logo-badge {
        width: 70px; height: 70px;
        border-radius: 18px;
        background: linear-gradient(135deg, var(--primary) 0%, #F97316 100%);
        display: flex; align-items: center; justify-content: center;
        color: var(--white); font-weight: 900; font-size: 21px;
        letter-spacing: -.5px;
        box-shadow: 0 18px 48px rgba(220,38,38,0.38), 0 0 0 1px rgba(255,255,255,0.08) inset;
        flex-shrink: 0;
    }
    .brand-name { color: var(--white); font-weight: 800; font-size: 1rem; line-height: 1.2; }
    .brand-sub  { color: rgba(255,255,255,0.42); font-size: .76rem; margin-top: .1rem; }

    /* Accent divider */
    .left-divider {
        width: 48px; height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        border-radius: 2px;
        margin: 1.65rem 0 1.25rem;
    }

    /* Animated heading — 3 staggered lines */
    .left-heading {
        font-size: 2.55rem; font-weight: 900;
        color: var(--white);
        line-height: 1.1;
        margin: 0;
    }
    .left-heading .line      { display: block; overflow: hidden; }
    .left-heading .line span { display: block; opacity: 0; transform: translateY(14px); animation: wordReveal .72s cubic-bezier(.22,.68,0,1.2) forwards; }
    .left-heading .line:nth-child(1) span { animation-delay: .25s; }
    .left-heading .line:nth-child(2) span { animation-delay: .48s; }
    .left-heading .line:nth-child(3) span { animation-delay: .71s; }
    .left-heading .accent-word { color: var(--accent); }

    .left-desc {
        color: rgba(255,255,255,0.52);
        margin-top: 1.1rem;
        line-height: 1.72;
        font-size: .91rem;
        max-width: 400px;
    }

    /* Tagline pill */
    .tagline-pill {
        display: inline-flex; align-items: center; gap: .55rem;
        margin-top: 1.8rem;
        background: rgba(220,38,38,0.14);
        border: 1px solid rgba(220,38,38,0.28);
        border-radius: 50px;
        padding: .5rem 1.15rem;
        color: rgba(255,255,255,0.82);
        font-size: .82rem; font-weight: 600;
        backdrop-filter: blur(6px);
    }
    .tagline-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: var(--accent);
        animation: pulse 2s ease-in-out infinite;
        flex-shrink: 0;
    }

    /* Benefit chips */
    .benefit-row { display: flex; gap: .7rem; flex-wrap: wrap; margin-top: 2rem; }
    .benefit-chip {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.09);
        border-radius: 10px;
        padding: .38rem .82rem;
        color: rgba(255,255,255,0.58);
        font-size: .77rem; font-weight: 500;
        display: inline-flex; align-items: center; gap: .38rem;
        backdrop-filter: blur(4px);
    }
    .benefit-chip i { color: var(--accent); font-size: .72rem; }

    /* ════════════════════════════════
       RIGHT SIDE
       ════════════════════════════════ */
    .right-side {
        width: 520px;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 2rem 2.25rem;
        background: var(--bg);
        position: relative;
        overflow-y: auto;
        min-height: 100vh;
    }

    /* Soft blurred circles behind the card */
    .right-side::before {
        content: '';
        position: absolute;
        width: 420px; height: 420px; border-radius: 50%;
        background: radial-gradient(circle, rgba(220,38,38,0.07) 0%, transparent 70%);
        top: -120px; right: -120px;
        pointer-events: none;
    }
    .right-side::after {
        content: '';
        position: absolute;
        width: 300px; height: 300px; border-radius: 50%;
        background: radial-gradient(circle, rgba(245,158,11,0.05) 0%, transparent 70%);
        bottom: -90px; left: -90px;
        pointer-events: none;
    }

    /* ── Registration card ── */
    .register-card {
        width: 100%;
        margin: auto 0;
        border-radius: 24px;
        padding: 2.1rem;
        background: var(--glass);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow:
            0 32px 80px rgba(17,24,39,0.10),
            0 0 0 1px rgba(255,255,255,0.72) inset,
            0 2px 4px rgba(255,255,255,0.5) inset;
        border: 1px solid rgba(17,24,39,0.06);
        position: relative;
        z-index: 1;
        animation: cardSlideUp .62s cubic-bezier(.22,.68,0,1.2) both;
        animation-delay: .18s;
    }

    /* ── Back to Home ── */
    .btn-back-home {
        display: inline-flex; align-items: center; gap: .42rem;
        padding: .4rem .88rem;
        border-radius: 50px;
        border: 1.5px solid rgba(17,24,39,0.11);
        background: transparent;
        color: #374151;
        font-size: .81rem; font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        text-decoration: none;
        transition: background .22s ease, border-color .22s ease, color .22s ease, transform .22s ease;
        margin-bottom: 1.35rem;
        white-space: nowrap;
        line-height: 1;
    }
    .btn-back-home i { font-size: .75rem; transition: transform .22s ease; }
    .btn-back-home:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: var(--white);
        transform: translateX(-2px);
    }
    .btn-back-home:hover i { transform: translateX(-3px); }
    .btn-back-home:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

    /* Card heading */
    .card-heading { margin: 0; font-size: 1.5rem; font-weight: 800; color: var(--dark); line-height: 1.2; }
    .card-sub { color: var(--muted); font-size: .86rem; margin-top: .28rem; line-height: 1.5; }

    /* Divider */
    .card-divider { height: 1px; background: rgba(17,24,39,0.07); margin: 1.2rem 0; }

    /* ── Input system ── */
    .field-block { margin-bottom: .9rem; }
    .field-block:last-of-type { margin-bottom: 0; }

    .name-row {
        display: flex; flex-wrap: wrap; gap: .75rem .9rem;
        margin-bottom: .9rem;
    }
    .name-row .field-block { flex: 1 1 220px; margin-bottom: 0; }

    .form-label {
        font-weight: 600; color: var(--dark);
        font-size: .85rem; margin-bottom: .4rem;
        display: flex; align-items: center; gap: .35rem;
    }
    .label-opt {
        font-size: .72rem; font-weight: 500;
        color: var(--muted);
        background: rgba(107,114,128,0.1);
        border-radius: 50px;
        padding: .05rem .42rem;
    }

    .input-wrap {
        display: flex; align-items: center;
        background: #ffffff;
        border: 1.5px solid rgba(17,24,39,0.09);
        border-radius: 13px;
        transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .input-wrap:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(220,38,38,0.09), 0 4px 14px rgba(220,38,38,0.10);
        transform: translateY(-1px);
    }
    .input-wrap.has-error {
        border-color: var(--error);
        box-shadow: 0 0 0 4px rgba(239,68,68,0.08);
    }

    .input-icon {
        padding: 0 .82rem;
        color: var(--primary);
        font-size: .9rem;
        flex-shrink: 0;
        display: flex; align-items: center;
        min-height: 46px;
    }
    .input-field {
        flex: 1; min-width: 0;
        border: none; outline: none; background: transparent;
        padding: .7rem .5rem .7rem 0;
        font-size: .91rem; color: var(--dark);
        font-family: inherit;
    }
    .input-field:focus,
    .input-field:focus-visible { outline: none; box-shadow: none; }
    .input-field::placeholder { color: rgba(17,24,39,0.28); font-weight: 400; }

    .toggle-pwd {
        background: transparent; border: none; outline: none;
        color: var(--muted); cursor: pointer;
        padding: 0 .82rem;
        min-height: 46px;
        display: flex; align-items: center;
        font-size: .88rem;
        transition: color .2s;
        flex-shrink: 0;
    }
    .toggle-pwd:hover { color: var(--primary); }
    .input-wrap:focus-within .toggle-pwd { color: var(--primary); }
    .toggle-pwd:focus-visible { outline: 2px solid var(--primary); outline-offset: -2px; }

    /* Validation error */
    .field-error {
        color: var(--error); font-size: .78rem;
        margin-top: .3rem;
        display: flex; align-items: center; gap: .28rem;
    }
    .field-error i { flex-shrink: 0; font-size: .72rem; }

    /* ── Password strength ── */
    .strength-track {
        display: flex; gap: 4px;
        margin-top: .48rem;
    }
    .strength-bar {
        flex: 1; height: 4px; border-radius: 2px;
        background: rgba(17,24,39,0.09);
        transition: background .35s ease;
        transform-origin: left;
    }
    .strength-bar.weak   { background: var(--error); }
    .strength-bar.fair   { background: var(--warn); }
    .strength-bar.good   { background: #2563EB; }
    .strength-bar.strong { background: var(--success); }

    .strength-hint {
        font-size: .75rem; font-weight: 600; margin-top: .22rem;
        height: 1rem;
    }
    .strength-hint.weak   { color: var(--error); }
    .strength-hint.fair   { color: var(--warn); }
    .strength-hint.good   { color: #2563EB; }
    .strength-hint.strong { color: var(--success); }

    /* ── Terms row ── */
    .terms-row {
        display: flex; align-items: flex-start; gap: .52rem;
        margin-top: .6rem;
    }
    .terms-row input[type="checkbox"] {
        width: 15px; height: 15px;
        accent-color: var(--primary);
        cursor: pointer; flex-shrink: 0;
        margin-top: .18rem;
    }
    .terms-row label {
        font-size: .82rem; color: #4B5563; cursor: pointer; line-height: 1.45;
    }
    .terms-row a { color: var(--primary); text-decoration: none; font-weight: 600; }
    .terms-row a:hover { text-decoration: underline; }

    /* ── Submit button ── */
    .btn-register {
        width: 100%;
        background: linear-gradient(90deg, var(--primary) 0%, #F97316 100%);
        color: var(--white);
        border: none; outline: none;
        padding: .8rem 1rem;
        border-radius: 13px;
        font-size: .96rem; font-weight: 700;
        font-family: inherit; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: .58rem;
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        box-shadow: 0 10px 30px rgba(220,38,38,0.28);
        margin-top: 1.15rem;
    }
    .btn-register:hover {
        transform: translateY(-3px) scale(1.013);
        box-shadow: 0 20px 46px rgba(220,38,38,0.36);
        filter: brightness(1.06);
    }
    .btn-register:active { transform: translateY(-1px) scale(1.004); }
    .btn-register:disabled { opacity: .68; cursor: not-allowed; transform: none; filter: none; }
    .btn-register:focus-visible { outline: 3px solid rgba(220,38,38,0.5); outline-offset: 2px; }

    /* Spinner */
    .btn-spinner {
        width: 17px; height: 17px; border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        animation: spin .72s linear infinite;
        display: none; flex-shrink: 0;
    }
    .btn-loading-text { display: none; }

    /* Login link */
    .login-row {
        text-align: center;
        margin-top: 1rem;
        font-size: .83rem; color: var(--muted);
    }
    .login-row a {
        color: var(--primary); font-weight: 700; text-decoration: none;
        transition: opacity .2s;
    }
    .login-row a:hover { opacity: .75; text-decoration: underline; }
    .login-row a:focus-visible { outline: 2px solid var(--primary); border-radius: 3px; }

    /* ── Responsive ── */
    @media (max-width: 991px) {
        .auth-wrap  { flex-direction: column; }
        .left-side  { padding: 2.5rem 2rem; min-height: auto; }
        .left-inner { margin-left: 0; }
        .left-heading { font-size: 2rem; }
        .right-side { width: 100%; min-height: auto; padding: 2rem; align-items: center; }
    }
    @media (max-width: 520px) {
        .left-side    { padding: 2rem 1.5rem; }
        .right-side   { padding: 1.25rem; }
        .register-card{ padding: 1.6rem 1.25rem; border-radius: 20px; }
        .left-heading { font-size: 1.7rem; }
        .benefit-row  { display: none; }
        .left-desc    { font-size: .86rem; }
    }
</style>

<div class="auth-wrap" role="main">

    <!-- ════════════ LEFT SIDE ════════════ -->
    <div class="left-side" aria-hidden="true">
        <!-- Glow blobs -->
        <div class="glow-blob g1"></div>
        <div class="glow-blob g2"></div>
        <div class="glow-blob g3"></div>

        <!-- Floating food icons -->
        <span class="food-icon">🍕</span>
        <span class="food-icon">🍔</span>
        <span class="food-icon">🍜</span>
        <span class="food-icon">🍣</span>
        <span class="food-icon">🥗</span>
        <span class="food-icon">🍗</span>
        <span class="food-icon">🌮</span>
        <span class="food-icon">🍱</span>

        <div class="left-inner">
            <!-- Logo -->
            <div class="logo-wrap">
                <div class="logo-badge" aria-label="BAB'S RESTO logo">BR</div>
                <div>
                    <div class="brand-name">BAB'S RESTO</div>
                    <div class="brand-sub">Web-based Ordering&nbsp;•&nbsp;POS&nbsp;•&nbsp;Inventory</div>
                </div>
            </div>

            <div class="left-divider" aria-hidden="true"></div>

            <!-- Heading — 3 staggered lines -->
            <h1 class="left-heading">
                <span class="line"><span>Join the</span></span>
                <span class="line"><span><span class="accent-word">BAB'S RESTO</span></span></span>
                <span class="line"><span>Family!</span></span>
            </h1>

            <p class="left-desc">
                Create your account to order delicious meals, track your orders, and enjoy a seamless dining experience.
            </p>

            <!-- Tagline -->
            <div class="tagline-pill">
                <span class="tagline-dot" aria-hidden="true"></span>
                Be Always Busog Saraap
            </div>

            <!-- Benefit chips -->
            <div class="benefit-row" aria-hidden="true">
                <span class="benefit-chip"><i class="fas fa-bolt"></i> Fast Ordering</span>
                <span class="benefit-chip"><i class="fas fa-truck"></i> Order Tracking</span>
                <span class="benefit-chip"><i class="fas fa-star"></i> Exclusive Deals</span>
            </div>
        </div>
    </div>

    <!-- ════════════ RIGHT SIDE ════════════ -->
    <div class="right-side">
        <div class="register-card" role="region" aria-labelledby="register-heading">

            <!-- Back to Home -->
            <a href="{{ url('/') }}" class="btn-back-home" aria-label="Back to Home">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Back to Home
            </a>

            <!-- Card header -->
            <h2 id="register-heading" class="card-heading">Create Account</h2>
            <p class="card-sub">Get started with BAB'S RESTO in just a few steps.</p>

            <div class="card-divider" aria-hidden="true"></div>

            <!-- Global error banner -->
            @if ($errors->any())
                <div style="
                    background: rgba(239,68,68,0.08);
                    border: 1.5px solid rgba(239,68,68,0.22);
                    border-radius: 12px;
                    padding: .7rem .9rem;
                    margin-bottom: 1rem;
                    display: flex; align-items: flex-start; gap: .5rem;
                    color: #B91C1C; font-size: .83rem; font-weight: 500;
                " role="alert" aria-live="polite">
                    <i class="fas fa-circle-exclamation" style="margin-top:.12rem;flex-shrink:0" aria-hidden="true"></i>
                    <span>Registration failed — please review the fields below.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
                @csrf

                <!-- First / Last Name -->
                <div class="name-row">
                    <div class="field-block">
                        <label class="form-label" for="first_name">
                            <i class="fas fa-user" style="color:var(--primary);font-size:.8rem" aria-hidden="true"></i>
                            First Name
                        </label>
                        <div class="input-wrap @error('first_name') has-error @enderror">
                            <span class="input-icon" aria-hidden="true"><i class="fas fa-user"></i></span>
                            <input
                                id="first_name"
                                name="first_name"
                                type="text"
                                value="{{ old('first_name') }}"
                                required
                                autofocus
                                autocomplete="given-name"
                                placeholder="Juan"
                                class="input-field"
                                aria-describedby="{{ $errors->has('first_name') ? 'first-name-error' : '' }}"
                                aria-invalid="{{ $errors->has('first_name') ? 'true' : 'false' }}"
                            >
                        </div>
                        @error('first_name')
                            <div class="field-error" id="first-name-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="field-block">
                        <label class="form-label" for="last_name">
                            <i class="fas fa-user" style="color:var(--primary);font-size:.8rem" aria-hidden="true"></i>
                            Last Name
                        </label>
                        <div class="input-wrap @error('last_name') has-error @enderror">
                            <span class="input-icon" aria-hidden="true"><i class="fas fa-user"></i></span>
                            <input
                                id="last_name"
                                name="last_name"
                                type="text"
                                value="{{ old('last_name') }}"
                                required
                                autocomplete="family-name"
                                placeholder="dela Cruz"
                                class="input-field"
                                aria-describedby="{{ $errors->has('last_name') ? 'last-name-error' : '' }}"
                                aria-invalid="{{ $errors->has('last_name') ? 'true' : 'false' }}"
                            >
                        </div>
                        @error('last_name')
                            <div class="field-error" id="last-name-error" role="alert">
                                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div class="field-block">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope" style="color:var(--primary);font-size:.8rem" aria-hidden="true"></i>
                        Email Address
                    </label>
                    <div class="input-wrap @error('email') has-error @enderror">
                        <span class="input-icon" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="input-field"
                            aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        >
                    </div>
                    @error('email')
                        <div class="field-error" id="email-error" role="alert">
                            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Phone (optional — UI only, not backend-validated) -->
                <div class="field-block">
                    <label class="form-label" for="phone">
                        <i class="fas fa-phone" style="color:var(--primary);font-size:.8rem" aria-hidden="true"></i>
                        Phone Number
                        <span class="label-opt">Optional</span>
                    </label>
                    <div class="input-wrap">
                        <span class="input-icon" aria-hidden="true"><i class="fas fa-mobile-screen"></i></span>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            placeholder="+63 9XX XXX XXXX"
                            class="input-field"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="field-block">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock" style="color:var(--primary);font-size:.8rem" aria-hidden="true"></i>
                        Password
                    </label>
                    <div class="input-wrap @error('password') has-error @enderror">
                        <span class="input-icon" aria-hidden="true"><i class="fas fa-lock"></i></span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Min. 8 characters"
                            class="input-field"
                            aria-describedby="pwd-strength-hint {{ $errors->has('password') ? 'pwd-error' : '' }}"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        >
                        <button
                            type="button"
                            id="togglePwd"
                            class="toggle-pwd"
                            aria-label="Show password"
                            title="Show password"
                        >
                            <i class="fas fa-eye" id="togglePwdIcon" aria-hidden="true"></i>
                        </button>
                    </div>

                    <!-- Strength meter -->
                    <div class="strength-track" id="strengthTrack" aria-hidden="true">
                        <div class="strength-bar" id="sBar1"></div>
                        <div class="strength-bar" id="sBar2"></div>
                        <div class="strength-bar" id="sBar3"></div>
                        <div class="strength-bar" id="sBar4"></div>
                    </div>
                    <div class="strength-hint" id="pwd-strength-hint" aria-live="polite"></div>

                    @error('password')
                        <div class="field-error" id="pwd-error" role="alert">
                            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="field-block">
                    <label class="form-label" for="password_confirmation">
                        <i class="fas fa-shield-halved" style="color:var(--primary);font-size:.8rem" aria-hidden="true"></i>
                        Confirm Password
                    </label>
                    <div class="input-wrap" id="confirmWrap">
                        <span class="input-icon" aria-hidden="true"><i class="fas fa-shield-halved"></i></span>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Re-enter your password"
                            class="input-field"
                            aria-describedby="confirm-match-hint"
                        >
                        <button
                            type="button"
                            id="toggleConfirm"
                            class="toggle-pwd"
                            aria-label="Show confirm password"
                            title="Show confirm password"
                        >
                            <i class="fas fa-eye" id="toggleConfirmIcon" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="field-error" id="confirm-match-hint" style="display:none" aria-live="polite">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        Passwords do not match
                    </div>
                    @error('password_confirmation')
                        <div class="field-error" role="alert">
                            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Terms -->
                <div class="terms-row">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the
                        <a href="#" tabindex="-1">Terms of Service</a>
                        and
                        <a href="#" tabindex="-1">Privacy Policy</a>
                    </label>
                </div>
                @error('terms')
                    <div class="field-error" role="alert" style="margin-top:.3rem">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        {{ $message }}
                    </div>
                @enderror

                <!-- Submit -->
                <button
                    id="registerBtn"
                    type="submit"
                    class="btn-register"
                    aria-label="Create your BAB'S RESTO account"
                >
                    <span class="btn-spinner" id="regSpinner" aria-hidden="true"></span>
                    <span id="regBtnText">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>&nbsp;Create Account
                    </span>
                    <span class="btn-loading-text" id="regBtnLoading" aria-live="polite">
                        Creating account…
                    </span>
                </button>

            </form>

            <!-- Login link -->
            <p class="login-row">
                Already have an account?&nbsp;<a href="{{ route('login') }}">Sign In</a>
            </p>

        </div>
    </div>

</div>

<!-- Font Awesome -->
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
>

<script>
(function () {

    /* ── Password strength scorer ── */
    function scorePassword(val) {
        if (!val) return 0;
        var score = 0;
        if (val.length >= 8)  score++;
        if (val.length >= 12) score++;
        if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[!@#$%^&*()\-_=+\[\]{};':"\\|,.<>\/?`~]/.test(val)) score++;
        if (score <= 1) return 1;
        if (score <= 2) return 2;
        if (score <= 3) return 3;
        return 4;
    }

    var LEVELS = ['', 'weak', 'fair', 'good', 'strong'];
    var LABELS = ['', 'Weak password', 'Fair password', 'Good password', 'Strong password'];

    var pwdInput    = document.getElementById('password');
    var bars        = [
        document.getElementById('sBar1'),
        document.getElementById('sBar2'),
        document.getElementById('sBar3'),
        document.getElementById('sBar4'),
    ];
    var strengthHint = document.getElementById('pwd-strength-hint');

    pwdInput && pwdInput.addEventListener('input', function () {
        var score = scorePassword(this.value);
        var level = this.value ? LEVELS[score] : '';
        bars.forEach(function (bar, i) {
            bar.className = 'strength-bar' + (i < score ? ' ' + level : '');
        });
        strengthHint.textContent  = this.value ? LABELS[score] : '';
        strengthHint.className    = 'strength-hint' + (level ? ' ' + level : '');
    });

    /* ── Show / hide password (main) ── */
    var togglePwd     = document.getElementById('togglePwd');
    var togglePwdIcon = document.getElementById('togglePwdIcon');
    togglePwd && togglePwd.addEventListener('click', function () {
        var isText = pwdInput.getAttribute('type') === 'text';
        pwdInput.setAttribute('type', isText ? 'password' : 'text');
        togglePwdIcon.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
        togglePwd.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
    });

    /* ── Show / hide password (confirm) ── */
    var confirmInput      = document.getElementById('password_confirmation');
    var toggleConfirm     = document.getElementById('toggleConfirm');
    var toggleConfirmIcon = document.getElementById('toggleConfirmIcon');
    toggleConfirm && toggleConfirm.addEventListener('click', function () {
        var isText = confirmInput.getAttribute('type') === 'text';
        confirmInput.setAttribute('type', isText ? 'password' : 'text');
        toggleConfirmIcon.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
        toggleConfirm.setAttribute('aria-label', isText ? 'Show confirm password' : 'Hide confirm password');
    });

    /* ── Real-time confirm match indicator ── */
    var confirmWrap = document.getElementById('confirmWrap');
    var matchHint   = document.getElementById('confirm-match-hint');
    confirmInput && confirmInput.addEventListener('input', function () {
        if (!this.value) { matchHint.style.display = 'none'; confirmWrap.classList.remove('has-error'); return; }
        var match = this.value === pwdInput.value;
        matchHint.style.display = match ? 'none' : 'flex';
        confirmWrap.classList.toggle('has-error', !match);
    });

    /* ── Submit loading state ── */
    var form        = document.getElementById('registerForm');
    var regBtn      = document.getElementById('registerBtn');
    var regSpinner  = document.getElementById('regSpinner');
    var regBtnText  = document.getElementById('regBtnText');
    var regLoading  = document.getElementById('regBtnLoading');
    var termsBox    = document.getElementById('terms');

    form && form.addEventListener('submit', function (e) {
        /* Client-side: password mismatch */
        if (pwdInput.value !== confirmInput.value) {
            e.preventDefault();
            matchHint.style.display = 'flex';
            confirmWrap.classList.add('has-error');
            confirmInput.focus();
            return;
        }
        /* Client-side: terms not accepted */
        if (termsBox && !termsBox.checked) {
            e.preventDefault();
            termsBox.focus();
            return;
        }
        /* Show loading */
        if (!form.checkValidity()) return;
        regSpinner.style.display    = 'block';
        regBtnText.style.display    = 'none';
        regLoading.style.display    = 'inline';
        regBtn.disabled             = true;
    });

    /* ── Left-panel parallax (desktop only) ── */
    if (window.innerWidth > 991) {
        var leftPanel = document.querySelector('.left-side');
        leftPanel && leftPanel.addEventListener('mousemove', function (e) {
            var r = leftPanel.getBoundingClientRect();
            var x = (e.clientX - r.left   - r.width  / 2) / r.width;
            var y = (e.clientY - r.top    - r.height / 2) / r.height;
            document.querySelectorAll('.glow-blob').forEach(function (blob, i) {
                var d = (i + 1) * 18;
                blob.style.transform = 'translate(' + (x * d) + 'px,' + (y * d) + 'px)';
            });
        });
    }

})();
</script>

</x-guest-layout>
