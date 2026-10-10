@extends('layouts.admin')

@section('title', 'Shortlist')
@section('page_title', 'Shortlist')

@section('content')
<div class="container py-6">

    <div class="card card-flush mb-5">
        <div class="card-header py-5">
            <div>
                <h3 class="fw-bold mb-1" id="runTitle">Loading...</h3>
                <div class="text-muted fs-7" id="runMeta"></div>
            </div>
            <div class="card-toolbar gap-2">
                <a href="{{ route('employer.hiring.export', $uuid) }}" class="btn btn-sm btn-light">
                    <i class="ki-duotone ki-file-down fs-3 me-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    Export CSV
                </a>
                <a href="{{ route('employer.hiring.index') }}" class="btn btn-sm btn-light">All Runs</a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="row g-3 mb-5">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control form-control-solid"
                           placeholder="Search name, email, title...">
                </div>
                <div class="col-md-3">
                    <select id="recFilter" class="form-select form-select-solid">
                        <option value="">Any Recommendation</option>
                        <option value="strong_yes">Strong Yes</option>
                        <option value="maybe">Maybe</option>
                        <option value="no">No</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="scoreFilter" class="form-select form-select-solid">
                        <option value="">Any Score</option>
                        <option value="80">80+</option>
                        <option value="60">60+</option>
                        <option value="40">40+</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-light w-100" id="reloadBtn">Reload</button>
                </div>
            </div>

            <div id="loadingSpinner" class="text-center py-10 d-none">
                <div class="spinner-border text-primary"></div>
            </div>

            <div id="resultsTable" class="d-none">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase">
                                <th class="w-50px">#</th>
                                <th class="min-w-200px">Candidate</th>
                                <th class="min-w-150px">Title</th>
                                <th class="min-w-80px">Exp</th>
                                <th class="min-w-120px">Score</th>
                                <th class="min-w-130px">Recommendation</th>
                                <th class="text-end min-w-120px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody"></tbody>
                    </table>
                </div>
                <div id="paginationContainer" class="d-flex justify-content-end mt-4">
                    <ul class="pagination m-0" id="pagination"></ul>
                </div>
            </div>

            <div id="noData" class="text-center py-15 d-none">
                <p class="text-muted">No candidates match your filters.</p>
            </div>
        </div>
    </div>
</div>

{{-- Detail modal --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold mb-0" id="detailName">Candidate</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const UUID = '{{ $uuid }}';
    let page = 1;
    let candidates = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadResults();
        document.getElementById('searchInput')?.addEventListener('input', debounce(() => { page = 1; loadResults(); }, 400));
        document.getElementById('recFilter')?.addEventListener('change', () => { page = 1; loadResults(); });
        document.getElementById('scoreFilter')?.addEventListener('change', () => { page = 1; loadResults(); });
        document.getElementById('reloadBtn')?.addEventListener('click', () => loadResults());
    });

    function loadResults() {
        document.getElementById('loadingSpinner').classList.remove('d-none');
        document.getElementById('resultsTable').classList.add('d-none');
        document.getElementById('noData').classList.add('d-none');

        const params = new URLSearchParams({ page });
        const s = document.getElementById('searchInput').value.trim();
        const r = document.getElementById('recFilter').value;
        const sc = document.getElementById('scoreFilter').value;
        if (s) params.set('search', s);
        if (r) params.set('recommendation', r);
        if (sc) params.set('min_score', sc);

        fetch(`{{ url('employer/hiring') }}/${UUID}/results?${params}`, {
            headers: { 'Accept': 'application/json' },
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('loadingSpinner').classList.add('d-none');

            if (!data.success || !data.data?.data?.length) {
                document.getElementById('noData').classList.remove('d-none');
                return;
            }

            document.getElementById('runTitle').textContent = data.run.title;
            document.getElementById('runMeta').textContent =
                `${data.run.total} CVs · ${data.run.processed} screened · ${data.run.failed} failed`;

            candidates = data.data.data;
            renderTable();
            renderPagination(data.data);
            document.getElementById('resultsTable').classList.remove('d-none');
        })
        .catch(() => {
            document.getElementById('loadingSpinner').classList.add('d-none');
            document.getElementById('noData').classList.remove('d-none');
        });
    }

    function renderTable() {
        const tbody = document.getElementById('resultsBody');
        tbody.innerHTML = candidates.map((c, i) => `
            <tr>
                <td class="text-muted">${(page - 1) * 50 + i + 1}</td>
                <td>
                    <div class="fw-bold">${escapeHtml(c.name)}</div>
                    <div class="text-muted fs-7">${escapeHtml(c.email || '')}</div>
                </td>
                <td>${escapeHtml(c.current_title || '-')}</td>
                <td>${c.years_of_experience ?? '-'}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress h-6px flex-grow-1" style="min-width:50px;">
                            <div class="progress-bar bg-${c.score_color}" style="width:${c.score}%"></div>
                        </div>
                        <span class="fw-bold text-${c.score_color}">${c.score}</span>
                    </div>
                </td>
                <td>${c.recommendation_badge}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-icon btn-light" onclick="openDetail(${c.id})">
                        <i class="ki-duotone ki-eye fs-3">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    window.openDetail = function (id) {
        const c = candidates.find(x => x.id === id);
        if (!c) return;

        document.getElementById('detailName').textContent = c.name;

        const list = (arr) => arr && arr.length
            ? `<ul class="ps-4 mb-0">${arr.map(x => `<li>${escapeHtml(x)}</li>`).join('')}</ul>`
            : '<span class="text-muted">None</span>';

        document.getElementById('detailBody').innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <div class="fs-1 fw-bold text-${c.score_color}">
                        ${c.score}<span class="fs-5">/100</span>
                    </div>
                    <div>${c.recommendation_badge}</div>
                </div>
                ${c.file_url ? `<a href="${c.file_url}" target="_blank" class="btn btn-sm btn-light">Open CV</a>` : ''}
            </div>

            ${c.summary ? `<div class="alert alert-light-${c.score_color} mb-4">${escapeHtml(c.summary)}</div>` : ''}

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="fw-bold text-success mb-2">Strengths</div>
                    ${list(c.strengths)}
                </div>
                <div class="col-md-6">
                    <div class="fw-bold text-warning mb-2">Gaps</div>
                    ${list(c.gaps)}
                </div>
                <div class="col-md-6">
                    <div class="fw-bold text-primary mb-2">Matched Skills</div>
                    ${list(c.matched_skills)}
                </div>
                <div class="col-md-6">
                    <div class="fw-bold text-muted mb-2">Missing Skills</div>
                    ${list(c.missing_skills)}
                </div>
                ${c.red_flags && c.red_flags.length ? `
                    <div class="col-12">
                        <div class="fw-bold text-danger mb-2">Red Flags</div>
                        ${list(c.red_flags)}
                    </div>
                ` : ''}
            </div>
        `;

        bootstrap.Modal.getOrCreateInstance(document.getElementById('detailModal')).show();
    };

    function renderPagination(data) {
        const el = document.getElementById('pagination');
        el.innerHTML = '';
        for (let i = 1; i <= data.last_page; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === data.current_page ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.querySelector('a').addEventListener('click', e => { e.preventDefault(); page = i; loadResults(); });
            el.appendChild(li);
        }
    }

    function debounce(fn, wait) {
        let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), wait); };
    }
    function escapeHtml(t) {
        if (t === null || t === undefined) return '';
        const d = document.createElement('div'); d.textContent = String(t); return d.innerHTML;
    }
})();
</script>
@endpush