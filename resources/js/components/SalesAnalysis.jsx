import React, { useEffect, useMemo, useState } from 'react';
import { Line } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Tooltip,
    Filler,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Filler);

function formatRupiahFull(value) {
    const n = Number(value || 0);
    return `Rp ${Math.round(n).toLocaleString('id-ID')}`;
}

// Label sumbu Y: Rp X.Xjt / Rp X.Xrb / Rp XM
function formatRupiahAxis(value) {
    const n = Number(value || 0);
    const abs = Math.abs(n);
    if (abs >= 1e9) return `Rp ${(n / 1e9).toFixed(0)}M`;
    if (abs >= 1e6) return `Rp ${(n / 1e6).toFixed(1)}jt`;
    if (abs >= 1e3) return `Rp ${(n / 1e3).toFixed(1)}rb`;
    return `Rp ${n.toLocaleString('id-ID')}`;
}

function fmtMonthLabel(ym) {
    if (!ym) return '-';
    const [y, m] = String(ym).split('-').map((x) => parseInt(x, 10));
    const d = new Date(y, (m || 1) - 1, 1);
    if (Number.isNaN(d.getTime())) return ym;
    return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
}

function buildRecentMonthOptions(count = 24) {
    const now = new Date();
    return Array.from({ length: count }, (_, i) => {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const ym = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
        return { value: ym, label: d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) };
    });
}

function totalFromDaily(daily) {
    return daily.reduce((acc, v) => acc + (Number(v) || 0), 0);
}

function activeDaysFromDaily(daily) {
    return daily.filter((v) => Number(v) > 0).length;
}

function normalizeDailyValue(cell) {
    if (cell == null) return 0;
    if (typeof cell === 'number') return Number(cell) || 0;
    if (typeof cell === 'string') return Number(cell) || 0;
    if (typeof cell === 'object') {
        // slot bisa { total: number } atau bentuk lain, ambil total jika ada
        if (typeof cell.total === 'number' || typeof cell.total === 'string') return Number(cell.total) || 0;
        return 0;
    }
    return 0;
}

function normalizeDailyFromApi(rawDaily) {
    const src = Array.isArray(rawDaily) ? rawDaily : [];
    // pad/truncate ke 31
    return Array.from({ length: 31 }, (_, idx) => normalizeDailyValue(src[idx]));
}

function toCumulative(daily) {
    let acc = 0;
    return daily.map((v) => {
        acc += Number(v) || 0;
        return acc;
    });
}

async function fetchSalesAnalysis({ month, mode, compareMonth }) {
    const params = new URLSearchParams({ month, mode });
    if (mode === 'compare' && compareMonth) params.set('compare_month', compareMonth);
    const res = await fetch(`/api/dashboard/sales-analysis?${params.toString()}`, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });
    const json = await res.json().catch(() => ({}));
    if (import.meta?.env?.DEV) {
        // debug raw response during development
        // eslint-disable-next-line no-console
        console.log('[SalesAnalysis] raw response', json);
    }
    if (!res.ok) throw new Error(json.error || 'Gagal memuat analisis penjualan');
    return json;
}

