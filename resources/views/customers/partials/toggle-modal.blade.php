{{--
    Activate / Deactivate confirmation for a customer account (Customer Accounts list and detail page).
    Open with openToggleModal(actionUrl, customerName, isActive). Deactivating requires a reason,
    which the customer sees when they next try to log in.
--}}
<div id="toggleModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:var(--surface);border-radius:18px;padding:2rem;max-width:440px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
        <div style="text-align:center;margin-bottom:1.25rem">
            <div id="toggleIcon" style="width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin:0 auto .9rem"></div>
            <h3 id="toggleTitle" style="font-size:1.05rem;font-weight:700;color:var(--dark);margin:0 0 .5rem"></h3>
            <p id="toggleBody" style="font-size:.85rem;color:var(--muted);margin:0;line-height:1.55"></p>
        </div>
        <form id="toggleForm" method="POST" novalidate>
            @csrf @method('PUT')
            <div id="toggleReasonWrap" style="display:none;margin-bottom:1.25rem;text-align:left">
                <label for="toggleReason" style="display:block;font-size:.8rem;font-weight:700;color:var(--dark);margin-bottom:.35rem">
                    Reason <span style="color:var(--red-600)">*</span>
                </label>
                <textarea name="reason" id="toggleReason" rows="3" maxlength="255" placeholder="e.g. Repeated no-show on paid orders"
                          style="width:100%;box-sizing:border-box;border:1.5px solid var(--border);border-radius:10px;padding:.6rem .8rem;font-family:inherit;font-size:.85rem;color:var(--dark);background:var(--surface);resize:vertical;outline:none">{{ old('reason') }}</textarea>
                <div style="display:flex;justify-content:space-between;gap:.5rem;margin-top:.3rem;font-size:.72rem;color:var(--muted)">
                    <span>The customer will see this when they try to log in.</span>
                    <span id="toggleReasonCount">0/255</span>
                </div>
                <div id="toggleReasonError" style="display:none;margin-top:.35rem;font-size:.78rem;font-weight:600;color:var(--red-600)">
                    <i class="fas fa-circle-exclamation"></i> <span></span>
                </div>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:center">
                <button type="button" onclick="closeToggleModal()" style="flex:1;padding:.65rem;border:1.5px solid var(--border);border-radius:10px;background:transparent;color:var(--dark);font-family:inherit;font-size:.85rem;font-weight:600;cursor:pointer">Cancel</button>
                <button type="submit" id="toggleSubmit" style="flex:1;padding:.65rem;border:none;border-radius:10px;color:#fff;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('toggleModal'), form = document.getElementById('toggleForm');
    var reason = document.getElementById('toggleReason'), wrap = document.getElementById('toggleReasonWrap');
    var error = document.getElementById('toggleReasonError');
    var deactivating = false;

    function showError(message) {
        error.querySelector('span').textContent = message || '';
        error.style.display = message ? 'block' : 'none';
        reason.style.borderColor = message ? 'var(--red-600)' : 'var(--border)';
    }
    function count() { document.getElementById('toggleReasonCount').textContent = reason.value.length + '/255'; }

    window.openToggleModal = function (action, name, isActive, keepReason) {
        var icon = document.getElementById('toggleIcon'), title = document.getElementById('toggleTitle');
        var body = document.getElementById('toggleBody'), submit = document.getElementById('toggleSubmit');
        deactivating = !! isActive;
        if (deactivating) {
            icon.style.background = 'rgba(220,38,38,0.10)'; icon.style.color = '#DC2626';
            icon.innerHTML = '<i class="fas fa-ban"></i>';
            title.textContent = 'Deactivate "' + name + '"?';
            body.innerHTML = 'This customer will be <strong>logged out</strong> and unable to log in or place orders. Their account and order history remain stored in the database.';
            submit.style.background = 'linear-gradient(90deg,#DC2626,#F97316)';
            submit.textContent = 'Deactivate Account';
        } else {
            icon.style.background = 'rgba(22,163,74,0.10)'; icon.style.color = '#16A34A';
            icon.innerHTML = '<i class="fas fa-check"></i>';
            title.textContent = 'Activate "' + name + '"?';
            body.innerHTML = 'This customer will be able to <strong>log in</strong> and place orders again.';
            submit.style.background = 'linear-gradient(90deg,#16A34A,#059669)';
            submit.textContent = 'Activate Account';
        }
        wrap.style.display = deactivating ? 'block' : 'none';
        if (! keepReason) reason.value = '';
        showError('');
        count();
        form.action = action;
        modal.style.display = 'flex';
        if (deactivating) reason.focus();
    };
    window.closeToggleModal = function () { modal.style.display = 'none'; };

    modal.addEventListener('click', function (e) { if (e.target === modal) closeToggleModal(); });
    reason.addEventListener('input', function () { count(); showError(''); });
    form.addEventListener('submit', function (e) {
        if (deactivating && ! reason.value.trim()) {
            e.preventDefault();
            showError('Please enter a reason for deactivating this account.');
            reason.focus();
        }
    });

    // The server rejected the reason (e.g. left blank): reopen the pop-up with its message
    @if($errors->has('reason') && session('toggle_customer'))
        openToggleModal(@json(session('toggle_customer.action')), @json(session('toggle_customer.name')), true, true);
        showError(@json($errors->first('reason')));
    @endif
})();
</script>
