@extends('layouts.customer')

@section('title', "Bab's Resto – Digital Menu")

@section('styles')
<style>
/* ══════════════════════════════════════════════
   TOP NAV
══════════════════════════════════════════════ */
.top-nav {
    position: sticky; top: 0; z-index: 900;
    background: var(--white);
    border-bottom: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
}
.nav-inner {
    max-width: 1280px; margin: 0 auto;
    display: flex; align-items: center; gap: 1rem;
    padding: .75rem 1.5rem;
}
.nav-logo {
    display: flex; align-items: center; gap: .6rem; flex-shrink: 0;
}
.nav-logo-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dk));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #fff;
}
.nav-logo-text { font-weight: 800; font-size: 1.05rem; color: var(--dark); }
.nav-logo-sub  { font-size: .68rem; font-weight: 500; color: var(--muted); letter-spacing: .04em; margin-top: -3px; }

.nav-search {
    flex: 1; min-width: 0; max-width: 480px;
    display: flex; align-items: center;
    background: var(--bg); border: 1.5px solid var(--border);
    border-radius: 50px; overflow: hidden;
    transition: border-color .2s;
}
.nav-search:focus-within { border-color: var(--primary); }
.nav-search i { padding: 0 .8rem 0 1.1rem; color: var(--muted); font-size: .9rem; }
.nav-search input {
    flex: 1; border: none; background: transparent;
    font-family: inherit; font-size: .88rem; color: var(--text);
    padding: .6rem 0; outline: none;
}
.nav-search input::placeholder { color: var(--muted); }
.nav-search-btn {
    padding: .5rem 1.1rem; background: var(--primary); border: none;
    color: #fff; font-size: .82rem; font-weight: 600; font-family: inherit;
    cursor: pointer; transition: background .2s;
    border-radius: 0 50px 50px 0;
}
.nav-search-btn:hover { background: var(--primary-dk); }

.nav-actions { display: flex; align-items: center; gap: .75rem; margin-left: auto; }

.cart-btn {
    position: relative; display: flex; align-items: center; justify-content: center;
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--bg); border: 1.5px solid var(--border);
    cursor: pointer; transition: all .2s; color: var(--dark);
    font-size: 1.1rem;
}
.cart-btn:hover { background: #fee2e2; border-color: var(--primary); color: var(--primary); }
.cart-badge {
    position: absolute; top: -4px; right: -4px;
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--primary); color: #fff;
    font-size: .65rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--white);
    transition: transform .2s;
}
.cart-badge.bump { animation: cartBump .3s cubic-bezier(.36,.07,.19,.97); }
@keyframes cartBump { 0%,100%{transform:scale(1)} 50%{transform:scale(1.55)} }

