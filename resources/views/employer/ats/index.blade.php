@extends('layouts.admin')

@section('title', 'Applicants — ' . $job['job_title'])
@section('page_title', 'Applicants')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Employer</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item">
        <a href="{{ route('employer.jobs.index') }}" class="text-muted text-hover-primary">My Jobs</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Applicants</li>
@endsection

@section('content')
<div class="container py-6">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- JOB HEADER                                              --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="card card-flush mb-6 bg-light-primary">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="text-muted fs-7">Applicants for</div>
                <h2 class="fw-bold mb-1">{{ $job['job_title'] }}</h2>
                <div class="text-muted fs-7">{{ $job['company'] }}</div>
            </div>

            <div class="card-toolbar gap-2">
                <button type="button" class="btn btn-sm btn-light-warning" id="screenAllBtn">
                    <i class="ki-duotone ki-robot fs-4 me-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    Screen All with AI
                </button>
                <a href="{{ route('employer.ats.export', $job['slug']) }}" class="btn btn-sm btn-light">
                    <i class="ki-duotone ki-file-down fs-4 me-1">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    Export CSV
                </a>
                <a href="{{ route('employer.jobs.index') }}" class="btn btn-light-primary">Back</a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- TABLE                                                   --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="card card-flush">
        <div class="card-header mt-6">
            <div class="card-title flex-wrap gap-2">
                <input type="text" id="searchInput" class="form-control form-control-solid w-250px" placeholder="Search name, email, skills..." />

                <select id="ratingFilter" class="form-select form-select-solid w-150px">
                    <option value="">Any Rating</option>
                    @foreach([5,4,3,2,1] as $r)
                        <option value="{{ $r }}">{{ str_repeat('★', $r) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-toolbar">
                <div id="bulkActionsBar" class="d-none">
                    <span class="text-muted fs-7 me-3"><span id="selectedCount">0</span> selected</span>
                    <button class="btn btn-sm btn-success" onclick="bulkUpdate('shortlisted')">Shortlist</button>
                    <button class="btn btn-sm btn-warning" onclick="bulkUpdate('interview')">Interview</button>
                    <button class="btn btn-sm btn-danger" onclick="bulkUpdate('rejected')">Reject</button>
                    <button class="btn btn-sm btn-light" onclick="clearSelection()">Clear</button>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            {{-- Status tabs --}}
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-7">
                <li class="nav-item">
                    <a class="nav-link active" data-status="" href="#" onclick="switchStatus(event, '')">
                        All <span class="badge badge-light-primary ms-1" data-count="all">{{ $counts['all'] ?? 0 }}</span>
                    </a>
                </li>
                @foreach(['new' => 'New', 'screening' => 'Screening', 'shortlisted' => 'Shortlisted', 'interview' => 'Interview', 'offer' => 'Offer', 'hired' => 'Hired', 'rejected' => 'Rejected'] as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link" data-status="{{ $key }}" href="#" onclick="switchStatus(event, '{{ $key }}')">
                            {{ $label }}
                            <span class="badge badge-light-secondary ms-1" data-count="{{ $key }}">{{ $counts[$key] ?? 0 }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div id="loadingSpinner" class="text-center py-10 d-none">
                <div class="spinner-border text-primary"></div>
            </div>

            <div id="tableContainer" class="d-none">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase">
                                <th class="w-30px">
                                    <input type="checkbox" id="selectAll" class="form-check-input" />
                                </th>
                                <th class="min-w-200px">Applicant</th>
                                <th class="min-w-180px">Title / Skills</th>
                                <th class="min-w-100px">Rating</th>
                                <th class="min-w-130px">Status</th>
                                <th class="min-w-120px">AI Score</th>
                                <th class="min-w-110px">Applied</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="applicantsTableBody"></tbody>
                    </table>
                </div>
                <div id="paginationContainer" class="d-flex justify-content-between align-items-center mt-5 d-none">
                    <div id="paginationInfo" class="text-muted"></div>
                    <nav><ul class="pagination m-0" id="pagination"></ul></nav>
                </div>
            </div>

            <div id="noDataMessage" class="text-center py-10 d-none">
                <p class="text-muted">No applicants for this filter.</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- APPLICANT DETAIL DRAWER                                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="kt_ats_drawer" style="width:720px;">
    <div class="offcanvas-header">
        <h3 class="offcanvas-title">Applicant</h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="atsDrawerBody">
        <div class="text-center py-10"><div class="spinner-border text-primary"></div></div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- SCREENING PROGRESS MODAL                                    --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="kt_screening_modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold mb-0">AI Screening</h3>
            </div>
            <div class="modal-body text-center py-8">

                <div class="mb-5">
                    <i class="ki-duotone ki-robot fs-3x text-primary mb-3 d-block">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <div class="fw-bold fs-4 mb-2" id="screening_status_text">Preparing...</div>
                    <div class="text-muted fs-7" id="screening_sub_text">Please keep this window open</div>
                </div>

                <div class="progress h-10px mb-3">
                    <div class="progress-bar bg-primary"
                         id="screening_progress_bar"
                         style="width: 0%"
                         role="progressbar"></div>
                </div>

                <div class="d-flex justify-content-between text-muted fs-7">
                    <span id="screening_count">0 / 0</span>
                    <span id="screening_percent">0%</span>
                </div>

                <div id="screening_error" class="alert alert-light-warning mt-5 d-none text-start fs-7"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="screening_close_btn" disabled>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const SLUG = '{{ $job["slug"] }}';

let currentPage = 1;
let currentSearch = '';
let currentStatus = '';
let currentRating = '';
let selectedIds = new Set();

// Track the current screening batch
let activeBatchUuid = null;
let activePollTimer = null;

// ================================================================
// BOOT
// ================================================================
document.addEventListener('DOMContentLoaded', function () {
    loadApplicants();

    let timeout;
    document.getElementById('searchInput')?.addEventListener('keyup', function () {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            currentSearch = this.value;
            currentPage = 1;
            loadApplicants();
        }, 500);
    });

    document.getElementById('ratingFilter')?.addEventListener('change', function () {
        currentRating = this.value;
        currentPage = 1;
        loadApplicants();
    });

    document.getElementById('selectAll')?.addEventListener('change', function () {
        document.querySelectorAll('.applicant-checkbox').forEach(cb => {
            cb.checked = this.checked;
            if (this.checked) selectedIds.add(parseInt(cb.value));
            else selectedIds.delete(parseInt(cb.value));
        });
        updateBulkBar();
    });

    document.getElementById('screenAllBtn')?.addEventListener('click', function () {
        screenApplicants({ force: false });
    });
});

// ================================================================
// TABLE LOADING
// ================================================================
window.switchStatus = function (e, status) {
    e.preventDefault();
    currentStatus = status;
    currentPage = 1;

    document.querySelectorAll('.nav-line-tabs .nav-link').forEach(a => a.classList.remove('active'));
    e.currentTarget.classList.add('active');

    loadApplicants();
};

function loadApplicants() {
    const spinner    = document.getElementById('loadingSpinner');
    const table      = document.getElementById('tableContainer');
    const noData     = document.getElementById('noDataMessage');
    const pagination = document.getElementById('paginationContainer');

    spinner.classList.remove('d-none');
    table.classList.add('d-none');
    noData.classList.add('d-none');
    pagination.classList.add('d-none');

    const params = new URLSearchParams({ page: currentPage });
    if (currentStatus) params.set('status', currentStatus);
    if (currentSearch) params.set('search', currentSearch);
    if (currentRating) params.set('rating', currentRating);

    fetch(`/employer/jobs/${SLUG}/applicants/data?${params}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        spinner.classList.add('d-none');

        if (data.counts) {
            Object.keys(data.counts).forEach(k => {
                const el = document.querySelector(`[data-count="${k}"]`);
                if (el) el.textContent = data.counts[k];
            });
        }

        if (!data.data || data.data.length === 0) {
            noData.classList.remove('d-none');
        } else {
            table.classList.remove('d-none');
            renderTable(data.data);
            renderPagination(data);
            pagination.classList.remove('d-none');
        }
    })
    .catch(() => {
        spinner.classList.add('d-none');
        window.showToast('error', 'Failed to load applicants.');
    });
}

function renderTable(items) {
    const tbody = document.getElementById('applicantsTableBody');
    tbody.innerHTML = '';
    selectedIds.clear();
    updateBulkBar();

    items.forEach(a => {
        const row = tbody.insertRow();

        // Checkbox
        row.insertCell(0).innerHTML = `
            <input type="checkbox" class="form-check-input applicant-checkbox" value="${a.id}" />`;

        // Applicant
        row.insertCell(1).innerHTML = `
            <div class="d-flex flex-column">
                <span class="fw-bold">${escapeHtml(a.name)}</span>
                <span class="text-muted fs-8">${escapeHtml(a.email)}</span>
                ${a.phone ? `<span class="text-muted fs-8">${escapeHtml(a.phone)}</span>` : ''}
            </div>`;

        // Title / Skills
        const skills = Array.isArray(a.skills)
            ? a.skills.slice(0, 3).join(', ')
            : (typeof a.skills === 'string' ? a.skills.split(',').slice(0, 3).join(', ') : '');

        row.insertCell(2).innerHTML = `
            <div class="fw-semibold fs-7">${escapeHtml(a.professional_title || '—')}</div>
            ${skills ? `<div class="text-muted fs-8">${escapeHtml(skills)}</div>` : ''}`;

        // Rating
        row.insertCell(3).innerHTML = a.employer_rating
            ? `<span class="text-warning">${'★'.repeat(a.employer_rating)}</span>`
            : '<span class="text-muted">—</span>';

        // Status
        row.insertCell(4).innerHTML = a.status_badge;

        // AI Score
        if (a.ai_score !== null && a.ai_score !== undefined) {
            row.insertCell(5).innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <div class="progress h-6px flex-grow-1" style="min-width:50px;">
                        <div class="progress-bar bg-${a.ai_score_color}" style="width: ${a.ai_score}%"></div>
                    </div>
                    <span class="fw-bold fs-7 text-${a.ai_score_color}">${a.ai_score}</span>
                </div>
                <div class="mt-1">${a.ai_recommendation_badge}</div>
            `;
        } else {
            row.insertCell(5).innerHTML = '<span class="text-muted fs-7">Not screened</span>';
        }

        // Applied date
        row.insertCell(6).innerHTML = a.applied_at
            ? `<span class="text-muted fs-7">${new Date(a.applied_at).toLocaleDateString()}</span>`
            : '<span class="text-muted fs-7">—</span>';

        // Actions
        row.insertCell(7).innerHTML = `
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-sm btn-icon btn-light" onclick="openApplicant(${a.id})" title="View">
                    <i class="ki-duotone ki-eye fs-3">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                </button>
            </div>`;

        // Wire checkbox
        const cb = row.querySelector('.applicant-checkbox');
        cb.addEventListener('change', function () {
            if (this.checked) selectedIds.add(parseInt(this.value));
            else selectedIds.delete(parseInt(this.value));
            updateBulkBar();
        });
    });
}

