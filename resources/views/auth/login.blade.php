<x-guest-layout full>

<!-- BAB'S RESTO – Premium Login Page -->
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

    /* Radial colour washes */
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

    /* Blurred glow blobs */
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

    /* Floating food emoji icons */
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

    /* Inner content */
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

    /* Heading with staggered word reveal */
    .left-heading {
        font-size: 2.75rem; font-weight: 900;
        color: var(--white);
        line-height: 1.08;
        margin: 0;
    }
    .left-heading .line         { display: block; overflow: hidden; }
    .left-heading .line span    { display: block; opacity: 0; transform: translateY(14px); animation: wordReveal .72s cubic-bezier(.22,.68,0,1.2) forwards; }
    .left-heading .line:nth-child(1) span { animation-delay: .3s; }
    .left-heading .line:nth-child(2) span { animation-delay: .55s; }
    .left-heading .accent-word  { color: var(--accent); }

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

    /* Feature chips */
    .feature-row { display: flex; gap: .7rem; flex-wrap: wrap; margin-top: 2rem; }
    .feat-chip {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.09);
        border-radius: 10px;
        padding: .38rem .82rem;
        color: rgba(255,255,255,0.58);
        font-size: .77rem; font-weight: 500;
        display: inline-flex; align-items: center; gap: .38rem;
        backdrop-filter: blur(4px);
    }
    .feat-chip i { color: var(--accent); font-size: .72rem; }

    /* ════════════════════════════════
       RIGHT SIDE
       ════════════════════════════════ */
    .right-side {
        width: 500px;
        display: flex; align-items: center; justify-content: center;
        padding: 2.5rem;
        background: var(--bg);
        position: relative;
        overflow: hidden;
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

    /* ── Login card ── */
    .login-card {
        width: 100%;
        border-radius: 24px;
        padding: 2.25rem;
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

    /* ── Back to Home button ── */
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
        margin-bottom: 1.5rem;
        white-space: nowrap;
        line-height: 1;
    }
    .btn-back-home i {
        font-size: .75rem;
        transition: transform .22s ease;
    }
    .btn-back-home:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: var(--white);
        transform: translateX(-2px);
    }
    .btn-back-home:hover i { transform: translateX(-3px); }
    .btn-back-home:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    /* Card heading */
    .card-heading { margin: 0; font-size: 1.6rem; font-weight: 800; color: var(--dark); line-height: 1.2; }
    .card-sub { color: var(--muted); font-size: .87rem; margin-top: .3rem; line-height: 1.55; }

    /* Divider */
    .card-divider { height: 1px; background: rgba(17,24,39,0.07); margin: 1.35rem 0; }

    /* ── Input fields ── */
    .field-block { margin-bottom: 1.1rem; }
    .form-label {
        font-weight: 600; color: var(--dark);
        font-size: .875rem; margin-bottom: .45rem;
        display: block;
    }
    .input-wrap {
        display: flex; align-items: center;
        background: #ffffff;
        border: 1.5px solid rgba(17,24,39,0.09);
        border-radius: 14px;
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
        padding: 0 .88rem;
        color: var(--primary);
        font-size: .92rem;
        flex-shrink: 0;
        display: flex; align-items: center;
        min-height: 48px;
    }
    .input-field {
        flex: 1; min-width: 0;
        border: none; outline: none; background: transparent;
        padding: .76rem .5rem .76rem 0;
        font-size: .93rem; color: var(--dark);
        font-family: inherit;
    }
    .input-field:focus,
    .input-field:focus-visible { outline: none; box-shadow: none; }
    .input-field::placeholder { color: rgba(17,24,39,0.28); font-weight: 400; }
    .toggle-pwd {
        background: transparent; border: none; outline: none;
        color: var(--muted); cursor: pointer;
        padding: 0 .88rem;
        min-height: 48px;
        display: flex; align-items: center;
        font-size: .9rem;
        transition: color .2s;
        flex-shrink: 0;
    }
    .toggle-pwd:hover { color: var(--primary); }
    .input-wrap:focus-within .toggle-pwd { color: var(--primary); }
    .toggle-pwd:focus-visible { outline: 2px solid var(--primary); outline-offset: -2px; }

    .field-error {
        color: var(--error); font-size: .8rem;
        margin-top: .35rem;
        display: flex; align-items: center; gap: .3rem;
    }
    .field-error i { flex-shrink: 0; }

    /* Remember + Forgot row */
    .controls-row {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: .15rem; margin-bottom: .2rem;
    }
    .remember-label {
        display: flex; align-items: center; gap: .48rem;
        cursor: pointer; user-select: none;
    }
    .remember-label input[type="checkbox"] {
        width: 16px; height: 16px;
        accent-color: var(--primary);
        cursor: pointer;
        flex-shrink: 0;
        border-radius: 4px;
    }
    .remember-label span { font-size: .84rem; color: #374151; font-weight: 500; }
    .forgot-link {
        font-size: .84rem; font-weight: 600;
        color: var(--primary); text-decoration: none;
        transition: opacity .2s;
    }
    .forgot-link:hover { opacity: .75; text-decoration: underline; }
    .forgot-link:focus-visible { outline: 2px solid var(--primary); border-radius: 3px; }

    /* ── Login button ── */
    .btn-login {
        width: 100%;
        background: linear-gradient(90deg, var(--primary) 0%, #F97316 100%);
        color: var(--white);
        border: none; outline: none;
        padding: .82rem 1rem;
        border-radius: 14px;
        font-size: .97rem; font-weight: 700;
        font-family: inherit; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: .58rem;
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        box-shadow: 0 10px 30px rgba(220,38,38,0.28);
        margin-top: 1.35rem;
    }
    .btn-login:hover {
        transform: translateY(-3px) scale(1.013);
        box-shadow: 0 20px 46px rgba(220,38,38,0.36);
        filter: brightness(1.06);
    }
    .btn-login:active { transform: translateY(-1px) scale(1.004); }
    .btn-login:disabled { opacity: .68; cursor: not-allowed; transform: none; filter: none; }
    .btn-login:focus-visible { outline: 3px solid rgba(220,38,38,0.5); outline-offset: 2px; }

    /* Spinner inside button */
    .btn-spinner {
        width: 18px; height: 18px; border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        animation: spin .72s linear infinite;
        display: none; flex-shrink: 0;
    }
    .btn-loading-text { display: none; }

    /* Register link */
    .register-row {
        text-align: center;
        margin-top: 1.1rem;
        font-size: .84rem; color: var(--muted);
    }
    .register-row a {
        color: var(--primary); font-weight: 700; text-decoration: none;
        transition: opacity .2s;
    }
    .register-row a:hover { opacity: .75; text-decoration: underline; }
    .register-row a:focus-visible { outline: 2px solid var(--primary); border-radius: 3px; }

    /* ── Responsive ── */
    @media (max-width: 991px) {
        .auth-wrap  { flex-direction: column; }
        .left-side  { padding: 2.5rem 2rem; min-height: auto; }
        .left-inner { margin-left: 0; }
        .left-heading { font-size: 2.1rem; }
        .right-side { width: 100%; padding: 2rem; }
    }
    @media (max-width: 520px) {
        .left-side  { padding: 2rem 1.5rem; }
        .right-side { padding: 1.25rem; }
        .login-card { padding: 1.75rem 1.25rem; border-radius: 20px; }
        .left-heading { font-size: 1.7rem; }
        .feature-row { display: none; }
        .left-desc  { font-size: .87rem; }
    }
</style>

<div class="auth-wrap" role="main">

    <!-- ════════════ LEFT SIDE ════════════ -->
    <div class="left-side" aria-hidden="true">
        <!-- Glow blobs (parallax targets) -->
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
                <div class="logo-badge" aria-label="BAB'S RESTO logo initials">BR</div>
                <div>
                    <div class="brand-name">BAB'S RESTO</div>
                    <div class="brand-sub">Web-based Ordering&nbsp;•&nbsp;POS&nbsp;•&nbsp;Inventory</div>
                </div>
            </div>

            <div class="left-divider" aria-hidden="true"></div>

            <!-- Main heading -->
            <h1 class="left-heading">
                <span class="line"><span>Welcome</span></span>
                <span class="line"><span><span class="accent-word">Back!</span></span></span>
            </h1>

            <p class="left-desc">
                Sign in to order delicious meals, manage restaurant operations, and enjoy a seamless dining experience.
            </p>

            <!-- Tagline -->
            <div class="tagline-pill">
                <span class="tagline-dot" aria-hidden="true"></span>
                Be Always Busog Saraap
            </div>

            <!-- Feature chips -->
            <div class="feature-row" aria-hidden="true">
                <span class="feat-chip"><i class="fas fa-utensils"></i> Online Ordering</span>
                <span class="feat-chip"><i class="fas fa-cash-register"></i> POS System</span>
                <span class="feat-chip"><i class="fas fa-boxes-stacked"></i> Inventory</span>
            </div>
        </div>
    </div>

    <!-- ════════════ RIGHT SIDE ════════════ -->
    <div class="right-side">
        <div class="login-card" role="region" aria-labelledby="login-heading">

            <!-- Back to Home -->
            <a href="{{ url('/') }}" class="btn-back-home" aria-label="Back to Home">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Back to Home
            </a>

            <!-- Card header -->
            <h2 id="login-heading" class="card-heading">Welcome Back</h2>
            <p class="card-sub">Sign in to manage orders, inventory, and sales.</p>

            <div class="card-divider" aria-hidden="true"></div>

            <!-- Session status (Breeze) -->
            <x-auth-session-status class="mb-3" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                @csrf

                <!-- Email -->
                <div class="field-block">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrap @error('email') has-error @enderror">
                        <span class="input-icon" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
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
                        @if(session('registration_otp_email'))
                            <a href="{{ route('register.otp.verify') }}" style="display:inline-block;margin-top:.4rem;color:var(--primary);font-weight:600;font-size:.82rem;text-decoration:underline;">
                                Enter verification code
                            </a>
                        @endif
                    @enderror
                </div>

                <!-- Password -->
                <div class="field-block" style="margin-bottom:.6rem">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap @error('password') has-error @enderror">
                        <span class="input-icon" aria-hidden="true"><i class="fas fa-lock"></i></span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="Your password"
                            class="input-field"
                            aria-describedby="{{ $errors->has('password') ? 'pwd-error' : '' }}"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        >
                        <button
                            type="button"
                            id="togglePwd"
                            class="toggle-pwd"
                            aria-label="Show password"
                            title="Show password"
                        >
                            <i class="fas fa-eye" id="toggleIcon" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error" id="pwd-error" role="alert">
                            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Remember Me + Forgot Password -->
                <div class="controls-row">
                    <label class="remember-label" for="remember">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span>Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Login button -->
                <button
                    id="loginBtn"
                    type="submit"
                    class="btn-login"
                    aria-label="Sign In to BAB'S RESTO"
                >
                    <span class="btn-spinner" id="loginSpinner" aria-hidden="true"></span>
                    <span id="loginBtnText">
                        <i class="fas fa-right-to-bracket" aria-hidden="true"></i>&nbsp;Sign In
                    </span>
                    <span class="btn-loading-text" id="loginBtnLoading" aria-live="polite">
                        Signing in…
                    </span>
                </button>

            </form>

            <!-- Register link -->
            <p class="register-row">
                Don't have an account?&nbsp;<a href="{{ route('register') }}">Create one</a>
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
    /* ── Password show / hide ── */
    var toggleBtn = document.getElementById('togglePwd');
    var pwdInput  = document.getElementById('password');
    var eyeIcon   = document.getElementById('toggleIcon');

    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function () {
            var showing = pwdInput.getAttribute('type') === 'text';
            pwdInput.setAttribute('type', showing ? 'password' : 'text');
            eyeIcon.className = showing ? 'fas fa-eye' : 'fas fa-eye-slash';
            toggleBtn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    }

    /* ── Login button loading state ── */
    var form        = document.getElementById('loginForm');
    var loginBtn    = document.getElementById('loginBtn');
    var spinner     = document.getElementById('loginSpinner');
    var btnText     = document.getElementById('loginBtnText');
    var loadingText = document.getElementById('loginBtnLoading');

    if (form && loginBtn) {
        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return;
            spinner.style.display     = 'block';
            btnText.style.display     = 'none';
            loadingText.style.display = 'inline';
            loginBtn.disabled         = true;
        });
    }

    /* ── Left-panel parallax (desktop only) ── */
    if (window.innerWidth > 991) {
        var leftPanel = document.querySelector('.left-side');
        if (leftPanel) {
            leftPanel.addEventListener('mousemove', function (e) {
                var r = leftPanel.getBoundingClientRect();
                var x = (e.clientX - r.left   - r.width  / 2) / r.width;
                var y = (e.clientY - r.top    - r.height / 2) / r.height;
                document.querySelectorAll('.glow-blob').forEach(function (blob, i) {
                    var depth = (i + 1) * 18;
                    blob.style.transform = 'translate(' + (x * depth) + 'px,' + (y * depth) + 'px)';
                });
            });
        }
    }
})();
</script>

</x-guest-layout>
