@extends('layouts.table-server')

@section('title', 'Take Order')

@section('styles')
<style>
    /* ── Layout ──────────────────────────────────────────── */
    .ts-layout {
        display: grid; grid-template-columns: 1fr 380px; gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 1000px) { .ts-layout { grid-template-columns: 1fr; } }

    /* ── Search + category chips ────────────────────────── */
    .ts-search-row { margin-bottom: 1.1rem; }
    .ts-search {
        display: flex; align-items: center; gap: .6rem;
        background: var(--white); border: 1.5px solid var(--border);
        border-radius: 50px; padding: 0 .3rem 0 1.1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-bottom: .9rem;
    }
    .ts-search:focus-within { border-color: var(--primary); }
    .ts-search i { color: var(--muted); }
    .ts-search input {
        flex: 1; border: none; outline: none; background: transparent;
        font-family: inherit; font-size: .9rem; padding: .7rem 0;
    }
    .ts-search button {
        padding: .55rem 1.2rem; border-radius: 50px; border: none;
        background: var(--primary); color: #fff; font-weight: 600; font-size: .84rem;
        font-family: inherit; cursor: pointer;
    }
    .ts-chips-row { display: flex; gap: .55rem; overflow-x: auto; padding-bottom: .3rem; }
    .ts-chips-row::-webkit-scrollbar { height: 5px; }
    .ts-chip {
        flex-shrink: 0; padding: .42rem 1.1rem; border-radius: 50px;
        border: 1.5px solid var(--border); background: var(--white);
        font-size: .81rem; font-weight: 600; color: var(--dark);
        transition: all .2s; white-space: nowrap;
    }
    .ts-chip.active, .ts-chip:hover { background: var(--primary); border-color: var(--primary); color: #fff; }

    /* ── Menu feed / grid (mirrors customer catalog) ────── */
    .section-block { margin-bottom: 2rem; }
    .section-heading {
        display: flex; align-items: center; gap: .65rem;
        margin-bottom: 1rem; padding-bottom: .6rem; border-bottom: 2px solid var(--border);
    }
    .section-heading h2 { font-size: 1rem; font-weight: 800; color: var(--dark); margin: 0; }
    .section-heading-count { background: var(--white); color: var(--muted); font-size: .72rem; font-weight: 700; padding: .18rem .55rem; border-radius: 50px; border: 1px solid var(--border); }
    .section-icon { color: var(--primary); }

    .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 1rem; }
    .menu-card {
        background: var(--white); border-radius: 14px; border: 1px solid var(--border);
        overflow: hidden; cursor: pointer; transition: transform .2s, box-shadow .2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .menu-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    .menu-card-disabled { cursor: default; opacity: .68; }
    .menu-card-disabled:hover { transform: none; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }

    .card-img { width: 100%; aspect-ratio: 4/3; background: var(--bg); position: relative; overflow: hidden; }
    .card-img img { width: 100%; height: 100%; object-fit: cover; }
    .card-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #d1d5db; background: linear-gradient(135deg,#f9fafb,#f3f4f6); }
    .card-type-badge { position: absolute; top: 8px; left: 8px; padding: .2rem .55rem; border-radius: 50px; font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .badge-food { background: rgba(34,197,94,.85); color: #fff; }
    .badge-beverage { background: rgba(59,130,246,.85); color: #fff; }
    .card-unavailable-overlay {
        position: absolute; inset: 0; background: rgba(17,24,39,0.55);
        display: flex; align-items: center; justify-content: center; padding: .5rem;
    }
    .badge { display: inline-flex; align-items: center; gap: .3rem; border-radius: 50px; font-size: .68rem; font-weight: 700; padding: .22rem .6rem; white-space: nowrap; }
    .badge-avail-no { background: rgba(255,255,255,0.92); color: #4B5563; }

    .card-body { padding: .8rem; }
    .card-category { font-size: .68rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: .04em; margin-bottom: .2rem; }
    .card-name { font-size: .88rem; font-weight: 700; color: var(--dark); line-height: 1.3; margin: 0 0 .25rem; }
    .card-desc { font-size: .74rem; color: var(--muted); line-height: 1.45; margin: 0 0 .6rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .card-footer { display: flex; align-items: center; justify-content: space-between; }
    .card-price { font-size: .96rem; font-weight: 800; color: var(--primary); }
    .add-btn {
        width: 32px; height: 32px; border-radius: 50%; background: var(--primary); border: none; color: #fff;
        font-size: .88rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: background .2s, transform .15s;
    }
    .add-btn:hover { background: var(--primary-dk); transform: scale(1.08); }
    .add-btn-disabled { background: rgba(107,114,128,0.25); color: #6B7280; cursor: not-allowed; }
    .add-btn-disabled:hover { transform: none; background: rgba(107,114,128,0.25); }

    .empty-state { text-align: center; padding: 3.5rem 1.5rem; color: var(--muted); background: var(--white); border-radius: 14px; border: 1px solid var(--border); }
    .empty-state i { font-size: 2.5rem; margin-bottom: .8rem; opacity: .4; }

    /* ── Order Summary panel ─────────────────────────────── */
    .order-panel {
        background: var(--white); border-radius: 16px; border: 1px solid var(--border);
        box-shadow: 0 4px 18px rgba(0,0,0,0.06); position: sticky; top: 90px;
        display: flex; flex-direction: column; max-height: calc(100vh - 110px);
    }
    .order-panel-header { padding: 1.1rem 1.25rem; border-bottom: 1px solid var(--border); }
    .order-panel-title { font-size: 1rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: .5rem; }
    .table-number-row { margin-top: .8rem; }
    .table-number-label { font-size: .78rem; font-weight: 600; color: var(--muted); margin-bottom: .35rem; display: block; }
    .table-number-input {
        width: 100%; padding: .6rem .8rem; border-radius: 10px; border: 1.5px solid var(--border);
        font-family: inherit; font-size: .95rem; font-weight: 700; color: var(--dark); outline: none;
    }
    .table-number-input:focus { border-color: var(--primary); }

    .order-items-list { flex: 1; overflow-y: auto; padding: .6rem 1.25rem; }
    .order-empty { text-align: center; color: var(--muted); padding: 2.5rem 1rem; }
    .order-empty i { font-size: 2.2rem; opacity: .35; margin-bottom: .6rem; }

    .order-line { padding: .8rem 0; border-bottom: 1px solid var(--bg); }
    .order-line-top { display: flex; justify-content: space-between; gap: .5rem; }
    .order-line-name { font-size: .87rem; font-weight: 700; color: var(--dark); }
    .order-line-subtotal { font-size: .87rem; font-weight: 700; color: var(--primary); flex-shrink: 0; }
    .order-line-unit { font-size: .72rem; color: var(--muted); margin-top: .1rem; }
    .order-line-controls { display: flex; align-items: center; justify-content: space-between; margin-top: .5rem; }
    .qty-mini { display: flex; align-items: center; gap: .5rem; }
    .qty-mini-btn {
        width: 26px; height: 26px; border-radius: 50%; border: 1.5px solid var(--border); background: var(--bg);
        cursor: pointer; font-size: .82rem; color: var(--dark); display: flex; align-items: center; justify-content: center;
    }
    .qty-mini-btn:hover { border-color: var(--primary); color: var(--primary); }
    .qty-mini-val { font-size: .85rem; font-weight: 700; min-width: 18px; text-align: center; }
    .order-line-remove { background: none; border: none; color: #d1d5db; cursor: pointer; font-size: .85rem; }
    .order-line-remove:hover { color: var(--primary); }
    .order-line-notes {
        margin-top: .5rem; width: 100%; border: 1px dashed var(--border); border-radius: 8px;
        padding: .4rem .6rem; font-size: .76rem; font-family: inherit; color: var(--dark); outline: none;
    }
    .order-line-notes:focus { border-color: var(--primary); border-style: solid; }

    .order-panel-footer { padding: 1.1rem 1.25rem; border-top: 1px solid var(--border); }
    .order-total-row { display: flex; justify-content: space-between; align-items: center; font-size: 1.05rem; font-weight: 800; color: var(--dark); margin-bottom: .9rem; }
    .order-total-row .amt { color: var(--primary); }
    .order-actions { display: flex; gap: .6rem; }
    .btn-clear-order {
        flex: 1; padding: .7rem; border-radius: 10px; border: 1.5px solid var(--border);
        background: var(--white); color: var(--dark); font-weight: 600; font-size: .86rem;
        font-family: inherit; cursor: pointer; transition: all .2s;
    }
    .btn-clear-order:hover { border-color: var(--primary); color: var(--primary); }
    .btn-submit-order {
        flex: 2; padding: .7rem; border-radius: 10px; border: none;
        background: var(--primary); color: #fff; font-weight: 700; font-size: .88rem;
        font-family: inherit; cursor: pointer; transition: background .2s;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .btn-submit-order:hover { background: var(--primary-dk); }
    .btn-submit-order:disabled { opacity: .5; cursor: not-allowed; }

    /* ── Item detail modal ──────────────────────────────── */
    .item-modal {
        background: var(--white); border-radius: 20px; width: 100%; max-width: 560px;
        max-height: 90vh; overflow-y: auto; box-shadow: 0 24px 64px rgba(0,0,0,0.2);
    }
    .modal-img { width: 100%; aspect-ratio: 16/9; background: var(--bg); position: relative; border-radius: 20px 20px 0 0; overflow: hidden; }
    .modal-img img { width: 100%; height: 100%; object-fit: cover; }
    .modal-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3.5rem; color: #d1d5db; background: linear-gradient(135deg,#f9fafb,#f3f4f6); }
    .modal-close-btn { position: absolute; top: 12px; right: 12px; width: 34px; height: 34px; border-radius: 50%; background: rgba(0,0,0,.5); border: none; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .modal-body { padding: 1.5rem; }
    .modal-badges { display: flex; gap: .5rem; margin-bottom: .7rem; }
    .modal-badge { padding: .22rem .65rem; border-radius: 50px; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .modal-badge-cat { background: #fee2e2; color: var(--primary); }
    .modal-badge-food { background: #dcfce7; color: #16a34a; }
    .modal-badge-bev { background: #dbeafe; color: #1d4ed8; }
    .modal-name { font-size: 1.3rem; font-weight: 800; color: var(--dark); margin: 0 0 .4rem; }
    .modal-desc { font-size: .86rem; color: var(--muted); line-height: 1.6; margin: 0 0 1rem; }
    .modal-price { font-size: 1.4rem; font-weight: 900; color: var(--primary); margin-bottom: 1.2rem; }
    .modal-qty-row { display: flex; align-items: center; justify-content: space-between; background: var(--bg); border-radius: 12px; padding: .9rem 1.1rem; margin-bottom: 1rem; }
    .modal-qty-label { font-size: .86rem; font-weight: 600; color: var(--dark); }
    .qty-control { display: flex; align-items: center; gap: .7rem; }
    .qty-btn { width: 34px; height: 34px; border-radius: 50%; border: 2px solid var(--border); background: var(--white); font-size: .95rem; font-weight: 700; cursor: pointer; color: var(--dark); display: flex; align-items: center; justify-content: center; }
    .qty-btn:hover { border-color: var(--primary); color: var(--primary); }
    .qty-btn:disabled { opacity: .35; cursor: not-allowed; }
    .qty-value { font-size: 1.05rem; font-weight: 800; min-width: 26px; text-align: center; }
    .modal-notes-label { font-size: .84rem; font-weight: 600; color: var(--dark); margin-bottom: .4rem; display: block; }
    .modal-notes-input {
        width: 100%; border: 1.5px solid var(--border); border-radius: 10px; padding: .65rem .8rem;
        font-family: inherit; font-size: .86rem; resize: vertical; min-height: 60px; margin-bottom: 1.1rem; outline: none;
    }
    .modal-notes-input:focus { border-color: var(--primary); }
    .modal-add-btn {
        width: 100%; padding: .9rem; border-radius: 12px; background: var(--primary); border: none; color: #fff;
        font-size: .95rem; font-weight: 700; font-family: inherit; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: .6rem;
    }
    .modal-add-btn:hover { background: var(--primary-dk); }
</style>
@endsection

@section('content')

<div class="ts-layout">

    {{-- ═══ LEFT: Menu Browsing ═══ --}}
    <div>
        <div class="ts-search-row">
            <form method="GET" action="{{ route('table-server.index') }}" class="ts-search">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search menu by name or category…">
                <button type="submit">Search</button>
            </form>

            <div class="ts-chips-row">
                <a href="{{ route('table-server.index', array_filter(['q' => request('q')])) }}"
                   class="ts-chip {{ !request('category') ? 'active' : '' }}">All Items</a>
                @foreach($categories as $cat)
                    @if($cat->menu_items_count > 0)
                    <a href="{{ route('table-server.index', array_filter(['q' => request('q'), 'category' => $cat->id])) }}"
                       class="ts-chip {{ request('category') == $cat->id ? 'active' : '' }}">
                        {{ $cat->category_name }}
                    </a>
                    @endif
                @endforeach
            </div>
        </div>

        @if($menuItems->isEmpty())
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <div style="font-weight:700;color:var(--dark);margin-bottom:.3rem">No items found</div>
            <p style="font-size:.85rem">
                @if(request('q'))
                    No menu items match "{{ request('q') }}".
                @else
                    No menu items are currently set up.
                @endif
            </p>
        </div>

        @elseif(request('category'))
        @php $cat = $categories->firstWhere('id', request('category')); @endphp
        <div class="section-block">
            <div class="section-heading">
                <i class="section-icon fas fa-utensils"></i>
                <h2>{{ $cat?->category_name ?? 'Items' }}</h2>
                <span class="section-heading-count">{{ $menuItems->count() }}</span>
            </div>
            <div class="menu-grid">
                @foreach($menuItems as $item)
                    @include('table-server._menu_card', ['item' => $item])
                @endforeach
            </div>
        </div>

        @elseif(request('q'))
        <div class="section-block">
            <div class="section-heading">
                <i class="section-icon fas fa-search"></i>
                <h2>Search Results</h2>
                <span class="section-heading-count">{{ $menuItems->count() }}</span>
            </div>
            <div class="menu-grid">
                @foreach($menuItems as $item)
                    @include('table-server._menu_card', ['item' => $item])
                @endforeach
            </div>
        </div>

        @else
        @foreach($categories as $cat)
            @php $items = $itemsByCategory->get($cat->id, collect()); @endphp
            @if($items->isNotEmpty())
            <div class="section-block">
                <div class="section-heading">
                    <i class="section-icon fas fa-utensils"></i>
                    <h2>{{ $cat->category_name }}</h2>
                    <span class="section-heading-count">{{ $items->count() }}</span>
                </div>
                <div class="menu-grid">
                    @foreach($items as $item)
                        @include('table-server._menu_card', ['item' => $item])
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach

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
                    @include('table-server._menu_card', ['item' => $item])
                @endforeach
            </div>
        </div>
        @endif
        @endif
    </div>

    {{-- ═══ RIGHT: Order Summary ═══ --}}
    <div class="order-panel">
        <div class="order-panel-header">
            <div class="order-panel-title"><i class="fas fa-receipt" style="color:var(--primary)"></i> Current Order</div>
            <div class="table-number-row">
                <label class="table-number-label" for="tableNumberInput">Table Card Number</label>
                <input type="number" id="tableNumberInput" class="table-number-input" min="1" max="999" placeholder="e.g. 12">
            </div>
        </div>

        <div class="order-items-list" id="orderItemsList">
            <div class="order-empty" id="orderEmptyState">
                <i class="fas fa-utensils"></i>
                <div>No items selected yet.<br>Tap a menu item to add it.</div>
            </div>
        </div>

        <div class="order-panel-footer">
            <div class="order-total-row">
                <span>Grand Total</span>
                <span class="amt" id="orderGrandTotal">₱0.00</span>
            </div>
            <div class="order-actions">
                <button type="button" class="btn-clear-order" id="clearOrderBtn">Clear Order</button>
                <button type="button" class="btn-submit-order" id="submitOrderBtn" disabled>
                    <i class="fas fa-paper-plane"></i> Submit Order
                </button>
            </div>
        </div>
    </div>

</div>

{{-- ═══ Item Detail Modal ═══ --}}
<div class="modal-overlay" id="itemModal" role="dialog" aria-modal="true" aria-labelledby="modalName">
    <div class="item-modal">
        <div class="modal-img">
            <div class="modal-img-placeholder" id="modalImgPlaceholder"><i class="fas fa-utensils"></i></div>
            <img id="modalImg" src="" alt="" style="display:none;">
            <button class="modal-close-btn" id="modalCloseBtn" aria-label="Close"><i class="fas fa-times"></i></button>
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

            <label class="modal-notes-label" for="modalNotesInput">Special Instructions (optional)</label>
            <textarea id="modalNotesInput" class="modal-notes-input" placeholder="e.g. Less spicy, no onion, extra rice"></textarea>

            <button class="modal-add-btn" id="addToOrderBtn">
                <i class="fas fa-cart-plus"></i>
                <span id="addToOrderBtnText">Add to Order — ₱0.00</span>
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    /* ══ STATE ══ */
    let orderItems = []; // { menu_item_id, name, price, quantity, notes }
    let currentItemId = null, currentItemName = '', currentItemPrice = 0, currentQty = 1;

    function formatMoney(n) { return '₱' + parseFloat(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    /* ══ ITEM DETAIL MODAL ══ */
    const modal = document.getElementById('itemModal');
    const modalImg = document.getElementById('modalImg');
    const modalImgPh = document.getElementById('modalImgPlaceholder');
    const modalName = document.getElementById('modalName');
    const modalDesc = document.getElementById('itemModalDesc');
    const modalPrice = document.getElementById('modalPrice');
    const modalCat = document.getElementById('modalCategory');
    const modalTypeBadge = document.getElementById('modalTypeBadge');
    const qtyVal = document.getElementById('qtyVal');
    const notesInput = document.getElementById('modalNotesInput');
    const addToOrderBtn = document.getElementById('addToOrderBtn');
    const addToOrderTxt = document.getElementById('addToOrderBtnText');

    function openItemModal(id, name, desc, price, category, type, image) {
        currentItemId = id;
        currentItemName = name;
        currentItemPrice = parseFloat(price);
        currentQty = 1;
        notesInput.value = '';

        if (image && !image.includes('placeholder')) {
            modalImg.src = image; modalImg.alt = name;
            modalImg.style.display = ''; modalImgPh.style.display = 'none';
        } else {
            modalImg.style.display = 'none'; modalImgPh.style.display = '';
        }

        modalName.textContent = name;
        modalDesc.textContent = desc || 'No description available.';
        modalPrice.textContent = formatMoney(price);
        modalCat.textContent = category;

        if (type === 'beverage') {
            modalTypeBadge.textContent = 'Beverage'; modalTypeBadge.className = 'modal-badge modal-badge-bev';
        } else {
            modalTypeBadge.textContent = 'Food'; modalTypeBadge.className = 'modal-badge modal-badge-food';
        }

        qtyVal.textContent = 1;
        updateModalAddBtn();
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeItemModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
        currentItemId = null;
    }

    function updateModalAddBtn() {
        addToOrderTxt.textContent = `Add to Order — ${formatMoney(currentItemPrice * currentQty)}`;
    }

    document.getElementById('modalCloseBtn').addEventListener('click', closeItemModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeItemModal(); });

    document.getElementById('qtyInc').addEventListener('click', () => {
        if (currentQty < 99) { currentQty++; qtyVal.textContent = currentQty; updateModalAddBtn(); }
    });
    document.getElementById('qtyDec').addEventListener('click', () => {
        if (currentQty > 1) { currentQty--; qtyVal.textContent = currentQty; updateModalAddBtn(); }
        document.getElementById('qtyDec').disabled = currentQty <= 1;
    });

    addToOrderBtn.addEventListener('click', () => {
        if (!currentItemId) return;

        orderItems.push({
            menu_item_id: currentItemId,
            name: currentItemName,
            price: currentItemPrice,
            quantity: currentQty,
            notes: notesInput.value.trim() || null,
        });

        renderOrderPanel();
        closeItemModal();
        showToast(`${currentItemName} added to order.`, 'success');
    });

    /* ══ ORDER SUMMARY PANEL ══ */
    function renderOrderPanel() {
        const list = document.getElementById('orderItemsList');
        const submitBtn = document.getElementById('submitOrderBtn');
        const tableNumber = document.getElementById('tableNumberInput').value;

        if (orderItems.length === 0) {
            list.innerHTML = `
                <div class="order-empty" id="orderEmptyState">
                    <i class="fas fa-utensils"></i>
                    <div>No items selected yet.<br>Tap a menu item to add it.</div>
                </div>`;
        } else {
            list.innerHTML = orderItems.map((it, idx) => `
                <div class="order-line">
                    <div class="order-line-top">
                        <span class="order-line-name">${it.name}</span>
                        <span class="order-line-subtotal">${formatMoney(it.price * it.quantity)}</span>
                    </div>
                    <div class="order-line-unit">${formatMoney(it.price)} each</div>
                    <div class="order-line-controls">
                        <div class="qty-mini">
                            <button type="button" class="qty-mini-btn" onclick="changeOrderQty(${idx}, -1)">−</button>
                            <span class="qty-mini-val">${it.quantity}</span>
                            <button type="button" class="qty-mini-btn" onclick="changeOrderQty(${idx}, 1)">+</button>
                        </div>
                        <button type="button" class="order-line-remove" onclick="removeOrderItem(${idx})" title="Remove">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <textarea class="order-line-notes" placeholder="Special instructions (optional)"
                        onblur="updateOrderNotes(${idx}, this.value)">${it.notes || ''}</textarea>
                </div>
            `).join('');
        }

        const grandTotal = orderItems.reduce((s, it) => s + it.price * it.quantity, 0);
        document.getElementById('orderGrandTotal').textContent = formatMoney(grandTotal);
        submitBtn.disabled = orderItems.length === 0 || !tableNumber;
    }

    function changeOrderQty(idx, delta) {
        const newQty = orderItems[idx].quantity + delta;
        if (newQty < 1) { removeOrderItem(idx); return; }
        if (newQty > 99) return;
        orderItems[idx].quantity = newQty;
        renderOrderPanel();
    }

    function removeOrderItem(idx) {
        orderItems.splice(idx, 1);
        renderOrderPanel();
    }

    function updateOrderNotes(idx, value) {
        if (orderItems[idx]) orderItems[idx].notes = value.trim() || null;
    }

    document.getElementById('tableNumberInput').addEventListener('input', renderOrderPanel);

    document.getElementById('clearOrderBtn').addEventListener('click', () => {
        if (orderItems.length === 0) return;
        openConfirmModal({
            title: 'Clear this order?',
            desc: 'All selected items will be removed. This cannot be undone.',
            confirmText: 'Clear Order',
            onConfirm: () => {
                orderItems = [];
                renderOrderPanel();
                closeConfirmModal();
                showToast('Order cleared.', 'info');
            },
        });
    });

    document.getElementById('submitOrderBtn').addEventListener('click', () => {
        if (orderItems.length === 0) return;
        const tableNumber = document.getElementById('tableNumberInput').value;
        if (!tableNumber) { showToast('Please enter a Table Card Number.', 'error'); return; }

        openConfirmModal({
            title: 'Submit this order?',
            desc: 'Are you sure you want to submit this order to the kitchen?',
            confirmText: 'Submit Order',
            onConfirm: submitOrder,
        });
    });

    async function submitOrder() {
        const submitBtn = document.getElementById('submitOrderBtn');
        const tableNumber = document.getElementById('tableNumberInput').value;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';

        try {
            const res = await fetch("{{ route('table-server.orders.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify({
                    table_number: tableNumber,
                    items: orderItems.map(it => ({ menu_item_id: it.menu_item_id, quantity: it.quantity, notes: it.notes })),
                }),
            });
            const data = await res.json();

            closeConfirmModal();

            if (!res.ok) {
                const msg = data.errors?.table_number?.[0] || data.message || 'Failed to submit order.';
                showToast(msg, 'error');
                // Keep the built order intact so the server can fix the table number and retry.
                return;
            }

            showToast(data.message, 'success');
            orderItems = [];
            document.getElementById('tableNumberInput').value = '';
            renderOrderPanel();
        } catch (e) {
            closeConfirmModal();
            showToast('Failed to submit order. Please try again.', 'error');
        } finally {
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Order';
            renderOrderPanel();
        }
    }

    // Initial paint
    renderOrderPanel();
</script>
@endsection
