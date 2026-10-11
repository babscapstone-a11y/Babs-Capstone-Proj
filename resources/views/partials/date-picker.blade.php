{{--
    Calendar date picker that matches the clock dial (it reuses the .cd-field / .cd-panel / .cd-foot
    styles from partials.clock-dial, so include that first). Include once per page; each field is a
    hidden input (optional data-min="Y-m-d") plus a
    <button class="cd-field" data-picker="date" data-for="inputId" onclick="DatePick.open(this)">.
    Picking a day writes "Y-m-d" to the hidden input and fires a "change" event.
    DatePick.set(inputId, 'Y-m-d' | '') fills a field from script.
--}}
<style>
.dp-panel { width: 284px; }
.dp-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .6rem; }
.dp-title { font-weight: 700; font-size: .95rem; color: var(--dark); }
.dp-nav { width: 32px; height: 32px; border: 0; border-radius: 9px; cursor: pointer;
          background: rgba(var(--ink-rgb, 17,24,39),0.06); color: var(--dark); font-size: .75rem; }
.dp-nav:hover:not(:disabled) { background: rgba(var(--cd-accent-rgb, 217,119,6),0.16); color: var(--cd-accent, #D97706); }
.dp-nav:disabled { opacity: .3; cursor: not-allowed; }
.dp-dow, .dp-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
.dp-dow span { text-align: center; font-size: .68rem; font-weight: 700; color: var(--muted); padding: .2rem 0 .35rem; }
.dp-day { width: 34px; height: 34px; justify-self: center; border: 0; border-radius: 50%; cursor: pointer;
          background: transparent; color: var(--dark); font: inherit; font-size: .82rem; font-weight: 600; }
.dp-day:hover:not(:disabled) { background: rgba(var(--cd-accent-rgb, 217,119,6),0.14); }
.dp-day.today { box-shadow: inset 0 0 0 1.5px var(--cd-accent, #D97706); }
.dp-day.on { background: var(--cd-accent, #D97706); color: #fff; }
.dp-day:disabled { color: var(--muted); opacity: .4; cursor: not-allowed; }
.dp-day:focus-visible { outline: 2px solid var(--cd-accent, #D97706); outline-offset: 1px; }
.dp-panel .cd-foot { justify-content: space-between; }
.dp-panel .dp-today { background: transparent; color: var(--cd-accent, #D97706); padding-left: .3rem; }
</style>

<script>
(function () {
    var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var pad = function (n) { return String(n).padStart(2, '0'); };
    var ymd = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };
    var parse = function (s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); };
    var valid = function (s) { return /^\d{4}-\d{2}-\d{2}$/.test(s || ''); };

    var panel = document.createElement('div');
    panel.className = 'cd-panel dp-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Choose a date');
    panel.innerHTML =
        '<div class="dp-head">' +
            '<button type="button" class="dp-nav" data-step="-1" aria-label="Previous month"><i class="fas fa-chevron-left"></i></button>' +
            '<div class="dp-title" aria-live="polite"></div>' +
            '<button type="button" class="dp-nav" data-step="1" aria-label="Next month"><i class="fas fa-chevron-right"></i></button>' +
        '</div>' +
        '<div class="dp-dow"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div>' +
        '<div class="dp-grid"></div>' +
        '<div class="cd-foot"><button type="button" class="dp-today">Today</button><button type="button" class="cd-cancel">Cancel</button></div>';
    document.body.appendChild(panel);

    var grid = panel.querySelector('.dp-grid');
    // view = month on screen; cursor = day with keyboard focus; min = earliest allowed day ('' = none)
    var view = null, cursor = '', selected = '', min = '', field = null;

    function render() {
        panel.querySelector('.dp-title').textContent = MONTHS[view.getMonth()] + ' ' + view.getFullYear();
        var firstOfMonth = ymd(view).slice(0, 7);
        panel.querySelector('[data-step="-1"]').disabled = !! min && firstOfMonth <= min.slice(0, 7);

        var today = ymd(new Date());
        var html = '';
        for (var b = 0; b < view.getDay(); b++) html += '<span></span>';   // blanks before the 1st
        var days = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
        for (var d = 1; d <= days; d++) {
            var date = firstOfMonth + '-' + pad(d);
            var cls = 'dp-day' + (date === selected ? ' on' : '') + (date === today ? ' today' : '');
            html += '<button type="button" class="' + cls + '" data-date="' + date + '" tabindex="' + (date === cursor ? 0 : -1) + '"' +
                    (min && date < min ? ' disabled' : '') + ' aria-label="' + parse(date).toDateString() + '">' + d + '</button>';
        }
        grid.innerHTML = html;
    }

    function focusCursor() {
        var btn = grid.querySelector('[data-date="' + cursor + '"]');
        if (btn) btn.focus();
    }

    function apply(date) {
        var hidden = document.getElementById(field.dataset.for);
        hidden.value = date;
        showOnField(field, date);
        field.classList.remove('has-error');
        var f = field;
        close();
        f.focus();
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
    }

    grid.addEventListener('click', function (e) {
        var btn = e.target.closest('.dp-day');
        if (btn && ! btn.disabled) apply(btn.dataset.date);
    });
    // Arrow keys move a day (left/right) or a week (up/down), crossing months as needed
    grid.addEventListener('keydown', function (e) {
        var step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[e.key];
        if (! step) return;
        e.preventDefault();
        var next = parse(cursor);
        next.setDate(next.getDate() + step);
        if (min && ymd(next) < min) return;
        cursor = ymd(next);
        view = new Date(next.getFullYear(), next.getMonth(), 1);
        render();
        focusCursor();
    });
    panel.querySelectorAll('.dp-nav').forEach(function (b) {
        b.addEventListener('click', function () {
            view = new Date(view.getFullYear(), view.getMonth() + (+b.dataset.step), 1);
            render();
        });
    });
    panel.querySelector('.dp-today').addEventListener('click', function () { apply(ymd(new Date())); });
    panel.querySelector('.cd-cancel').addEventListener('click', close);

    function showOnField(btn, value) {
        var text = btn.querySelector('.cd-text');
        text.textContent = valid(value)
            ? parse(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
            : 'Select date';
        text.classList.toggle('empty', ! valid(value));
    }

    function position() {
        if (! field) return;
        var r = field.getBoundingClientRect(), pw = panel.offsetWidth, ph = panel.offsetHeight;
        var left = Math.max(8, Math.min(r.left, window.innerWidth - pw - 8));
        var top = r.bottom + 6;
        if (top + ph > window.innerHeight - 8) top = Math.max(8, r.top - ph - 6);
        panel.style.left = left + 'px';
        panel.style.top = top + 'px';
    }

    function open(btn) {
        if (field === btn) { close(); return; }
        close();
        field = btn;
        var hidden = document.getElementById(btn.dataset.for);
        selected = valid(hidden.value) ? hidden.value : '';
        min = hidden.dataset.min || '';
        var today = ymd(new Date());
        cursor = selected || (min && min > today ? min : today);
        var c = parse(cursor);
        view = new Date(c.getFullYear(), c.getMonth(), 1);
        btn.classList.add('active');
        render();
        panel.classList.add('open');
        position();
        focusCursor();
    }

    function close() {
        if (field) field.classList.remove('active');
        field = null;
        panel.classList.remove('open');
    }

    // Click outside or Escape closes without changing anything (Escape doesn't also close the modal behind)
    document.addEventListener('pointerdown', function (e) {
        if (field && ! panel.contains(e.target) && ! field.contains(e.target)) close();
    }, true);
    document.addEventListener('keydown', function (e) {
        if (field && e.key === 'Escape') { e.stopImmediatePropagation(); e.preventDefault(); var f = field; close(); f.focus(); }
    }, true);
    window.addEventListener('resize', position);
    window.addEventListener('scroll', position, true);

    window.DatePick = {
        open: open,
        set: function (id, value) {
            var hidden = document.getElementById(id);
            hidden.value = valid(value) ? value : '';
            showOnField(document.querySelector('.cd-field[data-picker="date"][data-for="' + id + '"]'), hidden.value);
        },
    };

    // Show whatever the server filled in (e.g. old input after a failed submit)
    document.querySelectorAll('.cd-field[data-picker="date"]').forEach(function (btn) {
        showOnField(btn, document.getElementById(btn.dataset.for).value);
    });
})();
</script>
