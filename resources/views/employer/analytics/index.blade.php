@extends('layouts.admin')

@section('title', 'Job Analytics')
@section('page_title', 'Job Analytics')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Employer</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Job Analytics</li>
@endsection

@section('content')
<div class="container py-6">

    {{-- Filter bar --}}
    <div class="card card-flush mb-6">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 align-items-center">

                <div>
                    <label class="fw-semibold fs-7 mb-1 d-block">Time Range</label>
                    <select id="rangeFilter" class="form-select form-select-solid w-175px">
                        <option value="7d">Last 7 days</option>
                        <option value="30d" selected>Last 30 days</option>
                        <option value="90d">Last 90 days</option>
                        <option value="12m">Last 12 months</option>
                        <option value="all">All time</option>
                    </select>
                </div>

                <div>
                    <label class="fw-semibold fs-7 mb-1 d-block">Job</label>
                    <select id="jobFilter" class="form-select form-select-solid w-250px">
                        <option value="">All Jobs</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job['id'] }}">{{ $job['title'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ms-auto align-self-end">
                    <button class="btn btn-light-primary" id="exportBtn">
                        <i class="ki-duotone ki-file-down fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Totals strip --}}
    <div class="row g-5 g-xl-8 mb-6" id="totalsStrip">
        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-duotone ki-briefcase fs-2x text-primary">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-gray-600 fs-7">Jobs Posted</span>
                        <span class="fw-bold fs-2x text-gray-900" id="statJobsPosted">—</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-info">
                            <i class="ki-duotone ki-eye fs-2x text-info">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-gray-600 fs-7">Total Views</span>
                        <span class="fw-bold fs-2x text-gray-900" id="statViews">—</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-success">
                            <i class="ki-duotone ki-user-tick fs-2x text-success">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-gray-600 fs-7">Applicants (range)</span>
                        <span class="fw-bold fs-2x text-gray-900" id="statApplicants">—</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-6">
                    <div class="symbol symbol-50px me-5">
                        <span class="symbol-label bg-light-warning">
                            <i class="ki-duotone ki-chart-line-up fs-2x text-warning">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold text-gray-600 fs-7">Conversion</span>
                        <span class="fw-bold fs-2x text-gray-900" id="statConversion">—</span>
                        <span class="text-muted fs-8">views → applicants</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="row g-6 mb-6">
        {{-- Applications over time --}}
        <div class="col-xl-8">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h3 class="card-title">Applications Over Time</h3>
                </div>
                <div class="card-body pt-0">
                    <div id="applicationsChartWrap" style="height:320px;position:relative;">
                        <canvas id="applicationsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status breakdown --}}
        <div class="col-xl-4">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h3 class="card-title">By Status</h3>
                </div>
                <div class="card-body pt-0">
                    <div id="statusChartWrap" style="height:320px;position:relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tables row --}}
    <div class="row g-6">
        {{-- Top jobs --}}
        <div class="col-xl-7">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h3 class="card-title">Top Jobs</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-7">
                            <thead>
                                <tr class="text-start text-gray-500 fw-bold fs-8 text-uppercase">
                                    <th>Job</th>
                                    <th class="text-end">Views</th>
                                    <th class="text-end">Applicants</th>
                                    <th class="text-end">Conv.</th>
                                </tr>
                            </thead>
                            <tbody id="topJobsBody">
                                <tr><td colspan="4" class="text-center text-muted py-6">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top countries --}}
        <div class="col-xl-5">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h3 class="card-title">Applicants by Country</h3>
                </div>
                <div class="card-body pt-0">
                    <div id="topCountriesList">
                        <p class="text-muted text-center py-6">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const ANALYTICS_URL = '{{ route('employer.analytics.data') }}';

let applicationsChart = null;
let statusChart = null;

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('rangeFilter')?.addEventListener('change', loadAnalytics);
    document.getElementById('jobFilter')?.addEventListener('change', loadAnalytics);
    document.getElementById('exportBtn')?.addEventListener('click', exportCsv);

    loadAnalytics();
});

