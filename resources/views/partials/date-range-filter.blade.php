{{--
    From / To date filter for list pages (Stock-In, RTC Conversion History, Inventory Adjustments).
    Uses the calendar pop-up (partials/date-picker). Submits as "from" and "to" (Y-m-d), keeps the
    current request values, and stops "To" from being before "From". Include inside the filter <form>.
--}}
<input type="hidden" name="from" id="filterFrom" value="{{ request('from') }}">
<button type="button" class="cd-field filter-date-btn" data-picker="date" data-for="filterFrom" data-placeholder="From date" data-clearable
        aria-haspopup="dialog" aria-label="From date" onclick="DatePick.open(this)">
    <span class="cd-text"></span><i class="fas fa-calendar-days"></i>
</button>
<input type="hidden" name="to" id="filterTo" value="{{ request('to') }}" data-min="{{ request('from') }}">
<button type="button" class="cd-field filter-date-btn" data-picker="date" data-for="filterTo" data-placeholder="To date" data-clearable
        aria-haspopup="dialog" aria-label="To date" onclick="DatePick.open(this)">
    <span class="cd-text"></span><i class="fas fa-calendar-days"></i>
</button>

@once
<style>
/* Sized like the other filter controls; calendar in the admin red */
:root { --cd-accent: var(--primary); --cd-accent-rgb: 220,38,38; }
.filter-bar .filter-date-btn { flex: 0 0 165px; height: auto; padding: .55rem .9rem; font-size: .83rem; }
</style>
@include('partials.clock-dial')
@include('partials.date-picker')
<script>
// Keep "To" on or after "From"
document.getElementById('filterFrom').addEventListener('change', function () {
    var to = document.getElementById('filterTo');
    to.dataset.min = this.value;
    if (this.value && to.value && to.value < this.value) DatePick.set('filterTo', '');
});
</script>
@endonce
