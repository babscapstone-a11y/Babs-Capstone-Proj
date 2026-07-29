@extends('layouts.cashier')

@section('title', 'Billing')

@section('styles')
<style>
    .billing-layout { display: grid; grid-template-columns: 1.3fr 1fr; gap: 1.25rem; align-items: start; }

    /* ── Left panel ──────────────────────────────────────────── */
    .search-bar { display: flex; gap: .6rem; margin-bottom: 1rem; }
    .search-input {
        flex: 1; border: 1.5px solid rgba(17,24,39,0.1); border-radius: 10px;
        padding: .68rem .9rem; font-size: .88rem; font-family: inherit; outline: none; transition: all .2s;
    }
    .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(220,38,38,0.08); }
    .search-select {
        border: 1.5px solid rgba(17,24,39,0.1); border-radius: 10px;
        padding: .68rem .7rem; font-size: .86rem; font-family: inherit; background: var(--white); outline: none; cursor: pointer;
    }

    .order-result {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .85rem 1rem; border-radius: 12px; border: 1.5px solid var(--border);
        margin-bottom: .6rem; cursor: pointer; transition: all .15s;
    }
    .order-result:hover { border-color: var(--primary); background: rgba(220,38,38,0.03); }
    .order-result.selected { border-color: var(--primary); background: rgba(220,38,38,0.06); }
    .order-result-number { font-weight: 800; color: var(--dark); font-size: .95rem; }
    .order-result-meta { font-size: .78rem; color: var(--muted); margin-top: .2rem; }
    .order-result-chip {
        display: inline-flex; align-items: center; gap: .3rem; background: rgba(17,24,39,0.06);
        border-radius: 50px; padding: .12rem .55rem; font-size: .72rem; font-weight: 600; margin-right: .35rem;
    }
    .order-result-status {
        font-size: .72rem; font-weight: 700; padding: .2rem .6rem; border-radius: 50px;
        white-space: nowrap;
    }
    .not-ready-box {
        text-align: center; padding: 2rem 1.25rem; background: rgba(17,24,39,0.03);
        border-radius: 12px; margin-top: .5rem;
    }
    .not-ready-box i { font-size: 1.8rem; color: var(--muted); margin-bottom: .7rem; display: block; }
    .not-ready-box p { color: var(--muted); font-size: .88rem; margin: 0; }
    .search-empty { text-align: center; color: var(--muted); padding: 2.5rem 1rem; font-size: .88rem; }
    .search-results { max-height: 360px; overflow-y: auto; padding-right: .25rem; }

    .order-detail-box { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px dashed var(--border); }
    .detail-meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .7rem; margin-bottom: 1.1rem; }
    .detail-meta-item { background: rgba(17,24,39,0.03); border-radius: 10px; padding: .6rem .8rem; }
    .detail-meta-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); font-weight: 700; }
    .detail-meta-value { font-size: .9rem; color: var(--dark); font-weight: 600; margin-top: .15rem; }

    .item-row { display: flex; align-items: center; gap: .8rem; padding: .7rem 0; border-bottom: 1px dashed var(--border); }
    .item-row:last-child { border-bottom: none; }
    .item-img { width: 46px; height: 46px; border-radius: 10px; object-fit: cover; flex-shrink: 0; background: rgba(17,24,39,0.05); }
    .item-info { flex: 1; min-width: 0; }
    .item-name { font-weight: 600; font-size: .9rem; color: var(--dark); }
    .item-unit { font-size: .78rem; color: var(--muted); }
    .item-qty { font-weight: 700; color: var(--primary); font-size: .85rem; flex-shrink: 0; padding: 0 .5rem; }
    .item-sub { font-weight: 700; font-size: .9rem; color: var(--dark); flex-shrink: 0; min-width: 80px; text-align: right; }

    .instructions-box {
        background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25);
        border-radius: 10px; padding: .75rem .9rem; margin-top: 1rem; font-size: .86rem; color: #92400E;
    }
    .empty-left { text-align: center; color: var(--muted); padding: 3rem 1rem; }
    .empty-left i { font-size: 2.2rem; margin-bottom: .75rem; display: block; color: rgba(17,24,39,0.15); }

    /* ── Right panel (billing summary) ──────────────────────── */
    .billing-summary-row { display: flex; justify-content: space-between; font-size: .9rem; padding: .5rem 0; }
    .billing-summary-row.total { font-size: 1.15rem; font-weight: 800; border-top: 1.5px solid var(--border); margin-top: .5rem; padding-top: .85rem; }
    .billing-summary-row .neg { color: var(--primary); }

    .form-group { margin-bottom: 1.1rem; }
    .form-label { display: block; font-size: .82rem; font-weight: 700; color: var(--dark); margin-bottom: .4rem; }
    .form-select, .form-input {
        width: 100%; border: 1.5px solid rgba(17,24,39,0.1); border-radius: 10px;
        padding: .65rem .85rem; font-size: .88rem; font-family: inherit; outline: none; transition: all .2s;
        background: var(--white);
    }
    .form-select:focus, .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(220,38,38,0.08); }
    .form-input.has-error { border-color: #EF4444; }
    .field-error { color: #EF4444; font-size: .78rem; margin-top: .35rem; }
    .field-hint { color: var(--muted); font-size: .76rem; margin-top: .35rem; }

    .pay-method-row { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
    .pay-method-option { display: none; }
    .pay-method-label {
        display: flex; flex-direction: column; align-items: center; gap: .3rem;
        border: 1.5px solid rgba(17,24,39,0.1); border-radius: 10px; padding: .75rem .5rem;
        cursor: pointer; transition: all .15s; text-align: center;
    }
    .pay-method-label i { font-size: 1.1rem; color: var(--muted); }
    .pay-method-label span { font-size: .8rem; font-weight: 600; color: var(--muted); }
    .pay-method-option:checked + .pay-method-label { border-color: var(--primary); background: rgba(220,38,38,0.05); }
    .pay-method-option:checked + .pay-method-label i,
    .pay-method-option:checked + .pay-method-label span { color: var(--primary); }
    .pay-method-option:disabled + .pay-method-label { opacity: .45; cursor: not-allowed; }

    .change-box { background: rgba(22,163,74,0.08); border: 1px solid rgba(22,163,74,0.25); border-radius: 10px; padding: .8rem 1rem; margin-top: .5rem; }
    .change-box.insufficient { background: rgba(220,38,38,0.08); border-color: rgba(220,38,38,0.25); }
    .change-label { font-size: .78rem; color: var(--muted); font-weight: 600; }
    .change-value { font-size: 1.3rem; font-weight: 800; color: #16A34A; }
    .change-value.insufficient { color: var(--primary); }

    .eligibility-box {
        background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.3); border-radius: 10px;
        padding: .75rem .9rem; margin-top: -.4rem; margin-bottom: 1.1rem; font-size: .84rem; color: #92400E;
        display: flex; align-items: flex-start; gap: .55rem;
    }
    .eligibility-box input { margin-top: .2rem; }

    .action-buttons { display: flex; flex-direction: column; gap: .6rem; margin-top: 1.25rem; }
    .action-buttons-row { display: flex; gap: .6rem; }

    .receipt-success { text-align: center; padding: 1rem 0; }
    .receipt-success i { font-size: 2.5rem; color: #16A34A; margin-bottom: .6rem; }
    .receipt-success h3 { margin: 0 0 .3rem; }
    .receipt-success p { color: var(--muted); font-size: .88rem; margin: 0 0 1.2rem; }

    .gcash-qr-box { text-align: center; margin-top: 1.25rem; }
    .gcash-qr-box canvas { border: 1.5px solid var(--border); border-radius: 12px; padding: .5rem; background: #fff; }
    .gcash-qr-hint { font-size: .82rem; color: var(--muted); margin-top: .8rem; }
    .gcash-qr-status { font-size: .86rem; font-weight: 600; color: var(--dark); margin-top: .9rem; display: flex; align-items: center; justify-content: center; gap: .55rem; }
    .gcash-qr-status.is-error { color: var(--primary); }

    .spin {
        width: 15px; height: 15px; border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite; display: inline-block;
    }
    .spin.dark { border: 2px solid rgba(17,24,39,.15); border-top-color: var(--primary); }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 1100px) { .billing-layout { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

<div class="billing-layout">

    <!-- ── Left Panel: Order Search, Details, Items ── -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-magnifying-glass"></i> Order Search</h3></div>
        <div class="card-body">
            <div class="search-bar">
                <input type="text" id="searchInput" class="search-input"
                       placeholder="Search order #, customer name, or table number...">
                <select id="typeFilter" class="search-select">
                    <option value="">All Types</option>
                    <option value="dine_in">Dine-In</option>
                    <option value="takeout">Take-Out</option>
                    <option value="online">Online</option>
                </select>
            </div>
            <div class="search-results" id="searchResults"></div>

            <div class="order-detail-box" id="orderDetailBox" style="display:none"></div>
            <div class="empty-left" id="emptyLeft">
                <i class="fas fa-receipt"></i>
                Select an order from the search results to begin billing.
            </div>
        </div>
    </div>

    <!-- ── Right Panel: Billing Summary ── -->
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-calculator"></i> Billing Summary</h3></div>
        <div class="card-body" id="billingPanel">
            <div class="empty-left"><i class="fas fa-hand-pointer"></i>No order selected yet.</div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const MENU_PLACEHOLDER = "{{ asset('images/menu-placeholder.png') }}";
    const ORDERS_URL     = "{{ route('cashier.orders.index') }}";
    const DISCOUNTS_URL  = "{{ route('cashier.discounts.index') }}";
    const GCASH_INTENTS_BASE_URL = "{{ url('/cashier/gcash-intents') }}";
    const PRESELECT_ORDER = @json($preselectedOrderId);
    const PRESELECT_Q      = @json(request()->query('q'));

    let discountsCache = [];
    let currentOrder = null;
    let searchDebounce = null;
    let currentGcashIntentId = null;
    let gcashPollTimer = null;

    /* ── Search ── */
    async function searchOrders() {
        const q = document.getElementById('searchInput').value.trim();
        const type = document.getElementById('typeFilter').value;
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (type) params.set('type', type);

        try {
            const res = await fetch(`${ORDERS_URL}?${params.toString()}`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            renderSearchResults(data.orders);
        } catch (e) {
            console.error('Failed to search orders', e);
        }
    }

    function renderSearchResults(orders) {
        const container = document.getElementById('searchResults');
        if (!orders.length) {
            container.innerHTML = '<div class="search-empty">No unpaid orders match your search.</div>';
            return;
        }
        container.innerHTML = orders.map(o => `
            <div class="order-result ${currentOrder && currentOrder.id === o.id ? 'selected' : ''}" onclick="selectOrder(${o.id})">
                <div>
                    <div class="order-result-number">#${o.order_number}</div>
                    <div class="order-result-meta">
                        <span class="order-result-chip"><i class="fas fa-user"></i> ${o.customer_name}</span>
                        <span class="order-result-chip">${o.order_type_label}${o.table_number ? ' · Table ' + o.table_number : ''}</span>
                        <span class="order-result-chip"><i class="fas fa-clock"></i> ${new Date(o.created_at).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}</span>
                    </div>
                </div>
                <div class="order-result-status" style="background:${o.status_color}1a;color:${o.status_color}">${o.status_label}</div>
            </div>
        `).join('');
    }

    document.getElementById('searchInput').addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(searchOrders, 300);
    });
    document.getElementById('typeFilter').addEventListener('change', searchOrders);

    /* ── Select & load an order ── */
    async function selectOrder(orderId) {
        stopGcashPolling();
        currentGcashIntentId = null;
        try {
            const res = await fetch(`${ORDERS_URL}/${orderId}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                showToast(err.message || 'This order is no longer available for billing.', 'error');
                searchOrders();
                return;
            }
            const data = await res.json();
            currentOrder = data.order;
            renderOrderDetail(currentOrder);
            renderBillingPanel(currentOrder);
            searchOrders();
        } catch (e) {
            showToast('Failed to load order details.', 'error');
        }
    }

    function renderOrderDetail(order) {
        document.getElementById('emptyLeft').style.display = 'none';
        const box = document.getElementById('orderDetailBox');
        box.style.display = 'block';

        const itemsHtml = order.items.map(item => `
            <div class="item-row">
                <img class="item-img" src="${item.image_url || MENU_PLACEHOLDER}" alt="" onerror="this.src='${MENU_PLACEHOLDER}'">
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-unit">${formatPeso(item.price)} each</div>
                </div>
                <div class="item-qty">×${item.quantity}</div>
                <div class="item-sub">${formatPeso(item.subtotal)}</div>
            </div>
        `).join('');

        box.innerHTML = `
            <div class="detail-meta-grid">
                <div class="detail-meta-item"><div class="detail-meta-label">Order Number</div><div class="detail-meta-value">#${order.order_number}</div></div>
                <div class="detail-meta-item"><div class="detail-meta-label">Customer</div><div class="detail-meta-value">${order.customer_name}</div></div>
                <div class="detail-meta-item"><div class="detail-meta-label">Order Type</div><div class="detail-meta-value">${order.order_type_label}${order.table_number ? ' · Table ' + order.table_number : ''}</div></div>
                <div class="detail-meta-item"><div class="detail-meta-label">Total Items</div><div class="detail-meta-value">${order.item_count}</div></div>
            </div>
            <div class="detail-meta-label" style="margin-bottom:.5rem">Ordered Items</div>
            ${itemsHtml}
            ${order.special_instructions ? `<div class="instructions-box"><i class="fas fa-circle-info"></i> ${order.special_instructions}</div>` : ''}
        `;
    }

    /* ── Billing panel (right side) ── */
    async function ensureDiscountsLoaded() {
        if (discountsCache.length) return discountsCache;
        try {
            const res = await fetch(DISCOUNTS_URL, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            discountsCache = data.discounts;
        } catch (e) {
            discountsCache = [];
        }
        return discountsCache;
    }

    async function renderBillingPanel(order) {
        const panel = document.getElementById('billingPanel');

        if (!order.is_awaiting_payment) {
            panel.innerHTML = `
                <div class="not-ready-box">
                    <i class="fas fa-kitchen-set"></i>
                    <p><strong>Order #${order.order_number}</strong> is still <strong>${order.status_label}</strong> in the kitchen.<br>
                    Billing becomes available once it's marked Ready.</p>
                </div>
                <div class="action-buttons">
                    <button type="button" class="btn btn-outline btn-block" onclick="cancelBilling()">
                        <i class="fas fa-xmark"></i> Close
                    </button>
                </div>
            `;
            return;
        }

        await ensureDiscountsLoaded();
        const discountOptions = discountsCache.map(d => {
            const meetsMin = d.minimum_purchase === null || order.subtotal >= d.minimum_purchase;
            return `<option value="${d.id}" ${!meetsMin ? 'disabled' : ''}>
                ${d.name} (${d.formatted_value})${!meetsMin ? ' — min. ' + formatPeso(d.minimum_purchase) : ''}
            </option>`;
        }).join('');

        panel.innerHTML = `
            <div id="summarySubtotal" class="billing-summary-row"><span>Subtotal</span><span>${formatPeso(order.subtotal)}</span></div>
            <div id="summaryDiscountRow" class="billing-summary-row" style="display:none"><span>Less Discount</span><span class="neg">- <span id="summaryDiscountAmt">₱0.00</span></span></div>
            <div id="summaryServiceRow" class="billing-summary-row" style="display:none"><span>Service Charge</span><span id="summaryServiceAmt">₱0.00</span></div>
            <div class="billing-summary-row total"><span>Grand Total</span><span id="summaryGrandTotal">${formatPeso(order.subtotal)}</span></div>

            <div class="form-group" style="margin-top:1.25rem">
                <label class="form-label"><i class="fas fa-tag"></i> Discount</label>
                <select class="form-select" id="discountSelect">
                    <option value="">No Discount</option>
                    ${discountOptions}
                </select>
            </div>

            <div id="eligibilityBox" class="eligibility-box" style="display:none">
                <input type="checkbox" id="eligibilityConfirmed">
                <label for="eligibilityConfirmed">I have verified the customer's Senior Citizen / PWD ID for this discount.</label>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fas fa-receipt"></i> Service Charge <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
                <input type="number" class="form-input" id="serviceChargeInput" min="0" step="0.01" value="0">
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fas fa-money-bill-wave"></i> Payment Method</label>
                <div class="pay-method-row">
                    <input type="radio" name="paymentMethod" id="payCash" class="pay-method-option" value="cash" checked>
                    <label for="payCash" class="pay-method-label"><i class="fas fa-money-bill"></i><span>Cash</span></label>

                    <input type="radio" name="paymentMethod" id="payCashless" class="pay-method-option" value="cashless">
                    <label for="payCashless" class="pay-method-label"><i class="fas fa-qrcode"></i><span>GCash (Scan QR)</span></label>
                </div>
            </div>

            <div class="form-group" id="amountReceivedGroup">
                <label class="form-label"><i class="fas fa-hand-holding-dollar"></i> Amount Received</label>
                <input type="number" class="form-input" id="amountReceivedInput" min="0" step="0.01" placeholder="0.00">
                <div class="field-error" id="paymentError" style="display:none">Insufficient payment amount.</div>
                <div class="change-box" id="changeBox" style="display:none">
                    <div class="change-label">Change</div>
                    <div class="change-value" id="changeValue">₱0.00</div>
                </div>
            </div>

            <div class="action-buttons" id="mainActionButtons">
                <button type="button" class="btn btn-primary btn-block" id="primaryActionBtn" onclick="handlePrimaryAction()">
                    <i class="fas fa-check"></i> <span id="primaryActionLabel">Confirm Payment</span>
                </button>
                <button type="button" class="btn btn-outline btn-block" onclick="cancelBilling()">
                    <i class="fas fa-xmark"></i> Cancel
                </button>
            </div>

            <div class="gcash-qr-box" id="gcashQrBox" style="display:none">
                <canvas id="gcashQrCanvas" style="display:none"></canvas>
                <img id="gcashQrImage" alt="Scan to pay" style="display:none;max-width:220px;width:100%;border:1.5px solid var(--border);border-radius:12px;padding:.5rem;background:#fff">
                <div class="gcash-qr-hint">Ask the customer to scan this with their GCash app or phone camera.</div>
                <div class="gcash-qr-status" id="gcashQrStatus"></div>
                <div class="action-buttons" id="gcashQrActions">
                    <button type="button" class="btn btn-outline btn-block" onclick="cancelGcashIntent()">
                        <i class="fas fa-xmark"></i> Cancel GCash Payment
                    </button>
                </div>
            </div>
        `;

        document.getElementById('discountSelect').addEventListener('change', recomputeTotals);
        document.getElementById('serviceChargeInput').addEventListener('input', recomputeTotals);
        document.getElementById('amountReceivedInput').addEventListener('input', recomputeTotals);
        document.querySelectorAll('input[name="paymentMethod"]').forEach(el => el.addEventListener('change', recomputeTotals));

        recomputeTotals();
    }

    function currentDiscount() {
        const id = document.getElementById('discountSelect')?.value;
        if (!id) return null;
        return discountsCache.find(d => String(d.id) === String(id)) || null;
    }

    function recomputeTotals() {
        if (!currentOrder) return;
        const subtotal = currentOrder.subtotal;
        const discount = currentDiscount();

        let discountAmount = 0;
        if (discount) {
            discountAmount = discount.type === 'percentage'
                ? subtotal * (discount.value / 100)
                : discount.value;
            if (discount.maximum_discount !== null) discountAmount = Math.min(discountAmount, discount.maximum_discount);
            discountAmount = Math.min(discountAmount, subtotal);
        }

        const serviceCharge = Math.max(parseFloat(document.getElementById('serviceChargeInput').value) || 0, 0);
        const grandTotal = Math.max(subtotal - discountAmount + serviceCharge, 0);

        document.getElementById('summaryDiscountRow').style.display = discountAmount > 0 ? 'flex' : 'none';
        document.getElementById('summaryDiscountAmt').textContent = formatPeso(discountAmount);
        document.getElementById('summaryServiceRow').style.display = serviceCharge > 0 ? 'flex' : 'none';
        document.getElementById('summaryServiceAmt').textContent = formatPeso(serviceCharge);
        document.getElementById('summaryGrandTotal').textContent = formatPeso(grandTotal);

        document.getElementById('eligibilityBox').style.display = (discount && discount.requires_verification) ? 'flex' : 'none';

        const isCash = document.getElementById('payCash').checked;
        document.getElementById('amountReceivedGroup').style.display = isCash ? 'block' : 'none';

        const primaryLabel = document.getElementById('primaryActionLabel');
        const primaryIcon = document.querySelector('#primaryActionBtn i');
        if (primaryLabel) primaryLabel.textContent = isCash ? 'Confirm Payment' : 'Generate GCash QR Code';
        if (primaryIcon) primaryIcon.className = isCash ? 'fas fa-check' : 'fas fa-qrcode';

        const amountReceivedInput = document.getElementById('amountReceivedInput');
        const amountReceived = parseFloat(amountReceivedInput.value) || 0;
        const changeBox = document.getElementById('changeBox');
        const paymentError = document.getElementById('paymentError');

        if (isCash && amountReceivedInput.value !== '') {
            const change = amountReceived - grandTotal;
            if (change < 0) {
                changeBox.style.display = 'none';
                paymentError.style.display = 'block';
            } else {
                paymentError.style.display = 'none';
                changeBox.style.display = 'block';
                changeBox.classList.remove('insufficient');
                document.getElementById('changeValue').classList.remove('insufficient');
                document.getElementById('changeValue').textContent = formatPeso(change);
            }
        } else {
            changeBox.style.display = 'none';
            paymentError.style.display = 'none';
        }
    }

    function cancelBilling() {
        stopGcashPolling();
        currentGcashIntentId = null;
        currentOrder = null;
        document.getElementById('orderDetailBox').style.display = 'none';
        document.getElementById('orderDetailBox').innerHTML = '';
        document.getElementById('emptyLeft').style.display = 'block';
        document.getElementById('billingPanel').innerHTML = '<div class="empty-left"><i class="fas fa-hand-pointer"></i>No order selected yet.</div>';
        searchOrders();
    }

    function confirmPayment() {
        if (!currentOrder) return;

        const isCash = document.getElementById('payCash').checked;
        const grandTotalText = document.getElementById('summaryGrandTotal').textContent;
        const discount = currentDiscount();

        if (isCash) {
            const amountReceived = parseFloat(document.getElementById('amountReceivedInput').value) || 0;
            const grandTotal = parseFloat(grandTotalText.replace(/[^0-9.]/g, ''));
            if (amountReceived < grandTotal) {
                showToast('Insufficient payment amount.', 'error');
                return;
            }
        }

        if (discount && discount.requires_verification && !document.getElementById('eligibilityConfirmed').checked) {
            showToast("Please verify the customer's ID before applying this discount.", 'error');
            return;
        }

        openConfirmModal({
            title: 'Complete Payment?',
            desc: `Are you sure you want to complete this payment transaction for Order #${currentOrder.order_number} totaling ${grandTotalText}?`,
            confirmText: 'Complete Payment',
            onConfirm: submitPayment,
        });
    }

    async function submitPayment() {
        const isCash = document.getElementById('payCash').checked;
        const discount = currentDiscount();
        const payload = {
            payment_method: isCash ? 'cash' : 'cashless',
            discount_id: discount ? discount.id : null,
            service_charge: parseFloat(document.getElementById('serviceChargeInput').value) || 0,
            amount_received: isCash ? (parseFloat(document.getElementById('amountReceivedInput').value) || 0) : null,
            eligibility_confirmed: discount && discount.requires_verification
                ? document.getElementById('eligibilityConfirmed').checked
                : false,
        };

        try {
            const res = await fetch(`${ORDERS_URL}/${currentOrder.id}/payment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            closeConfirmModal();

            if (!res.ok) {
                showToast(data.message || 'Failed to process payment.', 'error');
                return;
            }

            showToast(data.message, 'success');
            renderPaymentSuccess(data.receipt_url);
        } catch (e) {
            closeConfirmModal();
            showToast('Failed to process payment.', 'error');
        }
    }

    function handlePrimaryAction() {
        if (document.getElementById('payCash').checked) {
            confirmPayment();
        } else {
            generateGcashQr();
        }
    }

    async function generateGcashQr() {
        const discount = currentDiscount();
        if (discount && discount.requires_verification && !document.getElementById('eligibilityConfirmed').checked) {
            showToast("Please verify the customer's ID before applying this discount.", 'error');
            return;
        }

        const btn = document.getElementById('primaryActionBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spin"></span> <span>Starting GCash Payment...</span>';

        const payload = {
            discount_id: discount ? discount.id : null,
            service_charge: parseFloat(document.getElementById('serviceChargeInput').value) || 0,
            eligibility_confirmed: discount && discount.requires_verification
                ? document.getElementById('eligibilityConfirmed').checked
                : false,
        };

        try {
            const res = await fetch(`${ORDERS_URL}/${currentOrder.id}/gcash-intent`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();

            if (!res.ok) {
                showToast(data.message || 'Failed to start GCash payment.', 'error');
                resetPrimaryButton();
                return;
            }

            currentGcashIntentId = data.intent_id;
            await showGcashQr(data.checkout_url, data.next_action_type);
            startGcashPolling();
        } catch (e) {
            showToast('Failed to start GCash payment.', 'error');
            resetPrimaryButton();
        }
    }

    function resetPrimaryButton() {
        const btn = document.getElementById('primaryActionBtn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-qrcode"></i> <span id="primaryActionLabel">Generate GCash QR Code</span>';
    }

    async function showGcashQr(url, nextActionType) {
        document.getElementById('mainActionButtons').style.display = 'none';
        document.getElementById('discountSelect').disabled = true;
        document.getElementById('serviceChargeInput').disabled = true;
        document.querySelectorAll('input[name="paymentMethod"]').forEach(el => el.disabled = true);

        const box = document.getElementById('gcashQrBox');
        box.style.display = 'block';
        setGcashStatus('Waiting for customer to scan and pay…', false);
        document.getElementById('gcashQrActions').innerHTML = `
            <button type="button" class="btn btn-outline btn-block" onclick="cancelGcashIntent()">
                <i class="fas fa-xmark"></i> Cancel GCash Payment
            </button>
        `;

        const canvas = document.getElementById('gcashQrCanvas');
        const img = document.getElementById('gcashQrImage');

        if (nextActionType === 'qr_image') {
            // PayMongo (e.g. QR Ph) already handed back a rendered QR image —
            // just display it, no client-side rendering needed.
            canvas.style.display = 'none';
            img.style.display = 'block';
            img.src = url;
        } else {
            // Redirect-based methods (e.g. GCash) hand back a URL the
            // customer's own device would navigate to — we render that
            // URL as our own QR code for a *different* device to scan.
            img.style.display = 'none';
            canvas.style.display = 'block';
            try {
                await QRCode.toCanvas(canvas, url, { width: 220, margin: 1 });
            } catch (e) {
                console.error('QR render failed', e);
            }
        }
    }

    function setGcashStatus(message, isError) {
        const el = document.getElementById('gcashQrStatus');
        el.classList.toggle('is-error', !!isError);
        el.innerHTML = isError
            ? `<i class="fas fa-triangle-exclamation"></i> ${message}`
            : `<span class="spin dark"></span> ${message}`;
    }

    function startGcashPolling() {
        stopGcashPolling();
        gcashPollTimer = setInterval(checkGcashStatus, 3000);
    }

    function stopGcashPolling() {
        if (gcashPollTimer) {
            clearInterval(gcashPollTimer);
            gcashPollTimer = null;
        }
    }

    async function checkGcashStatus() {
        if (!currentGcashIntentId) return;
        try {
            const res = await fetch(`${GCASH_INTENTS_BASE_URL}/${currentGcashIntentId}/status`, {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();

            if (data.status === 'paid') {
                stopGcashPolling();
                showToast('GCash payment received!', 'success');
                renderPaymentSuccess(data.receipt_url);
            } else if (data.status === 'expired') {
                stopGcashPolling();
                setGcashStatus('This QR code expired after 30 minutes.', true);
                showGenerateNewQrButton();
            } else if (data.status === 'failed' || data.status === 'cancelled') {
                stopGcashPolling();
                setGcashStatus('Payment was not completed.', true);
                showToast('GCash payment was not completed.', 'error');
            }
            // status === 'pending' → keep polling silently.
        } catch (e) {
            // Transient network hiccup — next tick will retry.
        }
    }

    function showGenerateNewQrButton() {
        document.getElementById('gcashQrActions').innerHTML = `
            <button type="button" class="btn btn-primary btn-block" onclick="regenerateGcashQr()">
                <i class="fas fa-qrcode"></i> Generate New QR
            </button>
            <button type="button" class="btn btn-outline btn-block" onclick="cancelGcashIntent()">
                <i class="fas fa-xmark"></i> Cancel GCash Payment
            </button>
        `;
    }

    async function regenerateGcashQr() {
        currentGcashIntentId = null;
        await generateGcashQr();
    }

    async function cancelGcashIntent() {
        stopGcashPolling();
        const intentId = currentGcashIntentId;
        currentGcashIntentId = null;

        if (intentId) {
            try {
                await fetch(`${GCASH_INTENTS_BASE_URL}/${intentId}/cancel`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                });
            } catch (e) {
                // Nothing to do — the intent will simply age out unused.
            }
        }

        document.getElementById('gcashQrBox').style.display = 'none';
        document.getElementById('mainActionButtons').style.display = 'flex';
        document.getElementById('discountSelect').disabled = false;
        document.getElementById('serviceChargeInput').disabled = false;
        document.querySelectorAll('input[name="paymentMethod"]').forEach(el => el.disabled = false);
        resetPrimaryButton();
        recomputeTotals();
    }

    function renderPaymentSuccess(receiptUrl) {
        document.getElementById('billingPanel').innerHTML = `
            <div class="receipt-success">
                <i class="fas fa-circle-check"></i>
                <h3>Payment Completed</h3>
                <p>Order #${currentOrder.order_number} has been marked as paid.</p>
                <div class="action-buttons">
                    <a href="${receiptUrl}" target="_blank" class="btn btn-primary btn-block"><i class="fas fa-print"></i> Print Receipt</a>
                    <button type="button" class="btn btn-outline btn-block" onclick="cancelBilling()"><i class="fas fa-plus"></i> New Transaction</button>
                </div>
            </div>
        `;
        document.getElementById('orderDetailBox').style.display = 'none';
        document.getElementById('emptyLeft').style.display = 'block';
        currentOrder = null;
    }

    /* ── Initial load ── */
    (async function init() {
        if (PRESELECT_Q) document.getElementById('searchInput').value = PRESELECT_Q;
        await searchOrders();
        if (PRESELECT_ORDER) selectOrder(PRESELECT_ORDER);
    })();
</script>
@endsection
