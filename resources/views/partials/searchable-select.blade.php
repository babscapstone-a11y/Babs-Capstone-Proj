{{--
    Searchable dropdowns for long item lists. Add data-searchable to any <select>:
        <select name="inventory_item_id" data-searchable data-search-placeholder="Search raw items…">
    Clicking the select (or Space / Enter / ↓) opens a search panel instead of the native list.
    The real <select> stays in place and keeps working as usual: picking an item sets its value and fires
    its normal "change" event, so existing page scripts (stock previews, calculations) and form submission
    are unchanged. Options a page hides (style display:none / hidden) or disables are respected.
--}}
<style>
    select[data-searchable] { cursor: pointer; }
    .ss-panel {
        position: fixed; z-index: 3000; display: none;
        background: var(--surface, #fff); color: var(--dark, #111827);
        border: 1px solid var(--line-2, #E5E7EB); border-radius: 12px;
        box-shadow: 0 18px 44px rgba(17,24,39,0.22); overflow: hidden;
    }
    .ss-panel.open { display: block; }
    .ss-search-wrap { position: relative; padding: .55rem; border-bottom: 1px solid var(--border, rgba(17,24,39,.08)); }
    .ss-search-wrap i { position: absolute; left: 1.15rem; top: 50%; transform: translateY(-50%); color: var(--muted, #6B7280); font-size: .8rem; pointer-events: none; }
    .ss-search {
        width: 100%; box-sizing: border-box; padding: .55rem .75rem .55rem 2.1rem;
        border: 1.5px solid var(--line-2, #E5E7EB); border-radius: 9px; outline: none;
        font-family: inherit; font-size: .85rem; background: var(--surface, #fff); color: var(--dark, #111827);
    }
    .ss-search:focus { border-color: var(--primary, #DC2626); box-shadow: 0 0 0 3px rgba(220,38,38,.1); }
    .ss-list { max-height: 280px; overflow-y: auto; padding: .3rem; margin: 0; list-style: none; }
    .ss-option {
        position: relative; display: block;
        padding: .5rem 1.9rem .5rem .7rem; border-radius: 8px; font-size: .85rem; cursor: pointer;
        line-height: 1.4;
    }
    .ss-option.active { background: rgba(220,38,38,0.1); color: var(--primary, #DC2626); }
    .ss-option.selected { font-weight: 700; }
    .ss-option.selected::after {
        content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: .75rem;
        color: var(--primary, #DC2626); position: absolute; right: .7rem; top: 50%; transform: translateY(-50%);
    }
    .ss-option.disabled { opacity: .45; cursor: not-allowed; }
    .ss-option mark { background: rgba(245,158,11,0.3); color: inherit; border-radius: 3px; padding: 0 1px; }
    .ss-empty { padding: 1rem; text-align: center; font-size: .82rem; color: var(--muted, #6B7280); }
    .ss-count { padding: .35rem .8rem .5rem; font-size: .72rem; color: var(--muted, #6B7280); border-top: 1px solid var(--border, rgba(17,24,39,.08)); }
</style>
<script>
(function () {
    let current = null;            // { select, panel, search, list, items, activeIndex }

    const panel = document.createElement('div');
    panel.className = 'ss-panel';
    panel.setAttribute('role', 'dialog');
    panel.innerHTML = '<div class="ss-search-wrap"><i class="fas fa-search"></i>'
        + '<input type="text" class="ss-search" autocomplete="off" aria-label="Search options"></div>'
        + '<ul class="ss-list" role="listbox"></ul><div class="ss-count"></div>';
    const search = panel.querySelector('.ss-search');
    const list   = panel.querySelector('.ss-list');
    const count  = panel.querySelector('.ss-count');

    function ready(fn) { document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn(); }
    ready(() => document.body.appendChild(panel));

    const esc = s => s.replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // Options the page currently offers (skips the empty placeholder and any it has hidden)
    function choices(select) {
        return Array.from(select.options).filter(o =>
            o.value !== '' && !o.hidden && o.style.display !== 'none' && !(o.parentElement && o.parentElement.style && o.parentElement.style.display === 'none'));
    }

    function render() {
        const q = search.value.trim().toLowerCase();
        const all = choices(current.select);
        current.items = all.filter(o => !q || o.text.toLowerCase().includes(q));
        list.innerHTML = current.items.map((o, i) => {
            const text = o.text;
            let html = esc(text);
            if (q) {
                const at = text.toLowerCase().indexOf(q);
                html = esc(text.slice(0, at)) + '<mark>' + esc(text.slice(at, at + q.length)) + '</mark>' + esc(text.slice(at + q.length));
            }
            const cls = ['ss-option', o.disabled ? 'disabled' : '', o.selected ? 'selected' : '', i === current.activeIndex ? 'active' : ''].join(' ');
            return `<li class="${cls}" role="option" data-i="${i}" aria-selected="${o.selected}">${html}</li>`;
        }).join('') || `<li class="ss-empty">No items match “${esc(search.value.trim())}”.</li>`;
        count.textContent = q ? `${current.items.length} of ${all.length} items` : `${all.length} items`;
        const active = list.querySelector('.ss-option.active');
        if (active) active.scrollIntoView({ block: 'nearest' });
    }

    function place() {
        const r = current.select.getBoundingClientRect();
        const below = window.innerHeight - r.bottom;
        panel.style.width = Math.max(r.width, 240) + 'px';
        panel.style.left  = Math.min(r.left, window.innerWidth - Math.max(r.width, 240) - 8) + 'px';
        if (below < 300 && r.top > below) {           // not enough room below: open upwards
            panel.style.top = ''; panel.style.bottom = (window.innerHeight - r.top + 4) + 'px';
        } else {
            panel.style.bottom = ''; panel.style.top = (r.bottom + 4) + 'px';
        }
    }

    function open(select) {
        if (select.disabled) return;
        current = { select, items: [], activeIndex: 0 };
        search.value = '';
        search.placeholder = select.dataset.searchPlaceholder || 'Type to search…';
        const sel = choices(select).indexOf(select.selectedOptions[0]);
        current.activeIndex = Math.max(0, sel);
        render();
        place();
        panel.classList.add('open');
        select.setAttribute('aria-expanded', 'true');
        setTimeout(() => search.focus(), 0);
    }

    function close(refocus) {
        if (!current) return;
        panel.classList.remove('open');
        current.select.setAttribute('aria-expanded', 'false');
        if (refocus) current.select.focus();
        current = null;
    }

    function choose(i) {
        const opt = current.items[i];
        if (!opt || opt.disabled) return;
        const select = current.select;
        select.value = opt.value;
        select.dispatchEvent(new Event('input', { bubbles: true }));
        select.dispatchEvent(new Event('change', { bubbles: true }));
        close(true);
    }

    search.addEventListener('input', () => { current.activeIndex = 0; render(); });
    search.addEventListener('keydown', e => {
        if (!current) return;
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            const n = current.items.length;
            if (n) { current.activeIndex = (current.activeIndex + (e.key === 'ArrowDown' ? 1 : -1) + n) % n; render(); }
        } else if (e.key === 'Enter') {
            e.preventDefault(); choose(current.activeIndex);
        } else if (e.key === 'Escape') {
            e.preventDefault(); e.stopPropagation(); close(true);
        } else if (e.key === 'Tab') {
            close(false);
        }
    });
    list.addEventListener('mousedown', e => e.preventDefault());      // keep focus in the search box
    list.addEventListener('click', e => {
        const li = e.target.closest('.ss-option');
        if (li) choose(+li.dataset.i);
    });
    list.addEventListener('mousemove', e => {
        const li = e.target.closest('.ss-option');
        if (li && +li.dataset.i !== current.activeIndex) { current.activeIndex = +li.dataset.i; render(); }
    });

    // Open our panel instead of the native list
    document.addEventListener('mousedown', e => {
        const select = e.target.closest && e.target.closest('select[data-searchable]');
        if (select) {
            e.preventDefault();
            current && current.select === select ? close(true) : (close(false), open(select));
            return;
        }
        if (current && !panel.contains(e.target)) close(false);
    });
    document.addEventListener('keydown', e => {
        const select = e.target.matches && e.target.matches('select[data-searchable]') ? e.target : null;
        if (select && [' ', 'Enter', 'ArrowDown', 'ArrowUp'].includes(e.key)) { e.preventDefault(); open(select); }
    });
    window.addEventListener('resize', () => current && place());
    document.addEventListener('scroll', e => { if (current && !panel.contains(e.target)) place(); }, true);
})();
</script>
