@extends('layouts.admin')

@section('title', 'WhatsApp Alerts Log')
@section('page_title', 'WhatsApp Alerts Log')

@section('content')
<div class="container py-6">

    @if(!$hasSubscription)
        {{-- Not on any plan --}}
        <div class="card card-flush bg-light-primary mb-6">
            <div class="card-body py-6 d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="symbol symbol-50px">
                        <span class="symbol-label bg-white">
                            <i class="ki-duotone ki-dollar fs-2x text-primary">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-gray-900">WhatsApp alerts are a paid feature</div>
                        <div class="text-muted fs-7">Subscribe to a plan that includes WhatsApp alerts to start receiving them.</div>
                    </div>
                </div>
                <a href="{{ $pricingUrl }}" class="btn btn-primary">See Pricing</a>
            </div>
        </div>
    @elseif(!$featureActive)
        {{-- Has a subscription but not WhatsApp --}}
        <div class="card card-flush bg-light-warning mb-6">
            <div class="card-body py-6 d-flex flex-wrap justify-content-between align-items-center gap-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="symbol symbol-50px">
                        <span class="symbol-label bg-white">
                            <i class="ki-duotone ki-information-5 fs-2x text-warning">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                        </span>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-gray-900">Your current plan does not include WhatsApp alerts</div>
                        <div class="text-muted fs-7">Upgrade your plan to receive job alerts on WhatsApp.</div>
                    </div>
                </div>
                <a href="{{ $pricingUrl }}" class="btn btn-warning">Upgrade Plan</a>
            </div>
        </div>
    @endif

    {{-- Counts --}}
    <div class="row g-4 mb-6">
        <div class="col-md-4">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Total Alerts</div>
                    <div class="fs-2 fw-bold text-gray-900" id="countAll">—</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Delivered</div>
                    <div class="fs-2 fw-bold text-success" id="countSent">—</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-flush">
                <div class="card-body py-5">
                    <div class="text-muted fs-7 mb-1">Failed</div>
                    <div class="fs-2 fw-bold text-danger" id="countFailed">—</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card card-flush">
        <div class="card-header align-items-center py-5">
            <h3 class="card-title fw-bold mb-0">Your WhatsApp Alerts</h3>
            <div class="card-toolbar">
                <select id="perPage" class="form-select form-select-sm w-auto">
                    <option value="10">10 / page</option>
                    <option value="25" selected>25 / page</option>
                    <option value="50">50 / page</option>
                </select>
            </div>
        </div>

        <div class="card-body pt-0">

            <div class="row g-3 mb-5">
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select form-select-solid">
                        <option value="">Any Status</option>
                        <option value="sent">Delivered</option>
                        <option value="failed">Failed</option>
                        <option value="queued">Queued</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filterKind" class="form-select form-select-solid">
                        <option value="">Any Kind</option>
                        <option value="jobs">Job Alert</option>
                        <option value="preferences_prompt">Set Preferences Prompt</option>
                        <option value="profile_prompt">Complete Profile Prompt</option>
                        <option value="cv_prompt">Upload CV Prompt</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" id="filterFrom" class="form-control form-control-solid">
                </div>
                <div class="col-md-3">
                    <input type="date" id="filterTo" class="form-control form-control-solid">
                </div>
                <div class="col-12">
                    <button class="btn btn-sm btn-light-primary" id="applyFilters">Apply</button>
                    <button class="btn btn-sm btn-light" id="clearFilters">Clear</button>
                </div>
            </div>

            <div id="tableWrap" class="text-center py-10">
                <div class="spinner-border text-primary"></div>
            </div>

            <div id="paginationWrap" class="d-none d-flex flex-wrap justify-content-between align-items-center gap-3 mt-5">
                <div id="paginationInfo" class="text-muted fs-7"></div>
                <nav><ul class="pagination m-0" id="paginationList"></ul></nav>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const state = { page: 1, perPage: 25, status: '', kind: '', from: '', to: '' };

    document.getElementById('applyFilters')?.addEventListener('click', () => {
        state.status = document.getElementById('filterStatus').value;
        state.kind   = document.getElementById('filterKind').value;
        state.from   = document.getElementById('filterFrom').value;
        state.to     = document.getElementById('filterTo').value;
        state.page   = 1;
        load();
    });

    document.getElementById('clearFilters')?.addEventListener('click', () => {
        ['filterStatus','filterKind','filterFrom','filterTo']
            .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
        Object.assign(state, { status:'', kind:'', from:'', to:'', page:1 });
        load();
    });

    document.getElementById('perPage')?.addEventListener('change', function () {
        state.perPage = parseInt(this.value) || 25;
        state.page = 1;
        load();
    });

    async function load() {
        const wrap = document.getElementById('tableWrap');
        const pager = document.getElementById('paginationWrap');
        pager.classList.add('d-none');
        wrap.innerHTML = '<div class="text-center py-10"><div class="spinner-border text-primary"></div></div>';

        const params = new URLSearchParams({ page: state.page, per_page: state.perPage });
        ['status','kind','from','to'].forEach(k => { if (state[k]) params.set(k, state[k]); });

        try {
            const res = await fetch(`{{ route("alert.log.data") }}?${params}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (data.counts) {
                document.getElementById('countAll').textContent    = data.counts.all ?? 0;
                document.getElementById('countSent').textContent   = data.counts.sent ?? 0;
                document.getElementById('countFailed').textContent = data.counts.failed ?? 0;
            }

            if (!data.success || !data.data?.data?.length) {
                wrap.innerHTML = `
                    <div class="text-center py-15">
                        <i class="ki-duotone ki-information-5 fs-5tx text-muted mb-4 d-block">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <h4 class="fw-bold text-gray-800 mb-2">No WhatsApp alerts yet</h4>
                        <p class="text-muted">Your delivered alerts will appear here.</p>
                    </div>`;
                return;
            }

            wrap.innerHTML = `
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-row-gray-300 fs-7 gy-3">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="text-start min-w-180px">Kind</th>
                                <th class="text-start min-w-90px">Jobs</th>
                                <th class="text-start min-w-120px">Status</th>
                                <th class="text-start min-w-180px">Delivered</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.data.data.map(row).join('')}
                        </tbody>
                    </table>
                </div>`;

            renderPagination(data.data);
            pager.classList.remove('d-none');
        } catch (e) {
            wrap.innerHTML = '<div class="alert alert-danger">Failed to load alerts.</div>';
        }
    }

    function row(d) {
        const sColor = d.status === 'sent' ? 'success' : (d.status === 'failed' ? 'danger' : 'info');
        const when = d.sent_at
            ? new Date(d.sent_at).toLocaleString()
            : (d.created_at ? new Date(d.created_at).toLocaleString() + ' (queued)' : '—');

        return `
            <tr>
                <td class="text-start fw-semibold">${esc(d.kind_label)}</td>
                <td class="text-start">
                    <span class="badge badge-light-secondary">${d.jobs_sent ?? 0}</span>
                </td>
                <td class="text-start">
                    <span class="badge badge-light-${sColor}">${esc(d.status)}</span>
                    ${d.error ? `<div class="text-danger fs-8 mt-1">${esc(d.error.substring(0, 60))}</div>` : ''}
                </td>
                <td class="text-start text-muted fs-8">${when}</td>
            </tr>`;
    }

    function renderPagination(meta) {
        const el = document.getElementById('paginationList');
        const info = document.getElementById('paginationInfo');
        el.innerHTML = '';

        info.textContent = meta.total
            ? `Showing ${meta.from || 0} to ${meta.to || 0} of ${meta.total}`
            : '0 records';

        const add = (page, text, active = false, disabled = false) => {
            const li = document.createElement('li');
            li.className = `page-item ${active ? 'active' : ''} ${disabled ? 'disabled' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = text;
            if (!disabled && !active) {
                a.addEventListener('click', (e) => { e.preventDefault(); state.page = page; load(); });
            }
            li.appendChild(a);
            el.appendChild(li);
        };

        if (meta.current_page > 1) add(meta.current_page - 1, 'Prev');
        const start = Math.max(1, meta.current_page - 2);
        const end   = Math.min(meta.last_page, meta.current_page + 2);
        if (start > 1) add(1, '1');
        if (start > 2) el.insertAdjacentHTML('beforeend', '<li class="page-item disabled"><span class="page-link">…</span></li>');
        for (let i = start; i <= end; i++) add(i, i, i === meta.current_page);
        if (end < meta.last_page - 1) el.insertAdjacentHTML('beforeend', '<li class="page-item disabled"><span class="page-link">…</span></li>');
        if (end < meta.last_page) add(meta.last_page, meta.last_page);
        if (meta.current_page < meta.last_page) add(meta.current_page + 1, 'Next');
    }

    function esc(t) {
        if (t === null || t === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(t);
        return div.innerHTML;
    }

    load();
})();
</script>
@endpush