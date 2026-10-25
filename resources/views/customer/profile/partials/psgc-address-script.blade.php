{{--
    Cascading Province → City/Municipality → Barangay dropdowns for the customer profile.
    Lists come live from the free PSGC API (official Philippine Standard Geographic Codes):
    https://psgc.gitlab.io/api  — no key needed, allows browser requests.

    The <select>s are not submitted. Each choice's name is copied into hidden inputs
    (province / municipality / barangay), which start out holding the saved address. So if the
    API can't be reached, saving the profile keeps the address. The PSGC codes are only used in
    the browser to load the next list; only the names are saved, and the dropdowns pre-select
    by name (each list holds just the places inside the one above it, where names are unique).
--}}
<script>
(function () {
    const API = 'https://psgc.gitlab.io/api';

    // Metro Manila has no province in PSGC, so it is offered as its own "province"
    const NCR = { code: '130000000', name: 'Metro Manila (NCR)' };
    // Two independent cities have no province either; list them under the province people expect
    const EXTRA_CITIES = {
        '150700000': [{ code: '099701000', name: 'City of Isabela' }],   // Basilan
        '153800000': [{ code: '129804000', name: 'City of Cotabato' }],  // Maguindanao
    };

    const el = id => document.getElementById(id);
    const sel    = { province: el('addrProvince'),     municipality: el('addrMunicipality'),     barangay: el('addrBarangay') };
    const name   = { province: el('addrProvinceName'), municipality: el('addrMunicipalityName'), barangay: el('addrBarangayName') };
    const notice = el('addrNotice');
    if (!sel.province) return;

    // What was saved when the page loaded (used to pre-select, and to restore on "Reset")
    const saved = {};
    ['province', 'municipality', 'barangay'].forEach(k => saved[k] = name[k].value);

    const cache = {};
    async function getList(path) {
        if (cache[path]) return cache[path];
        const ctrl = new AbortController();
        const timer = setTimeout(() => ctrl.abort(), 10000);
        try {
            const res = await fetch(API + path, { signal: ctrl.signal });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return cache[path] = await res.json();
        } finally {
            clearTimeout(timer);
        }
    }

    function fill(select, items, placeholder) {
        select.innerHTML = '';
        select.add(new Option(placeholder, ''));
        items.slice().sort((a, b) => a.name.localeCompare(b.name))
             .forEach(it => select.add(new Option(it.name, it.code)));
        select.disabled = false;
    }

    function reset(select, placeholder) {
        select.innerHTML = '';
        select.add(new Option(placeholder, ''));
        select.disabled = true;
    }

    // Pre-select the saved name (case-insensitive) in a freshly loaded list
    function preselect(level, savedName) {
        const select = sel[level];
        const wanted = (savedName || '').trim().toLowerCase();
        const opt = wanted && Array.from(select.options).find(o => o.value && o.text.toLowerCase() === wanted);
        if (opt) {
            select.value = opt.value;
            name[level].value = opt.text;
        }
        return !!opt;
    }

    function store(level) {
        const select = sel[level];
        name[level].value = select.value ? select.options[select.selectedIndex].text : '';
    }

    function clearBelow(level) {
        const order = ['province', 'municipality', 'barangay'];
        order.slice(order.indexOf(level) + 1).forEach(k => { name[k].value = ''; });
    }

    function showNotice(html) {
        notice.innerHTML = html;
        notice.style.display = html ? 'block' : 'none';
    }

    async function loadMunicipalities(provinceCode) {
        reset(sel.municipality, 'Loading…');
        reset(sel.barangay, 'Select a city / municipality first');
        const path = provinceCode === NCR.code
            ? `/regions/${NCR.code}/cities-municipalities.json`
            : `/provinces/${provinceCode}/cities-municipalities.json`;
        const items = (await getList(path)).concat(EXTRA_CITIES[provinceCode] || []);
        fill(sel.municipality, items, 'Select city / municipality');
    }

    async function loadBarangays(municipalityCode) {
        reset(sel.barangay, 'Loading…');
        fill(sel.barangay, await getList(`/cities-municipalities/${municipalityCode}/barangays.json`), 'Select barangay');
    }

    function failed() {
        ['province', 'municipality', 'barangay'].forEach(k => {
            if (sel[k].disabled) reset(sel[k], 'Unavailable right now');
        });
        const current = [name.barangay.value, name.municipality.value, name.province.value].filter(Boolean).join(', ');
        showNotice('<i class="fas fa-triangle-exclamation" style="color:#D97706"></i> The address list couldn\'t be loaded (check your internet connection).'
            + (current ? ' Your saved address is kept: <strong>' + current.replace(/</g, '&lt;') + '</strong>.' : ''));
    }

    // Load the lists and select the saved address, step by step
    async function restore(target) {
        showNotice('');
        // Hidden inputs aren't reverted by a form reset, so write the target address back explicitly
        ['province', 'municipality', 'barangay'].forEach(k => { name[k].value = target[k]; });
        try {
            reset(sel.municipality, 'Select a province first');
            reset(sel.barangay, 'Select a city / municipality first');
            const provinces = (await getList('/provinces.json')).concat([NCR]);
            fill(sel.province, provinces, 'Select province');

            // An old typed-in address that isn't in the official list stays saved (hidden inputs)
            // until the customer picks a new one; the dropdowns simply start unselected.
            if (!preselect('province', target.province)) return;
            await loadMunicipalities(sel.province.value);
            if (!preselect('municipality', target.municipality)) return;
            await loadBarangays(sel.municipality.value);
            preselect('barangay', target.barangay);
        } catch (e) {
            failed();
        }
    }

    sel.province.addEventListener('change', async () => {
        store('province'); clearBelow('province');
        if (!sel.province.value) {
            reset(sel.municipality, 'Select a province first');
            reset(sel.barangay, 'Select a city / municipality first');
            return;
        }
        try { await loadMunicipalities(sel.province.value); } catch (e) { failed(); }
    });

    sel.municipality.addEventListener('change', async () => {
        store('municipality'); clearBelow('municipality');
        if (!sel.municipality.value) { reset(sel.barangay, 'Select a city / municipality first'); return; }
        try { await loadBarangays(sel.municipality.value); } catch (e) { failed(); }
    });

    sel.barangay.addEventListener('change', () => store('barangay'));

    // The form's "Reset" button puts the hidden inputs back; put the dropdowns back to match
    document.getElementById('profileForm')?.addEventListener('reset', () => setTimeout(() => restore(saved), 0));

    restore(saved);
})();
</script>
