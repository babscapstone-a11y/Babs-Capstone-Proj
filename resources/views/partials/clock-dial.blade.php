{{--
    Clock dial time picker. Include once per page; each field is a hidden input plus a
    <button class="cd-field" data-picker="time" data-for="inputId" onclick="ClockDial.open(this)"> (see downtime-time-picker).
    Flow: pick the hour on the dial → it switches to minutes → choose AM/PM → Set Time.
    The hidden input gets a 24-hour "HH:MM" value and fires a "change" event.
    ClockDial.set(inputId, 'HH:MM' | '') fills a field from script.
    Optional data-min-time / data-max-time ("HH:MM") on the hidden input grey out times outside
    that range (e.g. opening hours). Accent colour: set --cd-accent and --cd-accent-rgb on the page.
--}}
<style>
.cd-field { flex: 1; display: flex; align-items: center; justify-content: space-between; gap: .5rem; height: 40px; padding: 0 .7rem;
            border: 1.5px solid rgba(var(--ink-rgb, 17,24,39),0.1); border-radius: 10px; background: var(--surface, #fff); color: var(--dark);
            font: inherit; font-size: .85rem; cursor: pointer; text-align: left; }
.cd-field:hover, .cd-field.active { border-color: var(--cd-accent, #D97706); }
.cd-field.has-error { border-color: #EF4444; }
.cd-field i { color: var(--muted); }
.cd-field .cd-text.empty { color: var(--muted); }

.cd-panel { position: fixed; z-index: 10050; width: 260px; padding: 1rem; display: none; border-radius: 16px;
            background: var(--surface, #fff); border: 1px solid rgba(var(--ink-rgb, 17,24,39),0.1); box-shadow: 0 18px 50px rgba(0,0,0,.35); }
.cd-panel.open { display: block; }
.cd-head { display: flex; align-items: center; justify-content: center; gap: .3rem; }
.cd-seg { border: 0; background: rgba(var(--ink-rgb, 17,24,39),0.06); color: var(--dark); cursor: pointer; min-width: 3.6rem;
          font: inherit; font-size: 1.9rem; font-weight: 700; line-height: 1; padding: .35rem .5rem; border-radius: 10px; }
.cd-seg.on { background: rgba(var(--cd-accent-rgb, 217,119,6),0.16); color: var(--cd-accent, #D97706); }
.cd-sep { font-size: 1.7rem; font-weight: 700; color: var(--muted); }
.cd-ap { display: flex; flex-direction: column; gap: 3px; margin-left: .45rem; }
.cd-ap button { border: 1px solid rgba(var(--ink-rgb, 17,24,39),0.12); background: transparent; color: var(--muted); cursor: pointer;
                font: inherit; font-size: .72rem; font-weight: 700; padding: .2rem .5rem; border-radius: 6px; }
.cd-ap button.on { background: var(--cd-accent, #D97706); border-color: var(--cd-accent, #D97706); color: #fff; }
.cd-hint { text-align: center; font-size: .72rem; color: var(--muted); margin: .55rem 0 .5rem; }
.cd-dial { position: relative; width: 220px; height: 220px; margin: 0 auto; border-radius: 50%; outline: none;
           background: rgba(var(--ink-rgb, 17,24,39),0.05); touch-action: none; cursor: pointer; user-select: none; -webkit-user-select: none; }
.cd-dial:focus-visible { box-shadow: 0 0 0 3px rgba(var(--cd-accent-rgb, 217,119,6),0.35); }
.cd-dial svg { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }
.cd-dial svg line { stroke: var(--cd-accent, #D97706); stroke-width: 2; }
.cd-dial svg circle { fill: var(--cd-accent, #D97706); }
.cd-num { position: absolute; z-index: 1; width: 32px; height: 32px; margin: -16px 0 0 -16px; border-radius: 50%; pointer-events: none;
          display: flex; align-items: center; justify-content: center; font-size: .85rem; font-weight: 600; color: var(--dark); }
.cd-num.on { color: #fff; }
.cd-num.off { opacity: .28; }
.cd-foot { display: flex; justify-content: flex-end; gap: .5rem; margin-top: .85rem; }
.cd-foot button { border: 0; cursor: pointer; font: inherit; font-size: .8rem; font-weight: 700; padding: .5rem .9rem; border-radius: 9px; }
.cd-cancel { background: rgba(var(--ink-rgb, 17,24,39),0.07); color: var(--dark); }
.cd-ok { background: var(--cd-accent, #D97706); color: #fff; }
.cd-ok:disabled { opacity: .45; cursor: not-allowed; }
</style>

<script>
(function () {
    var SIZE = 220, C = SIZE / 2, R = 84;          // dial size, centre and number radius (px)
    var pad = function (n) { return String(n).padStart(2, '0'); };

    var panel = document.createElement('div');
    panel.className = 'cd-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Choose a time');
    panel.innerHTML =
        '<div class="cd-head">' +
            '<button type="button" class="cd-seg" data-mode="h" aria-label="Hour"></button>' +
            '<span class="cd-sep">:</span>' +
            '<button type="button" class="cd-seg" data-mode="m" aria-label="Minutes"></button>' +
            '<div class="cd-ap"><button type="button" data-ap="AM">AM</button><button type="button" data-ap="PM">PM</button></div>' +
        '</div>' +
        '<div class="cd-hint"></div>' +
        '<div class="cd-dial" tabindex="0" aria-label="Clock dial. Use the arrow keys to change, Enter to continue.">' +
            '<svg viewBox="0 0 ' + SIZE + ' ' + SIZE + '"><line x1="' + C + '" y1="' + C + '"></line>' +
            '<circle class="cd-dot" cx="' + C + '" cy="' + C + '" r="3"></circle><circle class="cd-knob"></circle></svg>' +
        '</div>' +
        '<div class="cd-foot"><button type="button" class="cd-cancel">Cancel</button><button type="button" class="cd-ok">Set Time</button></div>';
    document.body.appendChild(panel);

    var dial = panel.querySelector('.cd-dial'), line = dial.querySelector('line'), knob = dial.querySelector('.cd-knob');
    var okBtn = panel.querySelector('.cd-ok');
    var nums = [];
    for (var i = 0; i < 12; i++) {
        var span = document.createElement('span');
        span.className = 'cd-num';
        span.style.left = (C + R * Math.sin(i * Math.PI / 6)) + 'px';
        span.style.top  = (C - R * Math.cos(i * Math.PI / 6)) + 'px';
        dial.appendChild(span);
        nums.push(span);
    }

    // Working copy while the dial is open; only written to the field on "Set Time"
    var state = { h: null, m: null, pm: false, mode: 'h' }, field = null, dragging = false;
    var range = null;   // { min, max } in minutes after midnight, from data-min-time / data-max-time

    // Is this time inside the allowed range? With m === null, asks "is any minute of this hour allowed?"
    function allowed(h12, m, pm) {
        if (! range || h12 === null) return true;
        var start = ((h12 % 12) + (pm ? 12 : 0)) * 60;
        if (m === null) return start + 59 >= range.min && start <= range.max;
        return start + m >= range.min && start + m <= range.max;
    }
    function label12(mins) { var h = Math.floor(mins / 60); return (h % 12 || 12) + ':' + pad(mins % 60) + ' ' + (h >= 12 ? 'PM' : 'AM'); }

    function render() {
        var segH = panel.querySelector('[data-mode="h"]'), segM = panel.querySelector('[data-mode="m"]');
        segH.textContent = state.h === null ? '--' : state.h;
        segM.textContent = state.m === null ? '--' : pad(state.m);
        segH.classList.toggle('on', state.mode === 'h');
        segM.classList.toggle('on', state.mode === 'm');
        panel.querySelectorAll('.cd-ap button').forEach(function (b) { b.classList.toggle('on', (b.dataset.ap === 'PM') === state.pm); });
        var outOfRange = state.h !== null && state.m !== null && ! allowed(state.h, state.m, state.pm);
        panel.querySelector('.cd-hint').textContent = outOfRange
            ? 'Choose a time from ' + label12(range.min) + ' to ' + label12(range.max)
            : (state.mode === 'h' ? 'Pick the hour' : 'Pick the minutes');

        nums.forEach(function (span, i) {
            var val = state.mode === 'h' ? (i || 12) : i * 5;
            span.textContent = state.mode === 'h' ? val : pad(val);
            span.classList.toggle('on', state.mode === 'h' ? state.h === val : state.m === val);
            span.classList.toggle('off', state.mode === 'h' ? ! allowed(val, null, state.pm) : ! allowed(state.h, val, state.pm));
        });

        var value = state.mode === 'h' ? state.h : state.m;
        line.style.display = knob.style.display = value === null ? 'none' : '';
        if (value !== null) {
            var angle = state.mode === 'h' ? (value % 12) * Math.PI / 6 : value * Math.PI / 30;
            var x = C + R * Math.sin(angle), y = C - R * Math.cos(angle);
            line.setAttribute('x2', x); line.setAttribute('y2', y);
            knob.setAttribute('cx', x); knob.setAttribute('cy', y);
            // Big knob behind a number; small dot for an in-between minute such as :57
            knob.setAttribute('r', state.mode === 'm' && value % 5 ? 5 : 16);
        }
        okBtn.disabled = state.h === null || state.m === null || outOfRange;
    }

    // Choose an hour; keep the minutes if they still fit, otherwise jump to the first allowed minute
    function chooseHour(h) {
        if (! allowed(h, null, state.pm)) return false;
        state.h = h;
        if (state.m === null || ! allowed(h, state.m, state.pm)) {
            state.m = null;
            for (var m = 0; m < 60; m += 5) { if (allowed(h, m, state.pm)) { state.m = m; break; } }
        }
        return true;
    }

    // Turn a pointer position on the dial into an hour (1–12) or minute (5-minute steps)
    function pickAt(e) {
        var rect = dial.getBoundingClientRect();
        var deg = Math.atan2(e.clientX - rect.left - rect.width / 2, rect.top + rect.height / 2 - e.clientY) * 180 / Math.PI;
        var step = Math.round(((deg + 360) % 360) / 30) % 12;
        if (state.mode === 'h') {
            if (chooseHour(step || 12)) pickedHour = true;
        } else if (allowed(state.h, step * 5, state.pm)) {
            state.m = step * 5;
        }
        render();
    }

    // Move on to minutes only if this click/drag actually landed on an allowed hour
    var pickedHour = false;
    dial.addEventListener('pointerdown', function (e) { dragging = true; pickedHour = false; dial.setPointerCapture(e.pointerId); pickAt(e); });
    dial.addEventListener('pointermove', function (e) { if (dragging) pickAt(e); });
    dial.addEventListener('pointerup', function () {
        if (! dragging) return;
        dragging = false;
        if (state.mode === 'h' && pickedHour) setTimeout(function () { state.mode = 'm'; render(); }, 250);
    });
    dial.addEventListener('keydown', function (e) {
        var dir = { ArrowUp: 1, ArrowRight: 1, ArrowDown: -1, ArrowLeft: -1 }[e.key];
        if (dir) {
            e.preventDefault();
            // Step to the next allowed hour / 5-minute mark, skipping greyed-out ones
            for (var tries = 0; tries < 12; tries++) {
                if (state.mode === 'h') {
                    if (chooseHour(((((state.h || 12) % 12) + dir * (tries + 1) + 24) % 12) || 12)) break;
                } else {
                    var m = (Math.round((state.m || 0) / 5) * 5 + dir * 5 * (tries + 1) + 600) % 60;
                    if (allowed(state.h, m, state.pm)) { state.m = m; break; }
                }
            }
            render();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (state.mode === 'h') { state.mode = 'm'; render(); } else if (! okBtn.disabled) apply();
        }
    });

    panel.querySelectorAll('.cd-seg').forEach(function (b) {
        b.addEventListener('click', function () { state.mode = b.dataset.mode; render(); dial.focus(); });
    });
    panel.querySelectorAll('.cd-ap button').forEach(function (b) {
        b.addEventListener('click', function () { state.pm = b.dataset.ap === 'PM'; render(); });
    });
    panel.querySelector('.cd-cancel').addEventListener('click', close);
    okBtn.addEventListener('click', apply);

    function to24(v) {   // "HH:MM" → "HH:MM" normalised, or '' if not a time
        var p = (v || '').split(':'), h = parseInt(p[0], 10), m = parseInt(p[1], 10);
        return isNaN(h) || isNaN(m) ? '' : pad(h) + ':' + pad(m);
    }

    function showOnField(btn, value) {
        var text = btn.querySelector('.cd-text');
        if (value) {
            var h = parseInt(value, 10);
            text.textContent = (h % 12 || 12) + ':' + value.slice(3, 5) + ' ' + (h >= 12 ? 'PM' : 'AM');
        } else {
            text.textContent = 'Select time';
        }
        text.classList.toggle('empty', ! value);
    }

    function fieldFor(id) { return document.querySelector('.cd-field[data-picker="time"][data-for="' + id + '"]'); }

    function apply() {
        var hidden = document.getElementById(field.dataset.for);
        hidden.value = pad((state.h % 12) + (state.pm ? 12 : 0)) + ':' + pad(state.m);   // 12 AM → 00, 12 PM → 12
        showOnField(field, hidden.value);
        field.classList.remove('has-error');
        var f = field;
        close();
        f.focus();
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function position() {
        if (! field) return;
        var r = field.getBoundingClientRect(), pw = panel.offsetWidth, ph = panel.offsetHeight;
        var left = Math.max(8, Math.min(r.right - pw, window.innerWidth - pw - 8));
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
        var value = to24(hidden.value);
        var toMins = function (v) { v = to24(v); return v ? parseInt(v, 10) * 60 + parseInt(v.slice(3), 10) : null; };
        var lo = toMins(hidden.dataset.minTime), hi = toMins(hidden.dataset.maxTime);
        range = lo === null && hi === null ? null : { min: lo === null ? 0 : lo, max: hi === null ? 1439 : hi };
        if (value) {
            var h = parseInt(value, 10);
            state = { h: h % 12 || 12, m: parseInt(value.slice(3), 10), pm: h >= 12, mode: 'h' };
        } else {
            state = { h: null, m: null, pm: new Date().getHours() >= 12, mode: 'h' };
        }
        btn.classList.add('active');
        render();
        panel.classList.add('open');
        position();
        dial.focus();
    }

    function close() {
        if (field) field.classList.remove('active');
        field = null;
        dragging = false;
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

    window.ClockDial = {
        open: open,
        set: function (id, value) {
            var hidden = document.getElementById(id);
            hidden.value = to24(value);
            showOnField(fieldFor(id), hidden.value);
        },
    };

    // Show whatever the server filled in (e.g. old input after a failed submit)
    document.querySelectorAll('.cd-field[data-picker="time"]').forEach(function (btn) {
        showOnField(btn, to24(document.getElementById(btn.dataset.for).value));
    });
})();
</script>