export default function SalesAnalysis({ initialMonth = '' }) {
    const monthOptions = useMemo(() => buildRecentMonthOptions(24), []);
    const defaultMonth = initialMonth || monthOptions[0]?.value || '';
    const defaultCompare = monthOptions[1]?.value || '';

    const [mode, setMode] = useState('compare'); // 'compare' | 'single'
    const [month, setMonth] = useState(defaultMonth);
    const [compareMonth, setCompareMonth] = useState(defaultCompare);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    // Chart.js canvas TIDAK resolve CSS var() otomatis untuk warna.
    // Jadi kita baca CSS variables lalu kirim warna final ke Chart.js.
    const [theme, setTheme] = useState(() => ({
        linePrimary: '#3b82f6',
        lineCompare: '#f97316',
        grid: 'rgba(255,255,255,0.10)',
        axis: 'rgba(255,255,255,0.50)',
    }));

    useEffect(() => {
        const el = document.getElementById('sales-analysis-root') || document.documentElement;
        const cs = getComputedStyle(el);
        const readVar = (name, fallback) => {
            const v = (cs.getPropertyValue(name) || '').trim();
            return v || fallback;
        };
        setTheme({
            linePrimary: readVar('--sa-line-primary', '#3b82f6'),
            lineCompare: readVar('--sa-line-compare', '#f97316'),
            grid: readVar('--sa-grid', 'rgba(255,255,255,0.10)'),
            axis: readVar('--sa-axis', 'rgba(255,255,255,0.50)'),
        });
    }, []);

    const labels = useMemo(() => Array.from({ length: 31 }, (_, i) => String(i + 1)), []);
    const [primaryDaily, setPrimaryDaily] = useState(() => Array.from({ length: 31 }, () => 0));
    const [compareDaily, setCompareDaily] = useState(() => Array.from({ length: 31 }, () => 0));

    useEffect(() => {
        let cancelled = false;
        async function run() {
            if (!month) return;
            setLoading(true);
            setError('');
            try {
                const data = await fetchSalesAnalysis({
                    month,
                    mode,
                    compareMonth: mode === 'compare' ? compareMonth : '',
                });

                if (cancelled) return;

                const rawPrimary =
                    data?.primary_daily ??
                    data?.daily ??
                    data?.data ??
                    [];
                const primary = normalizeDailyFromApi(rawPrimary);

                const rawCompare =
                    mode === 'compare'
                        ? (data?.compare?.daily ?? data?.compare?.primary_daily ?? data?.compare?.data ?? [])
                        : [];
                const compare = mode === 'compare' ? normalizeDailyFromApi(rawCompare) : Array.from({ length: 31 }, () => 0);

                setPrimaryDaily(primary);
                setCompareDaily(compare);

                // broadcast ke dashboard untuk header card & top products
                const activeDays = activeDaysFromDaily(primary);
                const totalTransactions = Number(data?.summary?.total_transactions) || 0;
                window.dispatchEvent(
                    new CustomEvent('sa:summary', {
                        detail: {
                            month,
                            monthLabel: data.month_label || fmtMonthLabel(month),
                            mode,
                            totalSales: totalFromDaily(primary),
                            activeDays,
                            totalTransactions,
                        },
                    })
                );
                window.dispatchEvent(new CustomEvent('sa:monthChange', { detail: { month } }));
            } catch (e) {
                if (!cancelled) setError(e?.message || String(e));
            } finally {
                if (!cancelled) setLoading(false);
            }
        }
        run();
        return () => {
            cancelled = true;
        };
    }, [month, compareMonth, mode]);

    const monthMainLabel = useMemo(() => fmtMonthLabel(month), [month]);
    const monthCompareLabel = useMemo(() => fmtMonthLabel(compareMonth), [compareMonth]);

    const totalUtama = useMemo(() => totalFromDaily(primaryDaily), [primaryDaily]);
    const hariAktif = useMemo(() => activeDaysFromDaily(primaryDaily), [primaryDaily]);
    const rataRataHariAktif = useMemo(
        () => (hariAktif > 0 ? totalUtama / hariAktif : 0),
        [hariAktif, totalUtama]
    );

    const totalBanding = useMemo(() => totalFromDaily(compareDaily), [compareDaily]);
    const delta = useMemo(() => totalUtama - totalBanding, [totalUtama, totalBanding]);
    const deltaPct = useMemo(() => {
        if (mode !== 'compare') return null;
        if (!totalBanding) return null;
        return (delta / totalBanding) * 100;
    }, [mode, totalBanding, delta]);

    const chartData = useMemo(() => {
        // Agar garis benar-benar "mengalir" kiri→kanan dan naik dari bawah→atas,
        // plot dalam bentuk KUMULATIF per hari (running total).
        const primarySeries = toCumulative(primaryDaily);
        const compareSeries = toCumulative(compareDaily);

        const dsPrimary = {
            label: monthMainLabel,
            data: primarySeries,
            borderColor: theme.linePrimary,
            pointBackgroundColor: theme.linePrimary,
            pointBorderColor: theme.linePrimary,
            backgroundColor: 'transparent',
            borderWidth: 3,
            tension: 0.25,
            fill: false,
            pointRadius: 0,
            pointHoverRadius: 4,
            spanGaps: false,
        };

        if (mode === 'single') return { labels, datasets: [dsPrimary] };

        const dsCompare = {
            label: monthCompareLabel,
            data: compareSeries,
            borderColor: theme.lineCompare,
            pointBackgroundColor: theme.lineCompare,
            pointBorderColor: theme.lineCompare,
            backgroundColor: 'transparent',
            borderWidth: 3,
            borderDash: [6, 6],
            tension: 0.25,
            fill: false,
            pointRadius: 0,
            pointHoverRadius: 4,
            spanGaps: false,
        };

        return { labels, datasets: [dsPrimary, dsCompare] };
    }, [labels, primaryDaily, compareDaily, mode, monthMainLabel, monthCompareLabel, theme]);

    const options = useMemo(() => {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    displayColors: true,
                    callbacks: {
                        label(context) {
                            const label = context.dataset?.label ? `${context.dataset.label}: ` : '';
                            const v = context.parsed?.y;
                            if (v == null || Number.isNaN(v)) return '';
                            return `${label}${formatRupiahFull(v)}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: theme.grid },
                    ticks: {
                        color: theme.axis,
                        font: { size: 10 },
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 16,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: theme.grid },
                    ticks: {
                        color: theme.axis,
                        font: { size: 10 },
                        callback: (v) => formatRupiahAxis(v),
                    },
                },
            },
        };
    }, [theme]);

    const deltaTone = delta === 0 ? 'neutral' : delta > 0 ? 'up' : 'down';
    const deltaText = useMemo(() => {
        if (mode !== 'compare') return null;
        if (!totalBanding) return 'Belum ada data bulan pembanding';
        const sign = delta > 0 ? '+' : '';
        // HCD: pengguna lebih mudah paham selisih rupiah daripada persen
        return `${sign}${formatRupiahFull(delta)}`;
    }, [mode, totalBanding, delta, deltaPct]);

    return (
        <section className="sa-panel" aria-label="Analisis penjualan">
            <header className="sa-head">
                <div className="sa-title">Grafik Penjualan</div>
                <div className="sa-toggle" role="group" aria-label="Mode analisis penjualan">
                    <button
                        type="button"
                        className={`sa-toggle-btn ${mode === 'compare' ? 'is-active' : ''}`}
                        onClick={() => setMode('compare')}
                    >
                        Bandingkan
                    </button>
                    <button
                        type="button"
                        className={`sa-toggle-btn ${mode === 'single' ? 'is-active' : ''}`}
                        onClick={() => setMode('single')}
                    >
                        Satu bulan
                    </button>
                </div>
            </header>

            <div className="sa-controls">
                <label className="sa-field">
                    <span className="sa-label">Bulan</span>
                    <select className="sa-select" value={month} onChange={(e) => setMonth(e.target.value)}>
                        {monthOptions.map((o) => (
                            <option key={o.value} value={o.value}>
                                {o.label}
                            </option>
                        ))}
                    </select>
                </label>

                {mode === 'compare' ? (
                    <label className="sa-field">
                        <span className="sa-label">Bandingkan</span>
                        <select
                            className="sa-select"
                            value={compareMonth}
                            onChange={(e) => setCompareMonth(e.target.value)}
                        >
                            {monthOptions.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </select>
                    </label>
                ) : null}
            </div>

            <div className="sa-legend" aria-label="Legend">
                <div className="sa-legend-item">
                    <span className="sa-legend-swatch is-primary" aria-hidden="true" />
                    <span className="sa-legend-text">{monthMainLabel}</span>
                </div>
                {mode === 'compare' ? (
                    <div className="sa-legend-item">
                        <span className="sa-legend-swatch is-compare" aria-hidden="true" />
                        <span className="sa-legend-text">{monthCompareLabel}</span>
                    </div>
                ) : null}
            </div>

            <div className="sa-body">
                <div className="sa-chart-wrap">
                    <Line data={chartData} options={options} />
                </div>

                <div className="sa-stats" aria-label="Ringkasan statistik">
                    <div className="sa-stat sa-stat--sales">
                        <div className="sa-stat-top">
                            <div className="sa-stat-k">Total penjualan</div>
                            <div className="sa-stat-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M7 7h10v10H7V7Z" stroke="currentColor" strokeWidth="2.25" />
                                    <path d="M4 9V6a2 2 0 0 1 2-2h3" stroke="currentColor" strokeWidth="2.25" />
                                    <path d="M20 15v3a2 2 0 0 1-2 2h-3" stroke="currentColor" strokeWidth="2.25" />
                                    <path d="M10 12h4" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" />
                                </svg>
                            </div>
                        </div>
                        <div className="sa-stat-v">{loading ? 'Memuat…' : formatRupiahFull(totalUtama)}</div>
                        <div className="sa-stat-sub">Uang penjualan di {monthMainLabel}</div>
                    </div>

                    <div className="sa-stat sa-stat--days">
                        <div className="sa-stat-top">
                            <div className="sa-stat-k">Hari ada penjualan</div>
                            <div className="sa-stat-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M7 3v3M17 3v3" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" />
                                    <path d="M4 8h16" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" />
                                    <path d="M6 6h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z" stroke="currentColor" strokeWidth="2.25" />
                                    <path d="M8 12h4" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" />
                                </svg>
                            </div>
                        </div>
                        <div className="sa-stat-v">{loading ? '—' : `${hariAktif.toLocaleString('id-ID')} hari`}</div>
                        <div className="sa-stat-sub">Hari dengan penjualan &gt; 0</div>
                    </div>

                    <div className="sa-stat sa-stat--avg">
                        <div className="sa-stat-top">
                            <div className="sa-stat-k">Rata-rata / hari</div>
                            <div className="sa-stat-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 19V5" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" />
                                    <path d="M4 19h16" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" />
                                    <path d="M7 15l3-4 3 2 4-6" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" strokeLinejoin="round" />
                                </svg>
                            </div>
                        </div>
                        <div className="sa-stat-v">{loading ? '—' : formatRupiahFull(rataRataHariAktif)}</div>
                        <div className="sa-stat-sub">Rata-rata per hari yang ada penjualan</div>
                    </div>

                    {mode === 'compare' ? (
                        <div className={`sa-stat sa-stat--delta is-${deltaTone}`}>
                            <div className="sa-stat-top">
                                <div className="sa-stat-k">Dibandingkan {monthCompareLabel}</div>
                                <div className="sa-stat-ico" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M12 3v18" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" />
                                        <path d="M7 8l5-5 5 5" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" strokeLinejoin="round" />
                                        <path d="M7 16l5 5 5-5" stroke="currentColor" strokeWidth="2.25" strokeLinecap="round" strokeLinejoin="round" opacity="0.7" />
                                    </svg>
                                </div>
                            </div>
                            <div className="sa-stat-v sa-delta">
                                {loading ? '—' : deltaText}
                            </div>
                            <div className="sa-stat-sub">Naik jika plus, turun jika minus</div>
                        </div>
                    ) : null}
                </div>
            </div>

            {error ? (
                <div className="sa-error" role="alert">
                    {error}
                </div>
            ) : null}
        </section>
    );
}