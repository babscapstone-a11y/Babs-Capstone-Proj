@extends('layouts.customer-app')
@section('title', "Shopping Cart – Bab's Resto")

@section('styles')
<style>
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.fade-up { animation: fadeUp .4s cubic-bezier(.22,1,.36,1) both; }

.cart-wrap { max-width: 980px; margin: 0 auto; }

.page-title {
    font-size: 1.5rem; font-weight: 900; color: var(--dark);
    display: flex; align-items: center; gap: .6rem; margin-bottom: 1.5rem;
}
.page-title i { color: var(--primary); }

.cart-grid { display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start; }
@media (max-width: 860px) { .cart-grid { grid-template-columns: 1fr; } }

.card {
    background: var(--white); border-radius: 18px;
    border: 1px solid var(--border);
    box-shadow: 0 2px 16px rgba(0,0,0,.06);
    overflow: hidden;
}

/* Empty state */
.empty-state {
    padding: 4rem 2rem; text-align: center;
}
.empty-state i { font-size: 3rem; color: var(--border); margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.1rem; font-weight: 700; color: var(--dark); margin-bottom: .4rem; }
.empty-state p { font-size: .88rem; color: var(--muted); margin-bottom: 1.5rem; }

/* Cart items list */
.cart-list-header {
    padding: 1.1rem 1.4rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.cart-list-header h2 { font-size: 1rem; font-weight: 700; color: var(--dark); }
.btn-clear {
    background: none; border: none; color: var(--muted);
    font-size: .8rem; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; gap: .35rem;
    transition: color .15s; font-family: inherit;
}
.btn-clear:hover { color: var(--primary); }

.cart-row {
    display: flex; gap: 1rem; padding: 1.1rem 1.4rem;
    border-bottom: 1px solid #F3F4F6;
    transition: background .2s, opacity .2s;
}
.cart-row:last-child { border-bottom: none; }
.cart-row.removing { opacity: 0; transform: translateX(20px); }

.cart-row-img {
    width: 72px; height: 72px; border-radius: 12px; overflow: hidden;
    flex-shrink: 0; background: #F3F4F6;
    display: flex; align-items: center; justify-content: center;
}
.cart-row-img img { width: 100%; height: 100%; object-fit: cover; }
.cart-row-img i { color: var(--border); font-size: 1.4rem; }

.cart-row-info { flex: 1; min-width: 0; }
.cart-row-name { font-weight: 700; color: var(--dark); font-size: .95rem; }
.cart-row-cat { font-size: .74rem; color: var(--muted); margin-top: .1rem; }
.cart-row-price { font-size: .82rem; color: var(--muted); margin-top: .35rem; }
.cart-row-notes {
    display: block; width: 100%; margin-top: .5rem;
    border: none; border-bottom: 1px dashed var(--border); background: transparent;
    font-family: inherit; font-size: .78rem; color: var(--text); padding: .2rem 0;
    outline: none;
}
.cart-row-notes:focus { border-bottom-color: var(--primary); }
.cart-row-notes::placeholder { color: var(--muted); font-style: italic; }

.cart-row-actions {
    display: flex; flex-direction: column; align-items: flex-end;
    justify-content: space-between; flex-shrink: 0; gap: .5rem;
}
.cart-row-subtotal { font-weight: 800; color: var(--dark); font-size: 1rem; }
.qty-control {
    display: flex; align-items: center; gap: .5rem;
    background: var(--bg); border-radius: 50px; padding: .25rem;
}
.qty-btn {
    width: 26px; height: 26px; border-radius: 50%; border: none;
    background: var(--white); color: var(--dark); cursor: pointer;
    font-size: .9rem; font-weight: 700; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 1px 3px rgba(0,0,0,.1); transition: transform .15s;
}
.qty-btn:hover { background: var(--primary); color: #fff; }
.qty-btn:active { transform: scale(.9); }
.qty-val { font-weight: 700; font-size: .85rem; min-width: 18px; text-align: center; }
.qty-val.bump { animation: bump .3s ease; }
@keyframes bump { 0%,100% { transform: scale(1); } 50% { transform: scale(1.3); } }

.btn-remove {
    background: none; border: none; color: var(--muted);
    font-size: .76rem; cursor: pointer; display: flex; align-items: center; gap: .3rem;
    transition: color .15s;
}
.btn-remove:hover { color: var(--primary); }

/* Summary card */
.summary-card { position: sticky; top: calc(var(--nav-h) + 1.5rem); }
.summary-card .card-body { padding: 1.4rem; }
.summary-card h2 { font-size: 1rem; font-weight: 700; color: var(--dark); margin-bottom: 1rem; }
.summary-row { display: flex; justify-content: space-between; padding: .5rem 0; font-size: .87rem; color: var(--text); }
.summary-row.total {
    border-top: 2px solid var(--border); margin-top: .5rem; padding-top: .9rem;
    font-size: 1.15rem; font-weight: 800; color: var(--dark);
}
.summary-row.total .amt { color: var(--primary); }

.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
    padding: .8rem 1.25rem; border-radius: 12px;
    font-size: .9rem; font-weight: 700; font-family: inherit;
    cursor: pointer; border: none; transition: all .18s; text-decoration: none;
    width: 100%;
}
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover { background: var(--primary-dk); transform: translateY(-1px); }
.btn-outline { background: var(--white); border: 1.5px solid var(--border); color: var(--text); }
.btn-outline:hover { border-color: var(--primary); color: var(--primary); }
.btn-row { display: flex; flex-direction: column; gap: .6rem; margin-top: 1.1rem; }
</style>
@endsection

@section('content')
<div class="page-wrap cart-wrap">
    <div class="page-title fade-up"><i class="fas fa-shopping-cart"></i> Shopping Cart</div>

    @if(! $cart || $cart->items->isEmpty())
        <div class="card fade-up">
            <div class="empty-state">
                <i class="fas fa-shopping-basket"></i>
                <h3>Your cart is empty.</h3>
                <p>Looks like you haven't added anything yet. Let's fix that!</p>
                <a href="{{ route('catalog.index') }}" class="btn btn-primary" style="width:auto;display:inline-flex;padding:.8rem 1.75rem">
                    <i class="fas fa-utensils"></i> Browse Menu
                </a>
            </div>
        </div>
    @else
        <div class="cart-grid">
            {{-- Items --}}
            <div class="card fade-up">
                <div class="cart-list-header">
                    <h2>Order Summary <span style="color:var(--muted);font-weight:500" id="itemCountLabel">({{ $cart->item_count }} {{ Str::plural('item', $cart->item_count) }})</span></h2>
                    <button class="btn-clear" id="clearCartBtn"><i class="fas fa-trash-alt"></i> Clear Cart</button>
                </div>
                <div id="cartRows">
                    @foreach($cart->items as $item)
                    @php $mi = $item->menuItem; @endphp
                    <div class="cart-row" id="cart-row-{{ $item->id }}" data-id="{{ $item->id }}" data-price="{{ $item->unit_price }}">
                        <div class="cart-row-img">
                            @if($mi->image_url && !str_contains($mi->image_url, 'placeholder'))
                                <img src="{{ $mi->image_url }}" alt="{{ $mi->menu_name }}">
                            @else
                                <i class="fas fa-utensils"></i>
                            @endif
                        </div>
                        <div class="cart-row-info">
                            <div class="cart-row-name">{{ $mi->menu_name }}</div>
                            <div class="cart-row-cat">{{ $mi->category?->category_name ?? 'Uncategorized' }}</div>
                            <div class="cart-row-price">₱{{ number_format($item->unit_price, 2) }} each</div>
                            <input type="text" class="cart-row-notes" id="notes-{{ $item->id }}"
                                   placeholder="Add a note (e.g. no onions)"
                                   value="{{ $item->notes }}"
                                   maxlength="255"
                                   onblur="saveNotes({{ $item->id }}, this.value)">
                        </div>
                        <div class="cart-row-actions">
                            <div class="cart-row-subtotal" id="subtotal-{{ $item->id }}">₱{{ number_format($item->unit_price * $item->quantity, 2) }}</div>
                            <div class="qty-control">
                                <button class="qty-btn" onclick="changeQty({{ $item->id }}, {{ $item->quantity - 1 }})">−</button>
                                <span class="qty-val" id="qty-{{ $item->id }}">{{ $item->quantity }}</span>
                                <button class="qty-btn" onclick="changeQty({{ $item->id }}, {{ $item->quantity + 1 }})">+</button>
                            </div>
                            <button class="btn-remove" onclick="removeItem({{ $item->id }})"><i class="fas fa-trash-alt"></i> Remove</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Summary --}}
            <div class="card summary-card fade-up">
                <div class="card-body">
                    <h2>Total</h2>
                    <div class="summary-row"><span>Total Items</span><span id="totalItems">{{ $cart->item_count }}</span></div>
                    <div class="summary-row total"><span>Grand Total</span><span class="amt" id="grandTotal">₱{{ number_format($cart->total, 2) }}</span></div>

                    <div class="btn-row">
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary" id="checkoutBtn">
                            <i class="fas fa-receipt"></i> Proceed to Checkout
                        </a>
                        <a href="{{ route('catalog.index') }}" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Continue Ordering
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function formatMoney(n) { return '₱' + parseFloat(n).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2}); }

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