function loadAnalytics() {
    const range  = document.getElementById('rangeFilter').value;
    const jobId  = document.getElementById('jobFilter').value;

    const params = new URLSearchParams({ range });
    if (jobId) params.set('job_id', jobId);

    fetch(`${ANALYTICS_URL}?${params}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.message || 'Failed to load');
            renderAll(res.data);
        })
        .catch(err => {
            console.error(err);
            if (typeof window.showToast === 'function') {
                window.showToast('error', 'Failed to load analytics.');
            }
        });
}

function renderAll(data) {
    // ── Totals
    document.getElementById('statJobsPosted').textContent = formatNumber(data.totals.jobs_posted);
    document.getElementById('statViews').textContent      = formatNumber(data.totals.total_views);
    document.getElementById('statApplicants').textContent = formatNumber(data.totals.applicants_range);
    document.getElementById('statConversion').textContent = data.totals.conversion_rate + '%';

    // ── Applications chart
    renderApplicationsChart(data.series);

    // ── Status chart
    renderStatusChart(data.by_status);

    // ── Top jobs
    renderTopJobs(data.top_jobs);

    // ── Top countries
    renderTopCountries(data.top_countries);
}

function renderApplicationsChart(series) {
    const ctx = document.getElementById('applicationsChart').getContext('2d');

    const labels = series.map(p => p.period);
    const values = series.map(p => p.count);

    if (applicationsChart) applicationsChart.destroy();

    applicationsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Applications',
                data: values,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
            },
        },
    });
}

function renderStatusChart(byStatus) {
    const ctx = document.getElementById('statusChart').getContext('2d');

    const labels = Object.keys(byStatus).map(k => ucfirst(k.replace('_', ' ')));
    const values = Object.values(byStatus);
    const colors = {
        new:         '#0d6efd',
        screening:   '#0dcaf0',
        shortlisted: '#198754',
        interview:   '#ffc107',
        offer:       '#fd7e14',
        hired:       '#20c997',
        rejected:    '#dc3545',
        withdrawn:   '#6c757d',
    };
    const background = Object.keys(byStatus).map(k => colors[k] || '#adb5bd');

    if (statusChart) statusChart.destroy();

    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: background,
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
            },
        },
    });
}

function renderTopJobs(jobs) {
    const tbody = document.getElementById('topJobsBody');

    if (!jobs.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-6">No data for this range.</td></tr>';
        return;
    }

    tbody.innerHTML = jobs.map(j => `
        <tr>
            <td>
                <div class="fw-semibold text-truncate" style="max-width:220px;">${escapeHtml(j.title)}</div>
            </td>
            <td class="text-end">${formatNumber(j.views)}</td>
            <td class="text-end fw-bold">${formatNumber(j.applicants)}</td>
            <td class="text-end"><span class="badge badge-light-primary">${j.conversion}%</span></td>
        </tr>
    `).join('');
}

function renderTopCountries(countries) {
    const wrap = document.getElementById('topCountriesList');
    const entries = Object.entries(countries);

    if (!entries.length) {
        wrap.innerHTML = '<p class="text-muted text-center py-6">No data for this range.</p>';
        return;
    }

    const max = Math.max(...entries.map(([, c]) => c));

    wrap.innerHTML = entries.map(([country, count]) => {
        const pct = max > 0 ? (count / max) * 100 : 0;
        return `
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="fw-semibold fs-7">${escapeHtml(country)}</span>
                    <span class="text-muted fs-7">${formatNumber(count)}</span>
                </div>
                <div class="progress h-6px">
                    <div class="progress-bar bg-primary" style="width: ${pct}%"></div>
                </div>
            </div>
        `;
    }).join('');
}

function exportCsv() {
    const range = document.getElementById('rangeFilter').value;
    const jobId = document.getElementById('jobFilter').value;

    const params = new URLSearchParams({ range, export: '1' });
    if (jobId) params.set('job_id', jobId);

    // Reuse the same data endpoint with export flag (or add a dedicated route)
    window.open(`${ANALYTICS_URL}?${params}`, '_blank');
}

// ── Utils
function formatNumber(n) {
    return new Intl.NumberFormat('en-US').format(n || 0);
}

function ucfirst(s) {
    return s.charAt(0).toUpperCase() + s.slice(1);
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush