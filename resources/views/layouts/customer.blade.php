<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "Bab's Resto") – Digital Menu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:    #DC2626;
            --primary-dk: #B91C1C;
            --accent:     #F59E0B;
            --dark:       #111827;
            --text:       #1F2937;
            --muted:      #6B7280;
            --border:     #E5E7EB;
            --bg:         #F9FAFB;
            --white:      #FFFFFF;
            --success:    #16A34A;
            --shadow-sm:  0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.06);
            --shadow-md:  0 4px 16px rgba(0,0,0,.10);
            --shadow-lg:  0 12px 40px rgba(0,0,0,.14);
            --radius:     14px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }
        img { display: block; max-width: 100%; }

        /* ── Toast Notification ── */
        #toastContainer {
            position: fixed; top: 80px; right: 1.5rem;
            z-index: 9999; display: flex; flex-direction: column; gap: .5rem;
            pointer-events: none;
        }
        .toast {
            display: flex; align-items: center; gap: .65rem;
            background: var(--dark); color: #fff;
            padding: .75rem 1.1rem; border-radius: 12px;
            font-size: .83rem; font-weight: 500;
            box-shadow: var(--shadow-lg);
            pointer-events: all;
            animation: toastIn .3s cubic-bezier(.34,1.56,.64,1) both;
            max-width: 320px;
        }
        .toast.success { background: var(--success); }
        .toast.error   { background: var(--primary); }
        @keyframes toastIn  { from { opacity:0; transform:translateX(60px) } to { opacity:1; transform:translateX(0) } }
        @keyframes toastOut { from { opacity:1; transform:translateX(0) }   to { opacity:0; transform:translateX(60px) } }
        .toast.hiding { animation: toastOut .3s ease forwards; }

        /* ── Shared confirm modal ── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            display: none; align-items: center; justify-content: center;
            z-index: 2000; padding: 1rem;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--white); border-radius: 20px;
            padding: 2rem; max-width: 440px; width: 100%;
            box-shadow: var(--shadow-lg);
            animation: modalIn .3s cubic-bezier(.22,.68,0,1.2) both;
        }
        @keyframes modalIn { from { opacity: 0; transform: scale(.92) translateY(16px) } to { opacity: 1; transform: none } }
        .modal-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.1rem; font-size: 1.4rem;
            background: rgba(245,158,11,0.12); color: var(--accent);
        }
        .modal-title { font-size: 1.15rem; font-weight: 700; text-align: center; margin: 0 0 .4rem; color: var(--dark); }
        .modal-desc  { font-size: .9rem; color: var(--muted); text-align: center; line-height: 1.6; margin: 0 0 1.5rem; }
        .modal-actions { display: flex; gap: .75rem; }
        .modal-actions button {
            flex: 1; padding: .7rem; border-radius: 10px;
            font-size: .9rem; font-weight: 600; font-family: inherit;
            cursor: pointer; text-align: center;
            transition: all .18s ease; border: none;
        }
        .btn-modal-cancel { background: rgba(17,24,39,0.07); color: var(--dark); }
        .btn-modal-cancel:hover { background: rgba(17,24,39,0.12); }
        .btn-modal-confirm { background: var(--primary); color: var(--white); }
        .btn-modal-confirm:hover { background: var(--primary-dk); }
    </style>
    @yield('styles')
</head>
<body>

{{-- Toast container --}}
<div id="toastContainer" aria-live="polite" aria-atomic="true"></div>

@yield('content')

{{-- Shared confirm modal --}}
<div class="modal-overlay" id="confirmModal" role="dialog" aria-modal="true">
    <div class="modal-box">
        <div class="modal-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <h3 class="modal-title" id="modalTitle">Are you sure?</h3>
        <p class="modal-desc" id="modalDesc"></p>
        <div class="modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn-modal-confirm" id="modalConfirmBtn">Confirm</button>
        </div>
    </div>
</div>

{{-- Shared toast + confirm-modal helpers --}}
<script>
function showToast(msg, type = 'success', duration = 3200) {
    const container = document.getElementById('toastContainer');
    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i><span>${msg}</span>`;
    container.appendChild(el);
    setTimeout(() => {
        el.classList.add('hiding');
        setTimeout(() => el.remove(), 300);
    }, duration);
}

let _confirmCallback = null;
function openConfirmModal({ title, desc, confirmText = 'Confirm', onConfirm }) {
    document.getElementById('modalTitle').textContent = title || 'Are you sure?';
    document.getElementById('modalDesc').textContent = desc || '';
    const btn = document.getElementById('modalConfirmBtn');
    btn.textContent = confirmText;
    _confirmCallback = onConfirm;
    document.getElementById('confirmModal').classList.add('open');
}
function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('open');
    _confirmCallback = null;
}
document.getElementById('modalConfirmBtn').addEventListener('click', function () {
    if (typeof _confirmCallback === 'function') _confirmCallback();
});
document.getElementById('confirmModal').addEventListener('click', function (e) {
    if (e.target === this) closeConfirmModal();
});
</script>

@yield('scripts')
</body>
</html>
