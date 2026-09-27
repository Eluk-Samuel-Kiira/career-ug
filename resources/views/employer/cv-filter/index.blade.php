@extends('layouts.admin')

@section('title', 'Find Candidates')
@section('page_title', 'Find Candidates')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Find Candidates</li>
@endsection

@section('content')
<div class="card card-flush mb-5">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <input type="text" id="searchInput"
                       class="form-control form-control-solid w-250px ps-12"
                       placeholder="Search title, skills, name..." />
            </div>
        </div>
        <div class="card-toolbar flex-row-fluid justify-content-end gap-3">
            <select id="hasCvFilter" class="form-select form-select-solid w-150px">
                <option value="">Any CV Status</option>
                <option value="1">Has CV</option>
            </select>
            <select id="profileCompleteFilter" class="form-select form-select-solid w-175px">
                <option value="">Any Profile Status</option>
                <option value="1">Complete</option>
            </select>
            <button type="button" class="btn btn-light-primary" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                <i class="ki-duotone ki-filter fs-2">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                Advanced
            </button>
        </div>
    </div>

    <div class="collapse show" id="advancedFilters">
        <div class="card-body border-top pt-6">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="fw-semibold fs-7 mb-2">Job Category</label>
                    <select id="categoryFilter" class="form-select form-select-solid pref-filter"
                            data-control="select2" data-placeholder="All Categories">
                        <option value=""></option>
                        @foreach($filters['categories'] ?? [] as $c)
                            <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="fw-semibold fs-7 mb-2">Industry</label>
                    <select id="industryFilter" class="form-select form-select-solid pref-filter"
                            data-control="select2" data-placeholder="All Industries">
                        <option value=""></option>
                        @foreach($filters['industries'] ?? [] as $i)
                            <option value="{{ $i['id'] }}">{{ $i['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="fw-semibold fs-7 mb-2">Job Type</label>
                    <select id="jobTypeFilter" class="form-select form-select-solid pref-filter"
                            data-control="select2" data-placeholder="All Job Types">
                        <option value=""></option>
                        @foreach($filters['job_types'] ?? [] as $t)
                            <option value="{{ $t['id'] }}">{{ $t['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="fw-semibold fs-7 mb-2">Location</label>
                    <select id="locationFilter" class="form-select form-select-solid pref-filter"
                            data-control="select2" data-placeholder="All Locations">
                        <option value=""></option>
                        @foreach($filters['locations'] ?? [] as $l)
                            <option value="{{ $l['id'] }}">{{ $l['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="fw-semibold fs-7 mb-2">Experience Level</label>
                    <select id="experienceLevelFilter" class="form-select form-select-solid pref-filter"
                            data-control="select2" data-placeholder="All Experience Levels">
                        <option value=""></option>
                        @foreach($filters['experience_levels'] ?? [] as $e)
                            <option value="{{ $e['id'] }}">{{ $e['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="fw-semibold fs-7 mb-2">Education Level</label>
                    <select id="educationLevelFilter" class="form-select form-select-solid pref-filter"
                            data-control="select2" data-placeholder="All Education Levels">
                        <option value=""></option>
                        @foreach($filters['education_levels'] ?? [] as $e)
                            <option value="{{ $e['id'] }}">{{ $e['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="fw-semibold fs-7 mb-2">Salary Range</label>
                    <select id="salaryRangeFilter" class="form-select form-select-solid pref-filter"
                            data-control="select2" data-placeholder="All Salary Ranges">
                        <option value=""></option>
                        @foreach($filters['salary_ranges'] ?? [] as $s)
                            <option value="{{ $s['id'] }}">{{ $s['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="fw-semibold fs-7 mb-2">Min Years Experience</label>
                    <input type="number" id="minExperienceFilter"
                           class="form-control form-control-solid"
                           placeholder="e.g. 3" min="0" max="60" />
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="button" id="resetFiltersBtn" class="btn btn-light">
                        <i class="ki-duotone ki-arrows-circle fs-2 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <h3 class="card-title fw-bold text-gray-800">Candidates</h3>
        <div class="card-toolbar">
            <span class="badge badge-light-primary fs-7 py-3 px-5" id="totalBadge">0 Total</span>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="loadingSpinner" class="d-none">
            @for ($i = 0; $i < 5; $i++)
                <div class="d-flex align-items-center gap-4 py-4 border-bottom">
                    <div class="skeleton skeleton-circle" style="width:40px;height:40px;"></div>
                    <div class="flex-grow-1">
                        <div class="skeleton skeleton-text" style="width:180px;"></div>
                        <div class="skeleton skeleton-text" style="width:120px;"></div>
                    </div>
                    <div class="skeleton skeleton-text" style="width:100px;"></div>
                </div>
            @endfor
        </div>

        <div id="tableContainer" class="d-none">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-4 mb-0">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-220px">Candidate</th>
                            <th class="min-w-160px">Title</th>
                            <th class="min-w-140px">Location</th>
                            <th class="min-w-90px">Experience</th>
                            <th class="min-w-220px">Preferences</th>
                            <th class="min-w-110px">CV</th>
                            <th class="min-w-110px">Profile</th>
                            <th class="text-end min-w-120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="seekersTableBody"></tbody>
                </table>
            </div>

            <div id="paginationContainer"
                 class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-5 d-none">
                <div id="paginationInfo" class="text-muted fs-7"></div>
                <nav><ul class="pagination pagination-outline m-0" id="pagination"></ul></nav>
            </div>
        </div>

        <div id="noDataMessage" class="text-center py-15 d-none">
            <i class="ki-duotone ki-search-list fs-5tx text-muted mb-4 d-block">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            <h4 class="fw-bold text-gray-800 mb-2">No candidates match</h4>
            <p class="text-muted mb-5">Try removing some filters or broadening your search.</p>
            <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('resetFiltersBtn').click();">
                Reset Filters
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="kt_modal_view_seeker" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold mb-0">Candidate Profile</h2>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <div class="modal-body" id="seekerDetailsContainer"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .skeleton {
        display: inline-block;
        background: linear-gradient(90deg, #f1f1f4 0%, #e9e9ed 50%, #f1f1f4 100%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.4s ease-in-out infinite;
        border-radius: 6px;
    }
    .skeleton-circle { border-radius: 50%; }
    .skeleton-text   { height: 12px; margin: 4px 0; display: block; }
    @keyframes skeleton-shimmer {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .pref-cell .badge { font-size: 0.68rem; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    const state = {
        page: 1,
        perPage: 15,
        search: '',
        category: '',
        industry: '',
        jobType: '',
        location: '',
        experienceLevel: '',
        educationLevel: '',
        salaryRange: '',
        minExperience: '',
        hasCv: '',
        profileComplete: '',
    };

    let searchDebounce;

    document.addEventListener('DOMContentLoaded', () => {
        initSelect2();
        bindEvents();
        loadCandidates();
    });

    function initSelect2() {
        if (typeof jQuery === 'undefined' || !jQuery.fn.select2) return;
        jQuery('.pref-filter').each(function () {
            const $el = jQuery(this);
            if ($el.hasClass('select2-hidden-accessible')) return;
            $el.select2({
                placeholder: $el.data('placeholder') || 'Select...',
                allowClear: true,
                width: '100%',
                dropdownParent: jQuery('#advancedFilters'),
            });
        });
    }

    function bindEvents() {
        document.getElementById('searchInput')?.addEventListener('input', function () {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                state.search = this.value.trim();
                state.page = 1;
                loadCandidates();
            }, 400);
        });

        const bind = (id, setter) => {
            document.getElementById(id)?.addEventListener('change', function () {
                setter(this.value);
                state.page = 1;
                loadCandidates();
            });
        };

        bind('hasCvFilter',           v => state.hasCv = v);
        bind('profileCompleteFilter', v => state.profileComplete = v);
        bind('categoryFilter',        v => state.category = v);
        bind('industryFilter',        v => state.industry = v);
        bind('jobTypeFilter',         v => state.jobType = v);
        bind('locationFilter',        v => state.location = v);
        bind('experienceLevelFilter', v => state.experienceLevel = v);
        bind('educationLevelFilter',  v => state.educationLevel = v);
        bind('salaryRangeFilter',     v => state.salaryRange = v);

        document.getElementById('minExperienceFilter')?.addEventListener('input', function () {
            clearTimeout(this._debounce);
            this._debounce = setTimeout(() => {
                state.minExperience = this.value;
                state.page = 1;
                loadCandidates();
            }, 400);
        });

        document.getElementById('resetFiltersBtn')?.addEventListener('click', resetFilters);
    }

    function resetFilters() {
        Object.keys(state).forEach(k => {
            if (k === 'page') state[k] = 1;
            else if (k === 'perPage') return;
            else state[k] = '';
        });

        ['searchInput', 'hasCvFilter', 'profileCompleteFilter', 'categoryFilter', 'industryFilter',
         'jobTypeFilter', 'locationFilter', 'experienceLevelFilter', 'educationLevelFilter',
         'salaryRangeFilter', 'minExperienceFilter'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            if (typeof jQuery !== 'undefined' && jQuery(el).hasClass('select2-hidden-accessible')) {
                jQuery(el).val('').trigger('change.select2');
            } else {
                el.value = '';
            }
        });

        loadCandidates();
    }

    function loadCandidates() {
        showState('loading');

        const params = new URLSearchParams({ page: state.page, per_page: state.perPage });
        const map = {
            search: 'search',
            category: 'job_category_id',
            industry: 'industry_id',
            jobType: 'job_type_id',
            location: 'job_location_id',
            experienceLevel: 'experience_level_id',
            educationLevel: 'education_level_id',
            salaryRange: 'salary_range_id',
            minExperience: 'min_experience',
            hasCv: 'has_cv',
            profileComplete: 'profile_complete',
        };

        Object.entries(map).forEach(([key, param]) => {
            const v = state[key];
            if (v !== '' && v !== null && v !== undefined) params.set(param, v);
        });

        fetch(`{{ route('employer.cv-filter.data') }}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success || !res.data?.length) {
                showState('empty');
                return;
            }
            showState('table');
            renderTable(res.data);
            renderPagination(res.meta);
            document.getElementById('totalBadge').textContent = `${res.meta.total} Total`;
        })
        .catch(() => {
            showState('empty');
            window.showToast?.('error', 'Failed to load candidates');
        });
    }

    function showState(which) {
        document.getElementById('loadingSpinner').classList.toggle('d-none', which !== 'loading');
        document.getElementById('tableContainer').classList.toggle('d-none',   which !== 'table');
        document.getElementById('noDataMessage').classList.toggle('d-none',    which !== 'empty');
        document.getElementById('paginationContainer').classList.toggle('d-none', which !== 'table');
    }

    function renderTable(rows) {
        document.getElementById('seekersTableBody').innerHTML = rows.map(row => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-40px symbol-circle me-3">
                            <img src="${esc(row.avatar)}" alt="${esc(row.full_name)}" />
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800">${esc(row.full_name)}</span>
                            ${row.city ? `<span class="text-muted fs-7">${esc(row.city)}${row.country ? ', ' + esc(row.country) : ''}</span>` : ''}
                        </div>
                    </div>
                </td>
                <td>${row.professional_title ? esc(row.professional_title) : '<span class="text-muted">-</span>'}</td>
                <td>${row.job_location ? `<span class="text-gray-700 fs-7">${esc(row.job_location)}</span>` : '<span class="text-muted">-</span>'}</td>
                <td>${row.years_of_experience > 0 ? `${row.years_of_experience} yrs` : '<span class="text-muted">-</span>'}</td>
                <td>
                    <div class="d-flex flex-wrap gap-1 pref-cell">
                        ${badge(row.job_category, 'primary')}
                        ${badge(row.industry, 'info')}
                        ${badge(row.experience_level, 'warning')}
                        ${badge(row.education_level, 'success')}
                    </div>
                </td>
                <td>${row.cv_badge ?? ''}</td>
                <td>${row.profile_badge ?? ''}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-light btn-active-light-primary"
                            onclick="viewCandidate(${row.id})">
                        <i class="ki-duotone ki-eye fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                        View
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function badge(value, color) {
        if (!value) return '';
        return `<span class="badge badge-light-${color}">${esc(value)}</span>`;
    }

    function renderPagination(meta) {
        const el = document.getElementById('pagination');
        const info = document.getElementById('paginationInfo');
        if (!el || !meta) return;

        el.innerHTML = '';
        info.textContent = `Showing ${meta.from || 0} to ${meta.to || 0} of ${meta.total} candidates`;

        const item = (page, text, active = false, disabled = false) => {
            const li = document.createElement('li');
            li.className = `page-item ${active ? 'active' : ''} ${disabled ? 'disabled' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = text;
            a.addEventListener('click', e => {
                e.preventDefault();
                if (!disabled && !active) changePage(page);
            });
            li.appendChild(a);
            el.appendChild(li);
        };

        item(meta.current_page - 1, 'Prev', false, meta.current_page <= 1);

        const start = Math.max(1, meta.current_page - 2);
        const end   = Math.min(meta.last_page, meta.current_page + 2);

        if (start > 1) item(1, '1');
        if (start > 2) el.insertAdjacentHTML('beforeend',
            '<li class="page-item disabled"><span class="page-link">...</span></li>');

        for (let i = start; i <= end; i++) item(i, i, i === meta.current_page);

        if (end < meta.last_page - 1) el.insertAdjacentHTML('beforeend',
            '<li class="page-item disabled"><span class="page-link">...</span></li>');
        if (end < meta.last_page) item(meta.last_page, meta.last_page);

        item(meta.current_page + 1, 'Next', false, meta.current_page >= meta.last_page);
    }

    function changePage(page) {
        if (page === state.page || page < 1) return;
        state.page = page;
        loadCandidates();
    }

    window.viewCandidate = function (id) {
        const modalEl = document.getElementById('kt_modal_view_seeker');
        const container = document.getElementById('seekerDetailsContainer');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        container.innerHTML = `
            <div class="text-center py-10">
                <div class="spinner-border text-primary" role="status"></div>
            </div>`;
        modal.show();

        fetch(`{{ url('employer/cv-filter') }}/${id}`, { headers: { 'Accept': 'text/html' } })
            .then(r => r.text())
            .then(html => { container.innerHTML = html; })
            .catch(() => {
                container.innerHTML = '<div class="alert alert-danger">Failed to load candidate.</div>';
            });
    };

    function esc(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }
})();
</script>
@endpush