{{-- Table row actions: minimal icon-only buttons, same look on every list page (used by the admin and cashier layouts) --}}
<style>
    .row-acts { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }
    .row-acts form { display: inline-flex; margin: 0; }
    .act-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; padding: 0; margin: 0;
        border-radius: 8px; border: 1.5px solid transparent;
        font-size: .8rem; line-height: 1; font-family: inherit;
        cursor: pointer; text-decoration: none; flex-shrink: 0;
        transition: background .18s ease, color .18s ease, border-color .18s ease;
    }
    .act-btn.act-view    { background: var(--surface); border-color: var(--border); color: var(--dark); }
    .act-btn.act-view:hover    { border-color: var(--primary); color: var(--primary); }
    .act-btn.act-edit    { background: rgba(var(--ink-rgb),0.07); color: var(--dark); }
    .act-btn.act-edit:hover    { background: rgba(var(--ink-rgb),0.14); }
    .act-btn.act-danger  { background: rgba(220,38,38,0.1); color: var(--primary); }
    .act-btn.act-danger:hover  { background: var(--primary); color: var(--white); }
    .act-btn.act-success { background: rgba(22,163,74,0.1); color: var(--green-600); }
    .act-btn.act-success:hover { background: #16A34A; color: var(--white); }
</style>
