/* i-Page dashboard chart helpers (Chart.js 2.x). Colours follow light/dark mode. */
(function (w) {
    var palette = ['#6259ca', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444', '#ec4899', '#64748b'];

    function dark() { return document.body.classList.contains('dark-theme'); }
    function text() { return dark() ? '#cbd5e1' : '#64748b'; }
    function grid() { return dark() ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)'; }

    function axes(stacked) {
        return {
            xAxes: [{ stacked: !!stacked, gridLines: { display: false }, ticks: { fontColor: text(), maxTicksLimit: 10 } }],
            yAxes: [{ stacked: !!stacked, gridLines: { color: grid() }, ticks: { fontColor: text(), beginAtZero: true, precision: 0 } }]
        };
    }

    function gradient(ctx, color) {
        var g = ctx.createLinearGradient(0, 0, 0, 260);
        g.addColorStop(0, color + '55');
        g.addColorStop(1, color + '00');
        return g;
    }

    function empty(id, message) {
        var el = document.getElementById(id);
        if (!el) return;
        var p = document.createElement('p');
        p.className = 'text-muted text-center my-5';
        p.textContent = message || 'No data for this period';
        el.parentNode.replaceChild(p, el);
    }

    function hasData(arr) {
        return (arr || []).some(function (v) { return Number(v) > 0; });
    }

    w.IPChart = {
        palette: palette,

        line: function (id, series, emptyMsg) {
            var el = document.getElementById(id);
            if (!el) return;
            if (!series.datasets.some(function (d) { return hasData(d.data); })) return empty(id, emptyMsg);
            var ctx = el.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: series.labels,
                    datasets: series.datasets.map(function (d, i) {
                        var c = palette[i % palette.length];
                        return { label: d.label, data: d.data, borderColor: c, backgroundColor: gradient(ctx, c), borderWidth: 2, pointRadius: 0, pointHoverRadius: 4, lineTension: 0.35 };
                    })
                },
                options: { maintainAspectRatio: false, legend: { position: 'bottom', labels: { fontColor: text(), usePointStyle: true } }, tooltips: { mode: 'index', intersect: false }, scales: axes() }
            });
        },

        bar: function (id, labels, datasets, emptyMsg, horizontal) {
            var el = document.getElementById(id);
            if (!el) return;
            if (!datasets.some(function (d) { return hasData(d.data); })) return empty(id, emptyMsg);
            new Chart(el.getContext('2d'), {
                type: horizontal ? 'horizontalBar' : 'bar',
                data: {
                    labels: labels,
                    datasets: datasets.map(function (d, i) {
                        return { label: d.label, data: d.data, backgroundColor: palette[i % palette.length], borderRadius: 4, barPercentage: 0.7 };
                    })
                },
                options: { maintainAspectRatio: false, legend: { display: datasets.length > 1, position: 'bottom', labels: { fontColor: text(), usePointStyle: true } }, scales: axes() }
            });
        },

        doughnut: function (id, labels, data, emptyMsg, colors) {
            var el = document.getElementById(id);
            if (!el) return;
            if (!hasData(data)) return empty(id, emptyMsg);
            new Chart(el.getContext('2d'), {
                type: 'doughnut',
                data: { labels: labels, datasets: [{ data: data, backgroundColor: colors || palette, borderWidth: 0 }] },
                options: { maintainAspectRatio: false, cutoutPercentage: 68, legend: { position: 'bottom', labels: { fontColor: text(), usePointStyle: true } } }
            });
        }
    };
})(window);