.profile-menu { position: relative; }
.profile-btn {
    display: flex; align-items: center; gap: .5rem;
    padding: .4rem .8rem .4rem .5rem;
    border-radius: 50px; border: 1.5px solid var(--border);
    background: var(--bg); cursor: pointer;
    transition: all .2s; font-family: inherit;
}
.profile-btn:hover { border-color: var(--primary); background: #fff; }
.profile-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .78rem; font-weight: 700;
}
.profile-name { font-size: .82rem; font-weight: 600; color: var(--dark); max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.profile-dropdown {
    display: none; position: absolute; right: 0; top: calc(100% + 8px);
    background: var(--white); border: 1px solid var(--border);
    border-radius: 14px; box-shadow: var(--shadow-lg);
    min-width: 200px; overflow: hidden; z-index: 1000;
}
.profile-dropdown.open { display: block; animation: dropDown .2s ease; }
@keyframes dropDown { from { opacity:0; transform:translateY(-8px) } to { opacity:1; transform:translateY(0) } }
.dropdown-header { padding: .8rem 1rem; border-bottom: 1px solid var(--border); }
.dropdown-header-name { font-weight: 700; font-size: .88rem; color: var(--dark); }
.dropdown-header-email { font-size: .76rem; color: var(--muted); }
.dropdown-item {
    display: flex; align-items: center; gap: .6rem;
    padding: .7rem 1rem; font-size: .84rem; color: var(--text);
    transition: background .15s; cursor: pointer; width: 100%; border: none;
    background: none; font-family: inherit; text-align: left;
}
.dropdown-item:hover { background: var(--bg); }
.dropdown-item.danger { color: var(--primary); }
.dropdown-item i { width: 16px; text-align: center; color: var(--muted); }
.dropdown-item.danger i { color: var(--primary); }

/* ── Top nav responsive (phones) ── */
@media (max-width: 768px) {
    .nav-inner { gap: .6rem; padding: .65rem 1rem; }
    .nav-logo-sub { display: none; }
    .nav-search-btn { display: none; }
    .nav-search input { font-size: .82rem; }
}
@media (max-width: 480px) {
    .nav-logo-text { font-size: .92rem; }
    .profile-name { display: none; }
    .nav-search i { padding: 0 .5rem 0 .7rem; }
}

/* ══════════════════════════════════════════════
   HERO SECTION
══════════════════════════════════════════════ */
.hero {
    background: linear-gradient(135deg, #1a0a0a 0%, #3b0f0f 35%, #7f1d1d 70%, #dc2626 100%);
    color: #fff; padding: 3.5rem 1.5rem;
    position: relative; overflow: hidden;
}
.hero::before {
    content: ''; position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='28'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.hero-inner {
    max-width: 1280px; margin: 0 auto;
    display: flex; align-items: center; justify-content: space-between;
    gap: 2rem; position: relative;
}
.hero-text { max-width: 540px; }
.hero-tag {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
    border-radius: 50px; padding: .3rem .85rem;
    font-size: .78rem; font-weight: 600; letter-spacing: .04em;
    margin-bottom: 1.2rem; text-transform: uppercase;
}
.hero-tag i { color: var(--accent); }
.hero-title { font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 900; line-height: 1.15; margin-bottom: .8rem; }
.hero-title span { color: var(--accent); }
.hero-sub { font-size: .95rem; font-weight: 400; opacity: .8; line-height: 1.7; max-width: 400px; }
.hero-stats {
    display: flex; gap: 2rem; margin-top: 2rem;
}
.hero-stat-value { font-size: 1.4rem; font-weight: 800; }
.hero-stat-label { font-size: .75rem; opacity: .7; font-weight: 500; margin-top: .1rem; }

.hero-icons {
    display: flex; flex-direction: column; gap: 1rem;
    opacity: .15; font-size: 5rem; color: var(--accent);
    flex-shrink: 0;
}

@media (max-width: 700px) {
    .hero { padding: 2.25rem 1.25rem; }
    .hero-inner { flex-direction: column; align-items: flex-start; gap: 1.25rem; }
    .hero-icons { display: none; }
    .hero-stats { flex-wrap: wrap; gap: 1.25rem; }
}

/* ══════════════════════════════════════════════
   MAIN CONTENT AREA
══════════════════════════════════════════════ */
.catalog-layout {
    max-width: 1280px; margin: 0 auto;
    display: grid; grid-template-columns: 240px 1fr;
    gap: 2rem; padding: 2rem 1.5rem;
    align-items: start;
}
@media (max-width: 900px) {
    .catalog-layout { grid-template-columns: 1fr; }
    .cat-sidebar { display: none !important; }
}

/* ── Category Sidebar ── */
.cat-sidebar {
    position: sticky; top: 80px;
    background: var(--white); border-radius: var(--radius);
    border: 1px solid var(--border); box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.cat-sidebar-header {
    padding: 1rem 1.2rem .6rem;
    font-size: .78rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--muted);
}
.cat-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: .7rem 1.2rem; cursor: pointer;
    transition: background .15s, color .15s;
    border-left: 3px solid transparent;
    font-size: .86rem; font-weight: 500; color: var(--text);
}
.cat-item:hover { background: var(--bg); color: var(--primary); }
.cat-item.active {
    background: #fee2e2; color: var(--primary);
    border-left-color: var(--primary); font-weight: 600;
}
.cat-item-count {
    background: var(--bg); color: var(--muted);
    font-size: .72rem; font-weight: 600; padding: .15rem .45rem;
    border-radius: 50px; min-width: 24px; text-align: center;
}
.cat-item.active .cat-item-count { background: rgba(220,38,38,.1); color: var(--primary); }

/* ── Category chips (mobile) ── */
.cat-chips-row {
    display: none; gap: .5rem; overflow-x: auto; padding: 0 1.5rem 1rem;
    scrollbar-width: none; -ms-overflow-style: none;
}
.cat-chips-row::-webkit-scrollbar { display: none; }
@media (max-width: 900px) { .cat-chips-row { display: flex; } }
.cat-chip {
    flex-shrink: 0; padding: .42rem 1.1rem; border-radius: 50px;
    border: 1.5px solid var(--border); background: var(--white);
    font-size: .81rem; font-weight: 600; color: var(--text);
    cursor: pointer; transition: all .2s; white-space: nowrap;
}
.cat-chip.active, .cat-chip:hover {
    background: var(--primary); border-color: var(--primary); color: #fff;
}

/* ── Main feed ── */
.menu-feed { min-width: 0; }
.section-block { margin-bottom: 3rem; }
.section-heading {
    display: flex; align-items: center; gap: .75rem;
    margin-bottom: 1.25rem; padding-bottom: .75rem;
    border-bottom: 2px solid var(--border);
}
.section-heading h2 { font-size: 1.1rem; font-weight: 800; color: var(--dark); }
.section-heading-count {
    background: var(--bg); color: var(--muted);
    font-size: .75rem; font-weight: 700; padding: .2rem .6rem;
    border-radius: 50px;
}
.section-icon { font-size: 1.2rem; color: var(--primary); }

/* ── Menu Grid ── */
.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 1.25rem;
}
@media (max-width: 640px) { .menu-grid { grid-template-columns: repeat(2, 1fr); gap: .75rem; } }
@media (max-width: 380px) { .menu-grid { grid-template-columns: 1fr; } }

.menu-card {
    background: var(--white); border-radius: var(--radius);
    border: 1px solid var(--border); overflow: hidden;
    cursor: pointer; transition: transform .22s ease, box-shadow .22s ease;
    box-shadow: var(--shadow-sm);
    animation: cardFadeIn .4s ease both;
}
.menu-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
@keyframes cardFadeIn { from { opacity:0; transform:translateY(12px) } to { opacity:1; transform:translateY(0) } }

.card-img {
    width: 100%; aspect-ratio: 4/3; overflow: hidden;
    background: var(--bg); position: relative;
}
.card-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .35s ease;
}
.menu-card:hover .card-img img { transform: scale(1.06); }
.card-img-placeholder {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: #d1d5db;
    background: linear-gradient(135deg, #f9fafb, #f3f4f6);
}
.card-type-badge {
    position: absolute; top: 8px; left: 8px;
    padding: .22rem .6rem; border-radius: 50px;
    font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    backdrop-filter: blur(8px);
}
.badge-food     { background: rgba(34,197,94,.85); color: #fff; }
.badge-beverage { background: rgba(59,130,246,.85); color: #fff; }

.card-body { padding: .85rem; }
.card-category { font-size: .71rem; font-weight: 600; color: var(--primary); text-transform: uppercase; letter-spacing: .05em; margin-bottom: .2rem; }
.card-name { font-size: .9rem; font-weight: 700; color: var(--dark); line-height: 1.3; margin-bottom: .3rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.card-desc { font-size: .75rem; color: var(--muted); line-height: 1.5; margin-bottom: .75rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.card-footer { display: flex; align-items: center; justify-content: space-between; }
.card-price { font-size: 1rem; font-weight: 800; color: var(--primary); }

.add-btn {
    width: 34px; height: 34px; border-radius: 50%;
    background: var(--primary); border: none; color: #fff;
    font-size: .95rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s, transform .15s;
}
.add-btn:hover { background: var(--primary-dk); transform: scale(1.1); }
.add-btn:active { transform: scale(.95); }

/* ── Empty state ── */
.empty-state {
    text-align: center; padding: 4rem 2rem; color: var(--muted);
    background: var(--white); border-radius: var(--radius);
    border: 1px solid var(--border);
}
.empty-icon { font-size: 3rem; margin-bottom: 1rem; opacity: .4; }
.empty-title { font-size: 1.1rem; font-weight: 700; color: var(--dark); margin-bottom: .4rem; }

/* ══════════════════════════════════════════════
   ITEM DETAIL MODAL
══════════════════════════════════════════════ */
.modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,.6); backdrop-filter: blur(4px);
    display: none; align-items: center; justify-content: center; padding: 1rem;
}
.modal-overlay.open { display: flex; animation: overlayIn .25s ease; }
@keyframes overlayIn { from { opacity:0 } to { opacity:1 } }

.item-modal {
    background: var(--white); border-radius: 20px;
    width: 100%; max-width: 640px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: var(--shadow-lg);
    animation: modalSlide .3s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes modalSlide { from { opacity:0; transform:scale(.88) translateY(24px) } to { opacity:1; transform:scale(1) translateY(0) } }

.modal-img {
    width: 100%; aspect-ratio: 16/9; background: var(--bg); overflow: hidden;
    border-radius: 20px 20px 0 0; position: relative;
}
.modal-img img { width:100%; height:100%; object-fit:cover; }
.modal-img-placeholder {
    width:100%;height:100%;display:flex;align-items:center;justify-content:center;
    font-size:4rem;color:#d1d5db;
    background:linear-gradient(135deg,#f9fafb,#f3f4f6);
}
.modal-close-btn {
    position: absolute; top: 12px; right: 12px;
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(0,0,0,.5); border: none; color: #fff;
    font-size: .9rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s;
}
.modal-close-btn:hover { background: rgba(0,0,0,.75); }

.modal-body { padding: 1.5rem; }
.modal-badges { display: flex; gap: .5rem; margin-bottom: .75rem; flex-wrap: wrap; }
.modal-badge {
    padding: .25rem .7rem; border-radius: 50px;
    font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
}
.modal-badge-cat  { background: #fee2e2; color: var(--primary); }
.modal-badge-food { background: #dcfce7; color: #16a34a; }
.modal-badge-bev  { background: #dbeafe; color: #1d4ed8; }

.modal-name { font-size: 1.4rem; font-weight: 800; color: var(--dark); margin-bottom: .5rem; }
.modal-desc { font-size: .88rem; color: var(--muted); line-height: 1.7; margin-bottom: 1.25rem; }
.modal-price { font-size: 1.6rem; font-weight: 900; color: var(--primary); margin-bottom: 1.5rem; }

.modal-qty-row {
    display: flex; align-items: center; justify-content: space-between;
    background: var(--bg); border-radius: 14px; padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}
.modal-qty-label { font-size: .88rem; font-weight: 600; color: var(--dark); }
.qty-control { display: flex; align-items: center; gap: .75rem; }
.qty-btn {
    width: 36px; height: 36px; border-radius: 50%;
    border: 2px solid var(--border); background: var(--white);
    font-size: 1rem; font-weight: 700; cursor: pointer; color: var(--dark);
    display: flex; align-items: center; justify-content: center;
    transition: all .18s;
}
.qty-btn:hover { border-color: var(--primary); color: var(--primary); background: #fee2e2; }
.qty-btn:disabled { opacity: .35; cursor: not-allowed; }
.qty-value { font-size: 1.1rem; font-weight: 800; min-width: 28px; text-align: center; color: var(--dark); }

.modal-add-btn {
    width: 100%; padding: 1rem; border-radius: 14px;
    background: var(--primary); border: none; color: #fff;
    font-size: 1rem; font-weight: 700; font-family: inherit;
    cursor: pointer; transition: background .2s, transform .15s;
    display: flex; align-items: center; justify-content: center; gap: .65rem;
}
.modal-add-btn:hover { background: var(--primary-dk); }
.modal-add-btn:active { transform: scale(.98); }
.modal-add-btn i { font-size: 1rem; }

/* ══════════════════════════════════════════════
   CART DRAWER
══════════════════════════════════════════════ */
.cart-overlay {
    position: fixed; inset: 0; z-index: 1100;
    background: rgba(0,0,0,.5); backdrop-filter: blur(2px);
    display: none;
}
.cart-overlay.open { display: block; animation: overlayIn .25s ease; }
.cart-drawer {
    position: fixed; right: -420px; top: 0; bottom: 0;
    width: 100%; max-width: 420px; z-index: 1101;
    background: var(--white); box-shadow: -12px 0 40px rgba(0,0,0,.15);
    display: flex; flex-direction: column;
    transition: right .35s cubic-bezier(.4,0,.2,1);
}
.cart-drawer.open { right: 0; }

.cart-drawer-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.cart-drawer-title { font-size: 1.1rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: .55rem; }
.cart-drawer-close {
    width: 36px; height: 36px; border-radius: 50%;
    border: 1.5px solid var(--border); background: none;
    cursor: pointer; font-size: .95rem; color: var(--muted);
    display: flex; align-items: center; justify-content: center;
    transition: all .18s;
}
.cart-drawer-close:hover { border-color: var(--primary); color: var(--primary); background: #fee2e2; }

.cart-items-list { flex: 1; overflow-y: auto; padding: 1rem 1.5rem; }

.cart-empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    height: 100%; padding: 3rem 1rem; color: var(--muted); text-align: center;
}
.cart-empty-icon { font-size: 3.5rem; margin-bottom: 1rem; opacity: .35; }
.cart-empty-title { font-size: 1rem; font-weight: 700; color: var(--dark); margin-bottom: .35rem; }
.cart-empty-sub { font-size: .82rem; }

.cart-item {
    display: flex; align-items: center; gap: .9rem;
    padding: .9rem 0; border-bottom: 1px solid var(--bg);
}
.cart-item-img {
    width: 62px; height: 62px; border-radius: 10px;
    overflow: hidden; background: var(--bg); flex-shrink: 0;
}
.cart-item-img img { width:100%;height:100%;object-fit:cover; }
.cart-item-img-ph { width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#d1d5db; }
.cart-item-info { flex: 1; min-width: 0; }
.cart-item-name { font-size: .86rem; font-weight: 700; color: var(--dark); line-height: 1.3; display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
.cart-item-unit-price { font-size: .75rem; color: var(--muted); margin-top: .2rem; }
.cart-item-controls { display: flex; align-items: center; gap: .4rem; margin-top: .45rem; }
.cart-qty-btn {
    width: 26px; height: 26px; border-radius: 50%;
    border: 1.5px solid var(--border); background: var(--bg);
    font-size: .8rem; cursor: pointer; color: var(--dark);
    display: flex; align-items: center; justify-content: center;
    transition: all .18s;
}
.cart-qty-btn:hover { border-color: var(--primary); color: var(--primary); background: #fee2e2; }
.cart-qty-val { font-size: .86rem; font-weight: 700; min-width: 20px; text-align: center; }
.cart-item-subtotal { font-size: .88rem; font-weight: 700; color: var(--primary); flex-shrink: 0; }
.cart-remove-btn {
    width: 28px; height: 28px; border-radius: 50%; border: none;
    background: none; color: #d1d5db; cursor: pointer; font-size: .85rem;
    transition: color .18s;
}
.cart-remove-btn:hover { color: var(--primary); }

.cart-footer {
    border-top: 1px solid var(--border); padding: 1.25rem 1.5rem;
    flex-shrink: 0;
}
.cart-totals { margin-bottom: 1rem; }
.cart-total-row {
    display: flex; justify-content: space-between; align-items: center;
    font-size: .86rem; color: var(--muted); margin-bottom: .35rem;
}
.cart-total-row.grand {
    font-size: 1.05rem; font-weight: 800; color: var(--dark);
    margin-top: .5rem; padding-top: .5rem; border-top: 1px solid var(--border);
}
.cart-total-row.grand .amt { color: var(--primary); }
.checkout-btn {
    width: 100%; padding: .9rem; border-radius: 14px;
    background: var(--primary); border: none; color: #fff;
    font-size: .95rem; font-weight: 700; font-family: inherit;
    cursor: pointer; transition: background .2s;
    display: flex; align-items: center; justify-content: center; gap: .6rem;
}
.checkout-btn:hover { background: var(--primary-dk); }
.checkout-btn:disabled { opacity: .5; cursor: not-allowed; }

/* ── Floating cart FAB (mobile) ── */
.cart-fab {
    display: none;
    position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 800;
    background: var(--primary); color: #fff;
    border: none; border-radius: 50px; padding: .8rem 1.4rem;
    font-size: .88rem; font-weight: 700; font-family: inherit;
    cursor: pointer; box-shadow: 0 8px 28px rgba(220,38,38,.45);
    align-items: center; gap: .6rem;
    transition: transform .2s;
}
.cart-fab:hover { transform: scale(1.04); }
.cart-fab-count {
    background: #fff; color: var(--primary);
    font-size: .75rem; font-weight: 800;
    width: 22px; height: 22px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
@media (max-width: 900px) { .cart-fab { display: flex; } }

/* ── Skeleton loading ── */
.skeleton {
    background: linear-gradient(90deg, #f3f4f6 25%, #e9eaec 50%, #f3f4f6 75%);
    background-size: 200% 100%;
    animation: shimmer 1.6s infinite linear;
    border-radius: 8px;
}
@keyframes shimmer { 0% { background-position:200% 0 } 100% { background-position:-200% 0 } }
</style>
@endsection

@section('content')

{{-- ══ TOP NAVIGATION ══ --}}
<nav class="top-nav">
    <div class="nav-inner">
        {{-- Logo --}}
        <a href="{{ route('catalog.index') }}" class="nav-logo">
            <div class="nav-logo-icon"><i class="fas fa-utensils"></i></div>
            <div>
                <div class="nav-logo-text">BAB'S RESTO</div>
                <div class="nav-logo-sub">Online Menu</div>
            </div>
        </a>

        {{-- Search --}}
        <form method="GET" action="{{ route('catalog.index') }}" class="nav-search">
            <i class="fas fa-search"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search food, drinks, categories…">
            <button type="submit" class="nav-search-btn">Search</button>
        </form>

        {{-- Actions --}}
        <div class="nav-actions">
            <button class="cart-btn" id="openCartBtn" aria-label="Open cart" title="View Cart">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge" id="cartBadge">{{ $cartCount }}</span>
            </button>

            {{-- Profile dropdown --}}
            <div class="profile-menu">
                @php $navCustomer = auth('customer')->user(); @endphp
                <button class="profile-btn" id="profileBtn" aria-expanded="false">
                    <div class="profile-avatar">{{ $navCustomer->initials }}</div>
                    <span class="profile-name">{{ $navCustomer->first_name }}</span>
                    <i class="fas fa-chevron-down" style="font-size:.7rem;color:var(--muted);"></i>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-header-name">{{ $navCustomer->full_name }}</div>
                        <div class="dropdown-header-email">{{ $navCustomer->email }}</div>
                    </div>
                    <a href="{{ route('account.index') }}" class="dropdown-item">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a href="{{ route('account.index', ['#orders']) }}" class="dropdown-item">
                        <i class="fas fa-receipt"></i> Order History
                    </a>
                    <div style="height:1px;background:var(--border)"></div>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                    </form>
                    <button type="button" class="dropdown-item danger" onclick="openConfirmModal({
                            title: 'Log Out?',
                            desc: 'Are you sure you want to log out of your account?',
                            confirmText: 'Log Out',
                            onConfirm: () => document.getElementById('logoutForm').submit(),
                        })">
                        <i class="fas fa-sign-out-alt"></i> Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- ══ HERO SECTION ══ --}}
<section class="hero">
    <div class="hero-inner">
        <div class="hero-text">
            <div class="hero-tag">
                <i class="fas fa-circle" style="font-size:.5rem;color:#4ade80;"></i>
                Now Serving
            </div>
            <h1 class="hero-title">
                Authentic <span>Filipino</span><br>Flavors, Delivered
            </h1>
            <p class="hero-sub">
                Browse our full menu, add your favorites to the cart, and enjoy a seamless ordering experience right from your table.
            </p>
            <div class="hero-stats">
                <div>
                    <div class="hero-stat-value">{{ $menuItems->count() }}</div>
                    <div class="hero-stat-label">Menu Items</div>
                </div>
                <div>
                    <div class="hero-stat-value">{{ $categories->count() }}</div>
                    <div class="hero-stat-label">Categories</div>
                </div>
                <div>
                    <div class="hero-stat-value" style="color:var(--accent);">₱</div>
                    <div class="hero-stat-label">Best Prices</div>
                </div>
            </div>
        </div>
        <div class="hero-icons" aria-hidden="true">
            <i class="fas fa-bowl-food"></i>
        </div>
    </div>
</section>

{{-- ── Category chips (mobile) ── --}}
<div class="cat-chips-row" id="catChipsMobile">
    <a href="{{ route('catalog.index', array_filter(['q' => request('q')])) }}"
       class="cat-chip {{ !request('category') ? 'active' : '' }}">All</a>
    @foreach($categories as $cat)
        @if($cat->menu_items_count > 0)
        <a href="{{ route('catalog.index', array_filter(['q' => request('q'), 'category' => $cat->id])) }}"
           class="cat-chip {{ request('category') == $cat->id ? 'active' : '' }}">
            {{ $cat->category_name }}
        </a>
        @endif
    @endforeach
</div>

{{-- ══ MAIN CATALOG LAYOUT ══ --}}
<div class="catalog-layout">

    {{-- ── Sidebar: Categories ── --}}
    <aside class="cat-sidebar">
        <div class="cat-sidebar-header">Categories</div>
        <a href="{{ route('catalog.index', array_filter(['q' => request('q')])) }}"
           class="cat-item {{ !request('category') ? 'active' : '' }}">
            <span><i class="fas fa-th-large" style="margin-right:.5rem;opacity:.6;"></i> All Items</span>
            <span class="cat-item-count">{{ $menuItems->count() }}</span>
        </a>
        @foreach($categories as $cat)
            @if($cat->menu_items_count > 0)
            <a href="{{ route('catalog.index', array_filter(['q' => request('q'), 'category' => $cat->id])) }}"
               class="cat-item {{ request('category') == $cat->id ? 'active' : '' }}">
                <span>{{ $cat->category_name }}</span>
                <span class="cat-item-count">{{ $cat->menu_items_count }}</span>
            </a>
            @endif
        @endforeach
    </aside>

    {{-- ── Menu Feed ── --}}
    <main class="menu-feed">

        @if(request('q'))
        <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
            <span style="font-size:.88rem;color:var(--muted);">
                <i class="fas fa-search"></i>
                Showing results for "<strong style="color:var(--dark);">{{ request('q') }}</strong>"
                &mdash; {{ $menuItems->count() }} {{ Str::plural('item', $menuItems->count()) }} found
            </span>
            <a href="{{ route('catalog.index') }}" style="font-size:.8rem;color:var(--primary);font-weight:600;">
                <i class="fas fa-times-circle"></i> Clear
            </a>
        </div>
        @endif

        @if($menuItems->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-search"></i></div>
            <div class="empty-title">No items found</div>
            <p style="font-size:.85rem;max-width:320px;margin:.5rem auto 1.25rem;">
                @if(request('q'))
                    No menu items match "{{ request('q') }}". Try a different keyword.
                @else
                    No menu items are currently available. Please check back soon.
                @endif
            </p>
            @if(request('q') || request('category'))
            <a href="{{ route('catalog.index') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.2rem;border-radius:50px;background:var(--primary);color:#fff;font-size:.85rem;font-weight:600;">
                <i class="fas fa-arrow-left"></i> Browse All
            </a>
            @endif
        </div>

        @elseif(request('category'))
        {{-- Single category view --}}
        @php $cat = $categories->firstWhere('id', request('category')); @endphp
        <div class="section-block">
            <div class="section-heading">
                <i class="section-icon fas fa-utensils"></i>
                <h2>{{ $cat?->category_name ?? 'Items' }}</h2>
                <span class="section-heading-count">{{ $menuItems->count() }}</span>
            </div>
            <div class="menu-grid">
                @foreach($menuItems as $item)
                    @include('customer._menu_card', ['item' => $item])
                @endforeach
            </div>
        </div>

        @elseif(request('q'))
        {{-- Search results: flat grid --}}
        <div class="section-block">
            <div class="section-heading">
                <i class="section-icon fas fa-search"></i>
                <h2>Search Results</h2>
                <span class="section-heading-count">{{ $menuItems->count() }}</span>
            </div>
            <div class="menu-grid">
                @foreach($menuItems as $item)
                    @include('customer._menu_card', ['item' => $item])
                @endforeach
            </div>
        </div>

        @else
        {{-- All categories grouped --}}
        @foreach($categories as $cat)
            @php $items = $itemsByCategory->get($cat->id, collect()); @endphp
            @if($items->isNotEmpty())
            <div class="section-block" id="cat-{{ $cat->id }}">
                <div class="section-heading">
                    <i class="section-icon fas fa-utensils"></i>
                    <h2>{{ $cat->category_name }}</h2>
                    <span class="section-heading-count">{{ $items->count() }}</span>
                </div>
                <div class="menu-grid">
                    @foreach($items as $item)
                        @include('customer._menu_card', ['item' => $item])
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach

        {{-- Items with no category --}}
        @php $uncategorized = $itemsByCategory->get(null, collect()); @endphp
        @if($uncategorized->isNotEmpty())
        <div class="section-block">
            <div class="section-heading">
                <i class="section-icon fas fa-ellipsis-h"></i>
                <h2>Others</h2>
                <span class="section-heading-count">{{ $uncategorized->count() }}</span>
            </div>
            <div class="menu-grid">
                @foreach($uncategorized as $item)
                    @include('customer._menu_card', ['item' => $item])
                @endforeach
            </div>
        </div>
        @endif
        @endif

    </main>
</div>

{{-- ══ ITEM DETAIL MODAL ══ --}}
<div class="modal-overlay" id="itemModal" role="dialog" aria-modal="true" aria-labelledby="modalName">
    <div class="item-modal">
        <div class="modal-img" id="modalImgWrapper">
            <div class="modal-img-placeholder" id="modalImgPlaceholder"><i class="fas fa-utensils"></i></div>
            <img id="modalImg" src="" alt="" style="display:none;">
            <button class="modal-close-btn" id="modalCloseBtn" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-badges">
                <span class="modal-badge modal-badge-cat" id="modalCategory">Category</span>
                <span class="modal-badge" id="modalTypeBadge">Food</span>
            </div>
            <h2 class="modal-name" id="modalName">Item Name</h2>
            <p class="modal-desc" id="itemModalDesc"></p>
            <div class="modal-price" id="modalPrice">₱0.00</div>

            <div class="modal-qty-row">
                <span class="modal-qty-label">Quantity</span>
                <div class="qty-control">
                    <button class="qty-btn" id="qtyDec" aria-label="Decrease">−</button>
                    <span class="qty-value" id="qtyVal">1</span>
                    <button class="qty-btn" id="qtyInc" aria-label="Increase">+</button>
                </div>
            </div>

            <button class="modal-add-btn" id="addToCartBtn">
                <i class="fas fa-cart-plus"></i>
                <span id="addToCartBtnText">Add to Cart — ₱0.00</span>
            </button>
        </div>
    </div>
</div>

{{-- ══ CART DRAWER ══ --}}
<div class="cart-overlay" id="cartOverlay"></div>
<aside class="cart-drawer" id="cartDrawer" aria-label="Your cart">
    <div class="cart-drawer-header">
        <div class="cart-drawer-title">
            <i class="fas fa-shopping-cart" style="color:var(--primary);"></i>
            Your Cart
            <span id="cartDrawerCount" style="background:#fee2e2;color:var(--primary);font-size:.72rem;font-weight:700;padding:.15rem .5rem;border-radius:50px;"></span>
        </div>
        <button class="cart-drawer-close" id="closeCartBtn" aria-label="Close cart">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="cart-items-list" id="cartItemsList">
        <div class="cart-empty-state" id="cartEmptyState">
            <div class="cart-empty-icon"><i class="fas fa-shopping-basket"></i></div>
            <div class="cart-empty-title">Your cart is empty</div>
            <div class="cart-empty-sub">Add items from the menu to get started.</div>
        </div>
    </div>

    <div class="cart-footer" id="cartFooter" style="display:none;">
        <div class="cart-totals">
            <div class="cart-total-row">
                <span>Subtotal</span>
                <span class="amt" id="cartSubtotal">₱0.00</span>
            </div>
            <div class="cart-total-row grand">
                <span>Total</span>
                <span class="amt" id="cartGrandTotal">₱0.00</span>
            </div>
        </div>
        <button class="checkout-btn" id="checkoutBtn">
            <i class="fas fa-receipt"></i> Proceed to Checkout
        </button>
    </div>
</aside>

{{-- ══ FLOATING CART FAB ══ --}}
<button class="cart-fab" id="cartFab" aria-label="Open cart">
    <i class="fas fa-shopping-cart"></i>
    <span>Cart</span>
    <span class="cart-fab-count" id="cartFabCount">{{ $cartCount }}</span>
</button>

@endsection

@php
// Pre-compute cart items array so @json() doesn't choke on closure braces
$initialCartItems = $cartItems->map(function ($ci) {
    $mi = $ci->menuItem;
    return [
        'id'           => $ci->id,
        'menu_item_id' => $ci->menu_item_id,
        'name'         => $mi->menu_name,
        'price'        => (float) $ci->unit_price,
        'quantity'     => $ci->quantity,
        'subtotal'     => (float) $ci->unit_price * $ci->quantity,
        'image'        => $mi->image_url,
    ];
})->values()->all();
@endphp
@section('scripts')
<script>
/* ══════════════════════════════════════════════
   STATE
══════════════════════════════════════════════ */
let cartCount = {{ $cartCount }};
let cartItems = @json($initialCartItems);

let currentItemId   = null;
let currentItemPrice = 0;
let currentQty       = 1;

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const cartBadge       = document.getElementById('cartBadge');
const cartFabCount    = document.getElementById('cartFabCount');
const cartDrawerCount = document.getElementById('cartDrawerCount');

/* ══════════════════════════════════════════════
   PROFILE DROPDOWN
══════════════════════════════════════════════ */
const profileBtn      = document.getElementById('profileBtn');
const profileDropdown = document.getElementById('profileDropdown');
profileBtn.addEventListener('click', () => {
    const open = profileDropdown.classList.toggle('open');
    profileBtn.setAttribute('aria-expanded', open);
});
document.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove('open');
        profileBtn.setAttribute('aria-expanded', 'false');
    }
});

/* ══════════════════════════════════════════════
   CART DRAWER
══════════════════════════════════════════════ */
const cartOverlay = document.getElementById('cartOverlay');
const cartDrawer  = document.getElementById('cartDrawer');

function openCart()  { cartDrawer.classList.add('open'); cartOverlay.classList.add('open'); document.body.style.overflow = 'hidden'; renderCart(); }
function closeCart() { cartDrawer.classList.remove('open'); cartOverlay.classList.remove('open'); document.body.style.overflow = ''; }

document.getElementById('openCartBtn').addEventListener('click', openCart);
document.getElementById('cartFab').addEventListener('click', openCart);
document.getElementById('closeCartBtn').addEventListener('click', closeCart);
cartOverlay.addEventListener('click', closeCart);

document.getElementById('checkoutBtn').addEventListener('click', () => {
    window.location.href = '{{ route('cart.index') }}';
});

/* ══════════════════════════════════════════════
   BADGE UPDATE
══════════════════════════════════════════════ */
function updateBadge(count) {
    cartCount = count;
    [cartBadge, cartFabCount].forEach(el => {
        el.textContent = count;
        if (count > 0) { el.style.display = ''; el.classList.add('bump'); setTimeout(() => el.classList.remove('bump'), 400); }
        else el.style.display = 'none';
    });
    cartDrawerCount.textContent = count > 0 ? `${count} item${count !== 1 ? 's' : ''}` : '';
}
updateBadge(cartCount);

/* ══════════════════════════════════════════════
   RENDER CART
══════════════════════════════════════════════ */
function formatMoney(n) { return '₱' + parseFloat(n).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2}); }

function renderCart() {
    const list    = document.getElementById('cartItemsList');
    const footer  = document.getElementById('cartFooter');
    const subtotal = document.getElementById('cartSubtotal');
    const grand   = document.getElementById('cartGrandTotal');

    if (cartItems.length === 0) {
        list.innerHTML = `
            <div class="cart-empty-state" id="cartEmptyState">
                <div class="cart-empty-icon"><i class="fas fa-shopping-basket"></i></div>
                <div class="cart-empty-title">Your cart is empty</div>
                <div class="cart-empty-sub">Add items from the menu to get started.</div>
            </div>`;
        footer.style.display = 'none';
        updateBadge(0);
        return;
    }

    footer.style.display = 'block';

    const totalAmt = cartItems.reduce((s, ci) => s + ci.price * ci.quantity, 0);
    const totalQty = cartItems.reduce((s, ci) => s + ci.quantity, 0);
    subtotal.textContent = formatMoney(totalAmt);
    grand.textContent    = formatMoney(totalAmt);
    updateBadge(totalQty);

    list.innerHTML = cartItems.map(ci => `
        <div class="cart-item" data-id="${ci.id}">
            <div class="cart-item-img">
                ${ci.image && !ci.image.includes('placeholder')
                    ? `<img src="${ci.image}" alt="${ci.name}" loading="lazy">`
                    : `<div class="cart-item-img-ph"><i class="fas fa-utensils"></i></div>`}
            </div>
            <div class="cart-item-info">
                <div class="cart-item-name">${ci.name}</div>
                <div class="cart-item-unit-price">${formatMoney(ci.price)} each</div>
                <div class="cart-item-controls">
                    <button class="cart-qty-btn" onclick="changeCartQty(${ci.id}, ${ci.quantity - 1})">−</button>
                    <span class="cart-qty-val">${ci.quantity}</span>
                    <button class="cart-qty-btn" onclick="changeCartQty(${ci.id}, ${ci.quantity + 1})">+</button>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.4rem;flex-shrink:0;">
                <div class="cart-item-subtotal">${formatMoney(ci.price * ci.quantity)}</div>
                <button class="cart-remove-btn" onclick="removeCartItem(${ci.id})" title="Remove">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    `).join('');
}

/* ══════════════════════════════════════════════
   CART API CALLS
══════════════════════════════════════════════ */
async function apiPost(url, data = {}) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(data),
    });
    return res.json();
}
async function apiPatch(url, data = {}) {
    const res = await fetch(url, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(data),
    });
    return res.json();
}
async function apiDelete(url) {
    const res = await fetch(url, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    });
    return res.json();
}

async function changeCartQty(cartItemId, newQty) {
    if (newQty < 1) { removeCartItem(cartItemId); return; }
    if (newQty > 99) return;

    try {
        const data = await apiPatch(`/cart/${cartItemId}/update`, { quantity: newQty });
        const ci = cartItems.find(i => i.id === cartItemId);
        if (ci) { ci.quantity = newQty; ci.subtotal = ci.price * newQty; }
        renderCart();
    } catch(e) { showToast('Failed to update quantity.', 'error'); }
}

async function removeCartItem(cartItemId) {
    try {
        await apiDelete(`/cart/${cartItemId}/remove`);
        cartItems = cartItems.filter(i => i.id !== cartItemId);
        renderCart();
        showToast('Item removed from cart.', 'info');
    } catch(e) { showToast('Failed to remove item.', 'error'); }
}

/* ══════════════════════════════════════════════
   ITEM DETAIL MODAL
══════════════════════════════════════════════ */
const modal        = document.getElementById('itemModal');
const modalImg     = document.getElementById('modalImg');
const modalImgPh   = document.getElementById('modalImgPlaceholder');
const modalName    = document.getElementById('modalName');
const modalDesc    = document.getElementById('itemModalDesc');
const modalPrice   = document.getElementById('modalPrice');
const modalCat     = document.getElementById('modalCategory');
const modalTypeBadge = document.getElementById('modalTypeBadge');
const qtyVal       = document.getElementById('qtyVal');
const addToCartBtn = document.getElementById('addToCartBtn');
const addToCartTxt = document.getElementById('addToCartBtnText');

function openItemModal(id, name, desc, price, category, type, image) {
    currentItemId    = id;
    currentItemPrice = parseFloat(price);
    currentQty       = 1;

    if (image && !image.includes('placeholder')) {
        modalImg.src     = image;
        modalImg.alt     = name;
        modalImg.style.display = '';
        modalImgPh.style.display = 'none';
    } else {
        modalImg.style.display = 'none';
        modalImgPh.style.display = '';
    }

    modalName.textContent  = name;
    modalDesc.textContent  = desc || 'No description available.';
    modalPrice.textContent = formatMoney(price);
    modalCat.textContent   = category;

    if (type === 'beverage') {
        modalTypeBadge.textContent  = 'Beverage';
        modalTypeBadge.className    = 'modal-badge modal-badge-bev';
    } else {
        modalTypeBadge.textContent  = 'Food';
        modalTypeBadge.className    = 'modal-badge modal-badge-food';
    }

    qtyVal.textContent = 1;
    updateModalAddBtn();
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    modal.classList.remove('open');
    document.body.style.overflow = '';
    currentItemId = null;
}

function updateModalAddBtn() {
    addToCartTxt.textContent = `Add to Cart — ${formatMoney(currentItemPrice * currentQty)}`;
}

document.getElementById('modalCloseBtn').addEventListener('click', closeModal);
modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

document.getElementById('qtyInc').addEventListener('click', () => {
    if (currentQty < 99) { currentQty++; qtyVal.textContent = currentQty; updateModalAddBtn(); }
});
document.getElementById('qtyDec').addEventListener('click', () => {
    if (currentQty > 1) { currentQty--; qtyVal.textContent = currentQty; updateModalAddBtn(); }
    document.getElementById('qtyDec').disabled = currentQty <= 1;
});

addToCartBtn.addEventListener('click', async () => {
    if (!currentItemId) return;

    addToCartBtn.disabled = true;
    addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding…';

    try {
        const data = await apiPost('/cart/add', { menu_item_id: currentItemId, quantity: currentQty });

        // Refresh cart from server
        const cartData = await fetch('/cart', { headers: { 'Accept': 'application/json' } }).then(r => r.json());
        cartItems = cartData.items;
        updateBadge(cartData.count);
        renderCart();

        closeModal();
        showToast(data.message || 'Added to cart!', 'success');
    } catch(e) {
        showToast('Failed to add item to cart.', 'error');
    } finally {
        addToCartBtn.disabled = false;
        addToCartBtn.innerHTML = `<i class="fas fa-cart-plus"></i><span id="addToCartBtnText">Add to Cart — ${formatMoney(currentItemPrice * currentQty)}</span>`;
    }
});

// Keyboard close
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { closeModal(); closeCart(); }
});

// Initialize
renderCart();
</script>
@endsection
