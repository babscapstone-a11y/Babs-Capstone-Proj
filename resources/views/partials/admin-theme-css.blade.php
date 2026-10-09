{{--
    Admin colour tokens for light and dark mode.
    Light values match the original hard-coded colours exactly, so light mode looks unchanged.
    Dark mode is switched on by <html data-theme="dark"> (same "babsResto-theme" choice as the landing page).
--}}
<style>
    :root {
        /* Neutral surfaces & text */
        --ink:        #111827;          /* always-dark: sidebar, hero cards (does not flip) */
        --ink-rgb:    17,24,39;         /* used for subtle tints/borders: rgba(var(--ink-rgb), .08) */
        --surface:    #ffffff;          /* cards, panels, inputs */
        --surface-2:  #F8FAFC;          /* table headers, card headers, hovered rows */
        --surface-3:  #F3F4F6;          /* chips, track bars */
        --text-2:     #374151;          /* secondary body text */
        --line:       #F3F4F6;          /* row dividers */
        --line-2:     #E5E7EB;          /* light borders */

        /* Status tints (backgrounds / borders) */
        --red-25: #FFF5F5;  --red-50: #FEF2F2;  --red-100: #FEE2E2;  --red-200: #FECACA;  --red-300: #FCA5A5;
        --green-50: #F0FDF4; --green-100: #DCFCE7; --emerald-50: #ECFDF5; --green-200: #BBF7D0; --green-300: #86EFAC;
        --blue-25: #F8FBFF; --blue-50: #EFF6FF; --blue-100: #DBEAFE; --blue-200: #BFDBFE; --blue-300: #93C5FD;
        --amber-50: #FFFBEB; --amber-100: #FEF3C7; --orange-50: #FFF7ED; --amber-200: #FDE68A; --amber-300: #FCD34D;
        --violet-50: #F5F3FF; --cyan-50: #ECFEFF;

        /* Status text */
        --red-600: #DC2626;   --red-700: #B91C1C;   --red-800: #991B1B;
        --green-600: #16A34A; --green-700: #15803D; --green-800: #166534;
        --blue-600: #2563EB;  --blue-700: #1D4ED8;
        --amber-600: #D97706; --amber-700: #B45309; --amber-800: #92400E;
        --violet-600: #7C3AED; --violet-700: #6D28D9; --violet-800: #5B21B6;
        --orange-700: #C2410C; --cyan-700: #0E7490;
    }

    html[data-theme="dark"] {
        color-scheme: dark;

        --dark:       #E5E7EB;          /* main text colour */
        --muted:      #9CA3AF;
        --bg:         #0B1120;
        --border:     rgba(255,255,255,0.09);
        --ink-rgb:    255,255,255;
        --surface:    #131B2C;
        --surface-2:  #182134;
        --surface-3:  #1F293D;
        --text-2:     #CBD5E1;
        --line:       rgba(255,255,255,0.06);
        --line-2:     rgba(255,255,255,0.12);

        --red-25: rgba(220,38,38,0.08);  --red-50: rgba(220,38,38,0.13);  --red-100: rgba(220,38,38,0.2);
        --red-200: rgba(248,113,113,0.35); --red-300: rgba(248,113,113,0.5);
        --green-50: rgba(22,163,74,0.13); --green-100: rgba(22,163,74,0.2); --emerald-50: rgba(16,185,129,0.13);
        --green-200: rgba(74,222,128,0.3); --green-300: rgba(74,222,128,0.45);
        --blue-25: rgba(37,99,235,0.08); --blue-50: rgba(37,99,235,0.14); --blue-100: rgba(37,99,235,0.22);
        --blue-200: rgba(96,165,250,0.35); --blue-300: rgba(96,165,250,0.5);
        --amber-50: rgba(245,158,11,0.12); --amber-100: rgba(245,158,11,0.2); --orange-50: rgba(249,115,22,0.12);
        --amber-200: rgba(252,211,77,0.3); --amber-300: rgba(252,211,77,0.45);
        --violet-50: rgba(124,58,237,0.16); --cyan-50: rgba(6,182,212,0.14);

        --red-600: #F87171;   --red-700: #FCA5A5;   --red-800: #FECACA;
        --green-600: #4ADE80; --green-700: #86EFAC; --green-800: #BBF7D0;
        --blue-600: #60A5FA;  --blue-700: #93C5FD;
        --amber-600: #FBBF24; --amber-700: #FCD34D; --amber-800: #FDE68A;
        --violet-600: #A78BFA; --violet-700: #C4B5FD; --violet-800: #DDD6FE;
        --orange-700: #FDBA74; --cyan-700: #67E8F9;
    }

    /* Native form controls follow the theme. :where() keeps specificity at zero,
       so any page-specific input style (focus rings, highlighted fields) still wins. */
    :where(html[data-theme="dark"]) :where(input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]), select, textarea) {
        background-color: var(--surface);
        color: var(--dark);
        border-color: rgba(255,255,255,0.14);
    }
    :where(html[data-theme="dark"]) :where(input, textarea)::placeholder { color: rgba(255,255,255,0.35); }

    /* Theme toggle button (top bar) */
    .theme-toggle {
        width: 36px; height: 36px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        background: transparent; border: 1.5px solid var(--border);
        color: var(--dark); cursor: pointer; font-size: .9rem;
        transition: background .18s ease, border-color .18s ease;
    }
    .theme-toggle:hover { background: rgba(var(--ink-rgb),0.06); border-color: var(--primary); color: var(--primary); }
</style>