function recalcItemCount() {
    return document.querySelectorAll('.cart-row').length;
}

function updateTotals(grandTotal, count) {
    document.getElementById('grandTotal').textContent = formatMoney(grandTotal);
    document.getElementById('totalItems').textContent = count;
    document.getElementById('itemCountLabel').textContent = `(${count} ${count === 1 ? 'item' : 'items'})`;

    const navBadge = document.getElementById('navCartBadge');
    if (navBadge) {
        navBadge.textContent = count > 99 ? '99+' : count;
        navBadge.classList.toggle('hidden', count <= 0);
    }

    if (count <= 0) {
        setTimeout(() => window.location.reload(), 350);
    }
}

async function changeQty(cartItemId, newQty) {
    if (newQty < 1) { removeItem(cartItemId); return; }
    if (newQty > 99) return;

    try {
        const data = await apiPatch(`/cart/${cartItemId}/update`, { quantity: newQty });
        const row = document.getElementById(`cart-row-${cartItemId}`);
        const price = parseFloat(row.dataset.price);
        document.getElementById(`qty-${cartItemId}`).textContent = newQty;
        document.getElementById(`qty-${cartItemId}`).classList.add('bump');
        setTimeout(() => document.getElementById(`qty-${cartItemId}`).classList.remove('bump'), 300);
        document.getElementById(`subtotal-${cartItemId}`).textContent = formatMoney(price * newQty);
        updateTotals(data.total, data.count);
    } catch (e) {
        showToast('Failed to update quantity.', 'error');
    }
}

async function saveNotes(cartItemId, notes) {
    try {
        await apiPatch(`/cart/${cartItemId}/update`, { notes });
        showToast('Note saved.', 'info');
    } catch (e) {
        showToast('Failed to save note.', 'error');
    }
}

function removeItem(cartItemId) {
    if (! confirm('Remove this item from your cart?')) return;
    doRemove(cartItemId);
}

async function doRemove(cartItemId) {
    const row = document.getElementById(`cart-row-${cartItemId}`);
    row.classList.add('removing');
    try {
        const data = await apiDelete(`/cart/${cartItemId}/remove`);
        setTimeout(() => {
            row.remove();
            updateTotals(data.total, data.count);
        }, 200);
        showToast('Item removed from cart.', 'info');
    } catch (e) {
        row.classList.remove('removing');
        showToast('Failed to remove item.', 'error');
    }
}

document.getElementById('clearCartBtn')?.addEventListener('click', async () => {
    if (! confirm('Clear all items from your cart? This cannot be undone.')) return;
    try {
        await fetch('/cart/clear', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        window.location.reload();
    } catch (e) {
        showToast('Failed to clear cart.', 'error');
    }
});
</script>
@endsection
