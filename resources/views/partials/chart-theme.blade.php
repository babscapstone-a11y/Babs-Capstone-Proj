{{-- Makes Chart.js labels, gridlines and doughnut edges follow the admin light/dark theme. Include right after chart.js. --}}
<script>
(function () {
    if (typeof Chart === 'undefined') return;
    var light = { color: Chart.defaults.color, border: Chart.defaults.borderColor, arc: Chart.defaults.elements.arc.borderColor };

    function applyChartTheme() {
        var dark = document.documentElement.getAttribute('data-theme') === 'dark';
        Chart.defaults.color       = dark ? '#9CA3AF' : light.color;
        Chart.defaults.borderColor = dark ? 'rgba(255,255,255,0.08)' : light.border;
        Chart.defaults.elements.arc.borderColor = dark ? '#131B2C' : light.arc;
        Object.values(Chart.instances).forEach(function (chart) { chart.update('none'); });
    }

    applyChartTheme();
    document.addEventListener('themechange', applyChartTheme);
})();
</script>
