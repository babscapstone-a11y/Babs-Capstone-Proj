

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * LiveTable — shared debounced live-search/live-filter helper for admin listing pages.
 *
 * Usage (in a page's @section('scripts')):
 *   LiveTable.init({
 *       formSelector: '#liveFilterForm',
 *       resultsSelector: '#results',
 *       url: '{{ route("users.index") }}',
 *       searchFieldName: 'search',
 *       debounceMs: 300,
 *       statsSelectors: { total: '#statTotal', active: '#statActive' }, // optional
 *   });
 *
 * The target form's fields (the search input + every <select>) are serialized via
 * FormData on every request, so new filters added later need no JS changes. Pagination
 * and sort links rendered inside the results container are intercepted via event
 * delegation (attached once, on the stable results container, so it survives every
 * innerHTML swap) and fetched using their own full query string, since Laravel's
 * ->withQueryString()/fullUrlWithQuery() already bake the current filters into them.
 */
window.LiveTable = (function () {
    function init(opts) {
        const {
            formSelector,
            resultsSelector,
            url,
            searchFieldName = 'search',
            debounceMs = 300,
            statsSelectors = null,
        } = opts;

        const form = document.querySelector(formSelector);
        const results = document.querySelector(resultsSelector);
        if (!form || !results) return;

        const searchInput = form.querySelector(`[name="${searchFieldName}"]`);
        const searchWrap = searchInput ? searchInput.closest('.search-wrap') : null;
        const clearBtn = searchWrap ? searchWrap.querySelector('.search-clear') : null;

        let debounceTimer = null;
        let currentAbort = null;

        const resultsUrlPath = (() => {
            try { return new URL(url, window.location.origin).pathname; } catch (e) { return url; }
        })();

        function toggleClearVisibility() {
            if (!searchWrap) return;
            searchWrap.classList.toggle('has-value', !!(searchInput && searchInput.value));
        }

        function fetchAndSwap(params) {
            if (currentAbort) currentAbort.abort();
            currentAbort = new AbortController();

            const qs = params.toString();
            const newUrl = url + (qs ? '?' + qs : '');
            history.replaceState(null, '', newUrl);

            results.classList.add('is-loading');

            fetch(newUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: currentAbort.signal,
            })
                .then((r) => r.json())
                .then((data) => {
                    results.innerHTML = data.html;
                    if (statsSelectors && data.stats) {
                        Object.keys(statsSelectors).forEach((key) => {
                            const el = document.querySelector(statsSelectors[key]);
                            if (el && data.stats[key] !== undefined) el.textContent = data.stats[key];
                        });
                    }
                })
                .catch((err) => {
                    if (err.name !== 'AbortError') console.error('LiveTable fetch failed', err);
                })
                .finally(() => {
                    results.classList.remove('is-loading');
                });
        }

        function fetchFromForm() {
            const fd = new FormData(form);
            const params = new URLSearchParams();
            for (const [key, value] of fd.entries()) {
                if (value !== '') params.set(key, value);
            }
            fetchAndSwap(params);
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                toggleClearVisibility();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchFromForm, debounceMs);
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                toggleClearVisibility();
                clearTimeout(debounceTimer);
                fetchFromForm();
            });
        }

        form.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => {
                clearTimeout(debounceTimer);
                fetchFromForm();
            });
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            clearTimeout(debounceTimer);
            fetchFromForm();
        });

        // Event delegation on the stable results container — survives every innerHTML swap.
        results.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (!link) return;
            let linkUrl;
            try {
                linkUrl = new URL(link.href, window.location.origin);
            } catch (err) {
                return;
            }
            if (linkUrl.pathname !== resultsUrlPath) return;
            e.preventDefault();
            clearTimeout(debounceTimer);
            fetchAndSwap(linkUrl.searchParams);
        });

        toggleClearVisibility();
    }

    return { init };
})();
