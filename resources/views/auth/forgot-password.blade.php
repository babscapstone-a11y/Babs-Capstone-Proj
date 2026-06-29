<x-guest-layout full>

<!-- BAB'S RESTO – Forgot Password Page -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap');
    :root{
        --primary:#DC2626;
        --accent:#F59E0B;
        --black:#111827;
        --white:#fff;
        --glass:rgba(255,255,255,0.75);
        --muted:#6B7280;
        --success:#16A34A;
    }
    *{box-sizing:border-box}
    body{font-family:'Poppins',Inter,system-ui,Segoe UI,Roboto,Arial;margin:0}

    /* ── Page enter animation ── */
    @keyframes pageFadeIn{from{opacity:0}to{opacity:1}}
    @keyframes cardSlideUp{from{opacity:0;transform:translateY(32px)}to{opacity:1;transform:translateY(0)}}
    @keyframes wordIn{to{opacity:1;transform:none}}
    @keyframes spin{to{transform:rotate(360deg)}}
    @keyframes fadeInDown{from{opacity:0;transform:translateY(-12px)}to{opacity:1;transform:translateY(0)}}

    .auth-wrap{
        min-height:100vh;
        display:flex;
        align-items:stretch;
        animation:pageFadeIn .5s ease both;
    }

    /* ── Left Side ── */
    .left-side{
        flex:1;
        position:relative;
        overflow:hidden;
        background:linear-gradient(135deg,rgba(220,38,38,0.06),rgba(245,158,11,0.03));
        display:flex;
        align-items:center;
        padding:3.5rem;
    }
    .left-inner{
        max-width:520px;
        color:var(--black);
        margin-left:6rem;
        position:relative;
        z-index:1;
    }

    /* Logo badge */
    .logo-badge{
        width:96px;height:96px;border-radius:20px;
        background:var(--primary);
        display:flex;align-items:center;justify-content:center;
        color:var(--white);font-weight:800;font-size:28px;
        box-shadow:0 25px 60px rgba(220,38,38,0.22);
    }
    .brand{font-weight:800;letter-spacing:0.4px;margin-top:.6rem;font-size:1rem}

    /* Animated slogan */
    .animated-slogan{
        font-size:2.2rem;font-weight:800;margin-top:1.2rem;
        color:rgba(17,24,39,0.9);line-height:1.15;
    }
    .animated-slogan .word{
        display:inline-block;opacity:0;
        transform:translateY(8px);
        animation:wordIn 1s forwards;
    }
    .animated-slogan .word:nth-child(1){animation-delay:.2s}
    .animated-slogan .word:nth-child(2){animation-delay:.7s}
    .animated-slogan .word:nth-child(3){animation-delay:1.2s}
    .animated-slogan .accent{color:var(--primary)}

    .desc{color:var(--muted);margin-top:1rem;line-height:1.65;max-width:420px;font-size:.95rem}

    .tagline{
        display:inline-flex;align-items:center;gap:.5rem;
        margin-top:1.8rem;
        background:rgba(255,255,255,0.55);
        backdrop-filter:blur(6px);
        border:1px solid rgba(220,38,38,0.12);
        border-radius:50px;
        padding:.45rem 1rem;
        font-size:.85rem;font-weight:600;color:var(--primary);
    }

    /* Floating decorative blobs */
    .shape{
        position:absolute;border-radius:50%;
        opacity:.12;filter:blur(18px);
        transform:translate3d(0,0,0);
        transition:transform .08s linear;
    }
    .shape.s1{width:360px;height:360px;background:var(--primary);left:-120px;top:-80px}
    .shape.s2{width:220px;height:220px;background:var(--accent);right:-60px;bottom:-60px}
    .shape.s3{width:120px;height:120px;background:var(--black);left:40%;top:20%}

    /* ── Right Side ── */
    .right-side{
        width:520px;
        display:flex;align-items:center;justify-content:center;
        padding:3.5rem;
        background:linear-gradient(180deg,#ffffff,#f8fafc);
    }

    /* Glassmorphism card */
    .reset-card{
        width:100%;border-radius:24px;
        padding:2.5rem;
        background:var(--glass);
        backdrop-filter:blur(14px);
        -webkit-backdrop-filter:blur(14px);
        box-shadow:0 30px 80px rgba(17,24,39,0.09),0 0 0 1px rgba(255,255,255,0.6) inset;
        border:1px solid rgba(17,24,39,0.06);
        animation:cardSlideUp .55s cubic-bezier(.22,.68,0,1.2) both;
        animation-delay:.15s;
    }

    .reset-card h2{
        margin:0;font-weight:800;color:var(--black);font-size:1.65rem;
    }
    .reset-sub{color:var(--muted);margin-top:.35rem;font-size:.92rem}

    /* Icon header decoration */
    .card-icon{
        width:56px;height:56px;border-radius:16px;
        background:linear-gradient(135deg,var(--primary),#F97316);
        display:flex;align-items:center;justify-content:center;
        color:#fff;font-size:1.4rem;
        box-shadow:0 12px 30px rgba(220,38,38,0.25);
        margin-bottom:1.25rem;
    }

    /* ── Input styling ── */
    .form-label{font-weight:600;color:var(--black);margin-bottom:.5rem;display:block;font-size:.92rem}
    .input-wrap{
        display:flex;align-items:center;
        background:linear-gradient(135deg,rgba(255,255,255,0.9),rgba(245,158,11,0.03));
        border:1.5px solid rgba(17,24,39,0.08);
        border-radius:14px;
        padding:0;
        transition:all .25s ease;
        box-shadow:0 4px 12px rgba(0,0,0,0.04);
    }
    .input-wrap:focus-within{
        border-color:var(--primary);
        box-shadow:0 8px 24px rgba(220,38,38,0.14);
        transform:translateY(-2px);
    }
    .input-wrap .icon{
        padding:.75rem .9rem;
        color:var(--primary);
        flex-shrink:0;
        display:flex;align-items:center;font-size:1rem;
    }
    .input-wrap input{
        flex:1;min-width:0;
        background:transparent;border:none;outline:none;
        padding:.75rem .75rem .75rem 0;
        font-size:.95rem;color:var(--black);
        font-family:inherit;
    }
    .input-wrap input:focus,
    .input-wrap input:focus-visible { outline: none; box-shadow: none; }
    .input-wrap input::placeholder{color:rgba(17,24,39,0.28);font-weight:400}
    .input-wrap input.error{color:#DC2626}

    .field-error{
        color:#DC2626;font-size:.82rem;margin-top:.4rem;
        display:flex;align-items:center;gap:.3rem;
    }

    /* ── Success notification ── */
    .success-note{
        background:linear-gradient(135deg,rgba(22,163,74,0.1),rgba(22,163,74,0.05));
        border:1.5px solid rgba(22,163,74,0.25);
        border-radius:14px;
        padding:1rem 1.1rem;
        display:flex;align-items:flex-start;gap:.7rem;
        margin-bottom:1.25rem;
        animation:fadeInDown .4s ease both;
    }
    .success-note .success-icon{
        color:var(--success);font-size:1.1rem;flex-shrink:0;margin-top:.05rem;
    }
    .success-note .success-text{
        color:#166534;font-size:.88rem;line-height:1.5;font-weight:500;
    }

    /* ── Button ── */
    .btn-submit{
        width:100%;
        background:linear-gradient(90deg,var(--primary),#F97316);
        color:var(--white);border:0;
        padding:.78rem 1rem;
        border-radius:14px;
        font-weight:700;font-size:.97rem;
        font-family:inherit;
        cursor:pointer;
        display:flex;align-items:center;justify-content:center;gap:.6rem;
        transition:transform .2s ease,box-shadow .2s ease,filter .2s ease;
        box-shadow:0 10px 30px rgba(220,38,38,0.22);
        margin-top:1.4rem;
    }
    .btn-submit:hover{
        transform:translateY(-3px) scale(1.015);
        box-shadow:0 18px 44px rgba(220,38,38,0.28);
        filter:brightness(1.06);
    }
    .btn-submit:active{transform:translateY(-1px) scale(1.005)}
    .btn-submit:disabled{opacity:.7;cursor:not-allowed;transform:none;filter:none}

    /* Loading spinner */
    .spinner{
        width:18px;height:18px;border-radius:50%;
        border:2px solid rgba(255,255,255,0.35);
        border-top-color:rgba(255,255,255,0.95);
        animation:spin .8s linear infinite;
        display:none;
        flex-shrink:0;
    }

    /* ── Links ── */
    .btn-back{
        width:100%;
        background:transparent;
        border:1.5px solid rgba(17,24,39,0.1);
        color:var(--muted);
        padding:.72rem 1rem;
        border-radius:14px;
        font-weight:600;font-size:.92rem;
        font-family:inherit;
        cursor:pointer;
        display:flex;align-items:center;justify-content:center;gap:.5rem;
        text-decoration:none;
        transition:all .2s ease;
        margin-top:.85rem;
    }
    .btn-back:hover{
        border-color:var(--primary);color:var(--primary);
        background:rgba(220,38,38,0.04);
        transform:translateY(-1px);
    }

    .signin-link{
        text-align:center;margin-top:1.1rem;
        font-size:.85rem;color:var(--muted);
    }
    .signin-link a{
        color:var(--primary);font-weight:600;text-decoration:none;
        transition:opacity .2s;
    }
    .signin-link a:hover{opacity:.8;text-decoration:underline}

    /* Divider */
    .divider{
        height:1px;background:rgba(17,24,39,0.07);
        margin:1.5rem 0;
    }

    /* ── Mobile ── */
    @media(max-width:991px){
        .auth-wrap{flex-direction:column}
        .left-side{padding:2.25rem 1.75rem}
        .left-inner{margin-left:0}
        .animated-slogan{font-size:1.5rem}
        .right-side{width:100%;padding:1.75rem}
        .reset-card{padding:2rem 1.5rem}
    }
    @media(max-width:480px){
        .right-side{padding:1.25rem}
        .reset-card{padding:1.5rem 1.25rem;border-radius:20px}
        .left-side{padding:1.75rem 1.25rem}
        .animated-slogan{font-size:1.3rem}
    }
</style>

<div class="auth-wrap" role="main">

    <!-- ══════════ LEFT SIDE ══════════ -->
    <div class="left-side" aria-hidden="true">
        <div class="shape s1"></div>
        <div class="shape s2"></div>
        <div class="shape s3"></div>

        <div class="left-inner">
            <!-- Logo -->
            <div style="display:flex;align-items:center;gap:.85rem">
                <div class="logo-badge" aria-label="BAB'S RESTO Logo">BR</div>
                <div>
                    <div class="brand" style="font-size:1.05rem;color:var(--black)">BAB'S RESTO</div>
                    <div style="font-size:.82rem;color:var(--muted)">Web-based Ordering&nbsp;•&nbsp;POS&nbsp;•&nbsp;Inventory</div>
                </div>
            </div>

            <!-- Heading -->
            <div class="animated-slogan">
                <span class="word">Forgot</span>&nbsp;
                <span class="word">your</span>&nbsp;
                <span class="word accent">password?</span>
            </div>

            <!-- Description -->
            <p class="desc">
                Don't worry! Enter your email address and we'll send you a secure password reset link so you can get back to managing your restaurant in no time.
            </p>

            <!-- Tagline badge -->
            <div class="tagline">
                <i class="fas fa-utensils" aria-hidden="true"></i>
                Be Always Busog Saraap
            </div>
        </div>
    </div>

    <!-- ══════════ RIGHT SIDE ══════════ -->
    <div class="right-side">
        <div class="reset-card" role="region" aria-labelledby="reset-heading">

            <!-- Icon -->
            <div class="card-icon" aria-hidden="true">
                <i class="fas fa-key"></i>
            </div>

            <h2 id="reset-heading">Reset Your Password</h2>
            <p class="reset-sub">Enter the email address associated with your account.</p>

            <div class="divider"></div>

            <!-- ── Success status ── -->
            @if (session('status'))
                <div class="success-note" role="alert" aria-live="polite">
                    <span class="success-icon"><i class="fas fa-circle-check"></i></span>
                    <div class="success-text">{{ session('status') }}</div>
                </div>
            @endif

            <!-- ── Form ── -->
            <form method="POST" action="{{ route('password.email') }}" id="resetForm" novalidate>
                @csrf

                <!-- Email -->
                <div style="margin-bottom:1.1rem">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrap @error('email') error @enderror">
                        <span class="icon" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="you@example.com"
                            aria-describedby="email-error"
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

                <!-- Submit button -->
                <button type="submit" id="submitBtn" class="btn-submit" aria-label="Send Password Reset Link">
                    <span class="spinner" id="spinner" aria-hidden="true"></span>
                    <span id="btnLabel"><i class="fas fa-paper-plane" aria-hidden="true"></i>&nbsp; Send Password Reset Link</span>
                </button>

            </form>

            <!-- Back to login -->
            <a href="{{ route('login') }}" class="btn-back" aria-label="Back to Login">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Back to Login
            </a>

            <p class="signin-link">
                Remember your password?&nbsp;<a href="{{ route('login') }}">Sign In</a>
            </p>

        </div>
    </div>

</div>

<!-- ── Font Awesome ── -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

<script>
(function(){
    // Loading spinner on submit
    var form = document.getElementById('resetForm');
    var btn  = document.getElementById('submitBtn');
    var spin = document.getElementById('spinner');
    var lbl  = document.getElementById('btnLabel');

    if(form && btn){
        form.addEventListener('submit', function(){
            if(!form.checkValidity()) return;
            spin.style.display = 'block';
            lbl.style.opacity  = '0.6';
            btn.disabled       = true;
        });
    }

    // Parallax blobs on left panel (desktop only)
    if(window.innerWidth > 991){
        var left = document.querySelector('.left-side');
        if(left){
            left.addEventListener('mousemove', function(e){
                var r = left.getBoundingClientRect();
                var x = (e.clientX - r.left  - r.width /2) / r.width;
                var y = (e.clientY - r.top   - r.height/2) / r.height;
                document.querySelectorAll('.shape').forEach(function(s,i){
                    s.style.transform = 'translate3d('+x*(18*(i+1))+'px,'+y*(18*(i+1))+'px,0)';
                });
            });
        }
    }
})();
</script>

</x-guest-layout>