function updateBulkBar() {
    const bar = document.getElementById('bulkActionsBar');
    document.getElementById('selectedCount').textContent = selectedIds.size;
    bar.classList.toggle('d-none', selectedIds.size === 0);
}

window.clearSelection = function () {
    selectedIds.clear();
    document.querySelectorAll('.applicant-checkbox').forEach(cb => cb.checked = false);
    const all = document.getElementById('selectAll');
    if (all) all.checked = false;
    updateBulkBar();
};

window.bulkUpdate = function (status) {
    if (selectedIds.size === 0) return;
    if (!confirm(`Mark ${selectedIds.size} applicant(s) as "${status}"?`)) return;

    fetch(`/employer/jobs/${SLUG}/applicants/bulk`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: Array.from(selectedIds), ats_status: status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', data.message);
            loadApplicants();
        } else {
            window.showToast('error', data.message);
        }
    })
    .catch(() => window.showToast('error', 'Bulk update failed.'));
};

// ================================================================
// APPLICANT DRAWER
// ================================================================
window.openApplicant = function (id) {
    const body = document.getElementById('atsDrawerBody');
    body.innerHTML = '<div class="text-center py-10"><div class="spinner-border text-primary"></div></div>';
    new bootstrap.Offcanvas(document.getElementById('kt_ats_drawer')).show();

    fetch(`/employer/jobs/${SLUG}/applicants/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.message);
            body.innerHTML = renderApplicant(res.applicant);
        })
        .catch(err => {
            body.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
        });
};

function renderApplicant(a) {
    const cvFiles = Array.isArray(a.cv_files) ? a.cv_files : [];

    const cvSection = cvFiles.length
        ? cvFiles.map(cv => `
            <div class="d-flex align-items-center gap-3 p-2 bg-light rounded border border-gray-200 mb-2">
                <i class="bi bi-file-pdf text-danger fs-4"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold">${escapeHtml(cv.original_name || 'CV File')}</div>
                    <div class="text-muted fs-7">
                        ${cv.size ? Math.round(cv.size / 1024) + ' KB' : ''}
                        ${cv.uploaded_at ? ' • ' + new Date(cv.uploaded_at).toLocaleDateString() : ''}
                    </div>
                </div>
                <a href="${cv.url || '#'}" target="_blank" class="btn btn-sm btn-primary">
                    <i class="bi bi-eye me-1"></i>View
                </a>
            </div>
        `).join('')
        : '<span class="text-muted">No CV files.</span>';

    const skills = Array.isArray(a.skills)
        ? a.skills
        : (typeof a.skills === 'string' ? a.skills.split(',').filter(Boolean) : []);

    const languages = Array.isArray(a.languages)
        ? a.languages
        : (typeof a.languages === 'string' ? (() => { try { return JSON.parse(a.languages); } catch { return []; } })() : []);

    return `
        <div class="d-flex flex-column gap-6">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="fs-4 fw-bold">${escapeHtml(a.name)}</div>
                    <div class="text-muted fs-7">${escapeHtml(a.email)}</div>
                    ${a.phone ? `<div class="text-muted fs-7"><i class="bi bi-phone me-1"></i>${escapeHtml(a.phone)}</div>` : ''}
                    ${a.city || a.country ? `<div class="text-muted fs-7">${escapeHtml([a.city, a.country].filter(Boolean).join(', '))}</div>` : ''}
                </div>
                <div class="text-end">${a.status_badge}</div>
            </div>

            {{-- Quick info --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="fw-bold text-muted fs-7">Title</div>
                    <div class="fw-semibold">${escapeHtml(a.professional_title || '—')}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold text-muted fs-7">Experience</div>
                    <div class="fw-semibold">${a.years_of_experience || 0} years</div>
                </div>
            </div>

            {{-- AI Screening block --}}
            ${a.ai_score !== null && a.ai_score !== undefined ? `
                <div class="alert alert-light-${a.ai_score_color}">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="fw-bold fs-4">AI Match Score</div>
                            <div class="fs-1 fw-bold text-${a.ai_score_color}">
                                ${a.ai_score}<span class="fs-5">/100</span>
                            </div>
                        </div>
                        <div class="text-end">
                            ${a.ai_recommendation_badge}
                            <div class="text-muted fs-8 mt-2">
                                Screened ${new Date(a.ai_screened_at).toLocaleDateString()}
                            </div>
                            <button class="btn btn-sm btn-light mt-2" onclick="screenOne(${a.id})">
                                <i class="ki-duotone ki-arrows-circle fs-4 me-1">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                Re-screen
                            </button>
                        </div>
                    </div>

                    ${a.ai_summary ? `<p class="text-muted mb-3">${escapeHtml(a.ai_summary)}</p>` : ''}

                    ${a.ai_strengths && a.ai_strengths.length ? `
                        <div class="mb-3">
                            <div class="fw-bold text-success mb-1">✅ Strengths</div>
                            <ul class="ps-4 mb-0">${a.ai_strengths.map(s => `<li>${escapeHtml(s)}</li>`).join('')}</ul>
                        </div>
                    ` : ''}

                    ${a.ai_gaps && a.ai_gaps.length ? `
                        <div class="mb-3">
                            <div class="fw-bold text-warning mb-1">⚠️ Gaps</div>
                            <ul class="ps-4 mb-0">${a.ai_gaps.map(s => `<li>${escapeHtml(s)}</li>`).join('')}</ul>
                        </div>
                    ` : ''}

                    ${a.ai_red_flags && a.ai_red_flags.length ? `
                        <div class="mb-0">
                            <div class="fw-bold text-danger mb-1">🚩 Red Flags</div>
                            <ul class="ps-4 mb-0">${a.ai_red_flags.map(s => `<li>${escapeHtml(s)}</li>`).join('')}</ul>
                        </div>
                    ` : ''}
                </div>
            ` : `
                <div class="alert alert-light-secondary d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Not yet screened</div>
                        <div class="text-muted fs-8">Run AI screening to see match analysis</div>
                    </div>
                    <button class="btn btn-sm btn-primary" onclick="screenOne(${a.id})">
                        <i class="ki-duotone ki-robot fs-4 me-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Screen Now
                    </button>
                </div>
            `}

            ${a.professional_summary ? `
                <div>
                    <div class="fw-bold text-muted fs-7 mb-2">Summary</div>
                    <div>${escapeHtml(a.professional_summary)}</div>
                </div>
            ` : ''}

            ${skills.length ? `
                <div>
                    <div class="fw-bold text-muted fs-7 mb-2">Skills</div>
                    <div class="d-flex flex-wrap gap-2">
                        ${skills.slice(0, 20).map(s => `<span class="badge badge-light-primary">${escapeHtml(s.trim())}</span>`).join('')}
                    </div>
                </div>
            ` : ''}

            ${languages.length ? `
                <div>
                    <div class="fw-bold text-muted fs-7 mb-2">Languages</div>
                    <div class="d-flex flex-wrap gap-2">
                        ${languages.map(l => `<span class="badge badge-light-secondary">${escapeHtml(l)}</span>`).join('')}
                    </div>
                </div>
            ` : ''}

            ${a.linkedin_url || a.github_url || a.portfolio_url ? `
                <div>
                    <div class="fw-bold text-muted fs-7 mb-2">Links</div>
                    <div class="d-flex gap-2 flex-wrap">
                        ${a.linkedin_url ? `<a href="${a.linkedin_url}" target="_blank" class="btn btn-sm btn-outline-primary">LinkedIn</a>` : ''}
                        ${a.github_url ? `<a href="${a.github_url}" target="_blank" class="btn btn-sm btn-outline-dark">GitHub</a>` : ''}
                        ${a.portfolio_url ? `<a href="${a.portfolio_url}" target="_blank" class="btn btn-sm btn-outline-info">Portfolio</a>` : ''}
                    </div>
                </div>
            ` : ''}

            <div>
                <div class="fw-bold text-muted fs-7 mb-2">CV Files</div>
                <div>${cvSection}</div>
            </div>

            ${a.cover_letter ? `
                <div>
                    <div class="fw-bold text-muted fs-7 mb-2">Cover Letter</div>
                    <div class="p-3 bg-light rounded" style="white-space:pre-wrap;">${escapeHtml(a.cover_letter)}</div>
                </div>
            ` : ''}

            <div class="border-top pt-5">
                <h5 class="fw-bold mb-3">Employer Actions</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Status</label>
                        <select class="form-select" id="ats_status" onchange="saveApplicant(${a.id})">
                            ${['new','screening','shortlisted','interview','offer','hired','rejected','withdrawn'].map(s => `
                                <option value="${s}" ${a.ats_status === s ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold fs-7 mb-2">Rating</label>
                        <select class="form-select" id="employer_rating" onchange="saveApplicant(${a.id})">
                            <option value="">No rating</option>
                            ${[1,2,3,4,5].map(r => `<option value="${r}" ${a.employer_rating == r ? 'selected' : ''}>${'★'.repeat(r)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="fw-semibold fs-7 mb-2">Internal Notes</label>
                        <textarea class="form-control" id="employer_notes" rows="3"
                                  onblur="saveApplicant(${a.id})">${escapeHtml(a.employer_notes || '')}</textarea>
                    </div>
                </div>
            </div>

        </div>`;
}

window.saveApplicant = function (id) {
    const payload = {
        ats_status:      document.getElementById('ats_status').value,
        employer_rating: document.getElementById('employer_rating').value || null,
        employer_notes:  document.getElementById('employer_notes').value || null,
    };

    fetch(`/employer/jobs/${SLUG}/applicants/${id}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.showToast('success', 'Saved.');
            loadApplicants();
        } else {
            window.showToast('error', data.message);
        }
    })
    .catch(() => window.showToast('error', 'Save failed.'));
};

// ================================================================
// AI SCREENING — QUEUED WITH PROGRESS
// ================================================================

/**
 * Single entry point for both screen-all and screen-one.
 * Shows the progress modal, kicks off the batch, and polls for updates.
 */
function screenApplicants(payload) {
    // Reset modal state
    const modalEl    = document.getElementById('kt_screening_modal');
    const modal      = bootstrap.Modal.getOrCreateInstance(modalEl);
    const statusText = document.getElementById('screening_status_text');
    const subText    = document.getElementById('screening_sub_text');
    const progress   = document.getElementById('screening_progress_bar');
    const countEl    = document.getElementById('screening_count');
    const percentEl  = document.getElementById('screening_percent');
    const errorEl    = document.getElementById('screening_error');
    const closeBtn   = document.getElementById('screening_close_btn');

    statusText.textContent = 'Preparing...';
    subText.textContent    = 'Please keep this window open';
    progress.style.width   = '0%';
    countEl.textContent    = '0 / 0';
    percentEl.textContent  = '0%';
    errorEl.classList.add('d-none');
    errorEl.textContent    = '';
    closeBtn.disabled      = true;

    modal.show();

    // Kick off the batch
    fetch(`/employer/jobs/${SLUG}/applicants/screen`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            statusText.textContent = 'Failed to start';
            subText.textContent    = data.message || 'Unknown error';
            closeBtn.disabled      = false;
            return;
        }

        if (!data.batch_id) {
            // Nothing to screen
            statusText.textContent = '✅ All applicants up to date';
            subText.textContent    = data.message || 'Nothing to screen.';
            closeBtn.disabled      = false;
            setTimeout(() => {
                modal.hide();
            }, 1200);
            return;
        }

        // Start polling
        activeBatchUuid = data.batch_id;
        pollBatchStatus(data.batch_id);
    })
    .catch(err => {
        console.error(err);
        statusText.textContent = 'Network error';
        subText.textContent    = 'Could not reach the server.';
        closeBtn.disabled      = false;
    });
}

function pollBatchStatus(batchUuid) {
    const statusText = document.getElementById('screening_status_text');
    const subText    = document.getElementById('screening_sub_text');
    const progress   = document.getElementById('screening_progress_bar');
    const countEl    = document.getElementById('screening_count');
    const percentEl  = document.getElementById('screening_percent');
    const errorEl    = document.getElementById('screening_error');
    const closeBtn   = document.getElementById('screening_close_btn');

    // Clear any previous timer
    if (activePollTimer) {
        clearInterval(activePollTimer);
        activePollTimer = null;
    }

    const fetchStatus = () => {
        fetch(`/employer/jobs/ats/batches/${batchUuid}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.batch) return;

            const b = data.batch;

            const finished = b.processed + b.failed;
            const pct = b.total > 0 ? Math.round((finished / b.total) * 100) : 0;

            countEl.textContent = `${finished} / ${b.total}`;
            percentEl.textContent = `${pct}%`;
            progress.style.width = `${pct}%`;

            switch (b.status) {
                case 'queued':
                    statusText.textContent = 'Queued for processing...';
                    subText.textContent    = 'Your jobs are waiting in the queue.';
                    break;
                case 'processing':
                    statusText.textContent = 'Screening applicants...';
                    subText.textContent    = 'The AI is reading CVs and scoring matches.';
                    break;
                case 'completed':
                    statusText.textContent = '✅ Screening complete';
                    subText.textContent    = b.failed > 0
                        ? `${b.processed} screened, ${b.failed} failed.`
                        : `${b.processed} applicant(s) screened successfully.`;

                    if (b.failed > 0) {
                        errorEl.textContent = `${b.failed} applicant(s) couldn't be screened. Check logs or try again.`;
                        errorEl.classList.remove('d-none');
                    }

                    clearInterval(activePollTimer);
                    activePollTimer = null;
                    activeBatchUuid = null;
                    closeBtn.disabled = false;

                    setTimeout(() => {
                        const m = bootstrap.Modal.getInstance(document.getElementById('kt_screening_modal'));
                        if (m) m.hide();
                        loadApplicants();
                    }, 1800);
                    return;

                case 'failed':
                    statusText.textContent = '❌ Screening failed';
                    subText.textContent    = 'Something went wrong. Please try again.';
                    clearInterval(activePollTimer);
                    activePollTimer = null;
                    activeBatchUuid = null;
                    closeBtn.disabled = false;
                    return;
            }
        })
        .catch(err => {
            console.error('Poll error:', err);
        });
    };

    // First check immediately, then poll every 3 seconds
    fetchStatus();
    activePollTimer = setInterval(fetchStatus, 3000);
}

/**
 * Screen a single applicant (from the drawer).
 * Reuses the same modal + polling flow.
 */
window.screenOne = function (id) {
    screenApplicants({ applicant_ids: [id], force: true });
};

// ================================================================
// PAGINATION
// ================================================================
function renderPagination(data) {
    const el = document.getElementById('pagination');
    const info = document.getElementById('paginationInfo');
    el.innerHTML = '';
    info.innerHTML = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total}`;

    const add = (page, text, active = false, disabled = false) => {
        const li = document.createElement('li');
        li.className = `page-item ${active ? 'active' : ''} ${disabled ? 'disabled' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = text;
        if (!disabled) a.onclick = (e) => { e.preventDefault(); currentPage = page; loadApplicants(); };
        li.appendChild(a);
        el.appendChild(li);
    };

    add(data.current_page - 1, 'Prev', false, !data.prev_page_url);
    for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
        add(i, i, i === data.current_page);
    }
    add(data.current_page + 1, 'Next', false, !data.next_page_url);
}

// ================================================================
// HELPERS
// ================================================================
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush