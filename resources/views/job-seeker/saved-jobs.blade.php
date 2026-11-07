@extends('layouts.admin')

@section('title', 'Saved Jobs')
@section('page_title', 'Saved Jobs')

@section('content')
@php
    $pagination = $pagination ?? [
        'current_page' => 1,
        'last_page'    => 1,
        'per_page'     => 10,
        'total'        => $total ?? 0,
    ];
    $currentPage = $pagination['current_page'];
    $lastPage    = $pagination['last_page'];
    $totalCount  = $pagination['total'];
@endphp

<div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
    <div class="col-xl-12">
        <div class="card card-flush h-md-100">
            {{-- Header --}}
            <div class="card-header pt-7">
                <div class="d-flex align-items-center">
                    <div>
                        <h3 class="card-title fw-bold text-gray-800">Saved Jobs</h3>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Jobs you have saved for later</span>
                    </div>
                </div>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary fs-6 py-3 px-5" id="totalBadge">
                        {{ $totalCount }} Saved
                    </span>
                </div>
            </div>

            {{-- Body --}}
            <div class="card-body pt-6">
                {{-- Search only (client-side, current page) --}}
                <div class="d-flex flex-wrap align-items-center gap-4 mb-7">
                    <div class="position-relative flex-grow-1 min-w-200px">
                        <i class="bi bi-search fs-3 text-gray-500 position-absolute top-50 translate-middle ms-6"></i>
                        <input type="text" class="form-control form-control-solid ps-10"
                               id="searchInput"
                               placeholder="Search this page..." />
                    </div>
                    <button class="btn btn-sm btn-light-primary" id="clearSearchBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Clear
                    </button>
                </div>

                @if(!empty($savedJobs) && count($savedJobs) > 0)
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0" id="savedJobsTable">
                            <thead>
                                <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                    <th class="p-0 pb-3 min-w-250px text-start">JOB</th>
                                    <th class="p-0 pb-3 min-w-150px text-start">COMPANY</th>
                                    <th class="p-0 pb-3 min-w-100px text-start">LOCATION</th>
                                    <th class="p-0 pb-3 min-w-100px text-start">TYPE</th>
                                    <th class="p-0 pb-3 min-w-120px text-start">SALARY</th>
                                    <th class="p-0 pb-3 min-w-100px text-start">STATUS</th>
                                    <th class="p-0 pb-3 w-150px text-end">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="jobsTableBody">
                                @foreach($savedJobs as $job)
                                    @php
                                        $jobTitle    = $job['job_title'] ?? 'Job Title';
                                        $companyName = $job['company']['name'] ?? 'Company';
                                        $companyLogo = $job['company']['logo_url'] ?? null;
                                        $location    = $job['job_location']['name'] ?? $job['duty_station'] ?? 'Not specified';
                                        $salary      = $job['formatted_salary'] ?? 'Negotiable';
                                        $jobType     = $job['job_type']['name'] ?? $job['employment_type'] ?? 'Full-time';
                                        $slug        = $job['slug'] ?? $job['id'];
                                        $isFeatured  = $job['is_featured'] ?? false;
                                        $isUrgent    = $job['is_urgent'] ?? false;
                                    @endphp
                                    <tr class="job-row"
                                        data-id="{{ $job['id'] }}"
                                        data-title="{{ strtolower($jobTitle) }}"
                                        data-company="{{ strtolower($companyName) }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    @if($companyLogo)
                                                        <img src="{{ $companyLogo }}" alt="{{ $companyName }}"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                                        <div class="symbol-label bg-light-primary text-primary fs-4 fw-bold" style="display:none;">
                                                            {{ strtoupper(substr($companyName, 0, 2)) }}
                                                        </div>
                                                    @else
                                                        <div class="symbol-label bg-light-primary text-primary fs-4 fw-bold">
                                                            {{ strtoupper(substr($companyName, 0, 2)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <a href="{{ route('jobs.show', $slug) }}" target="_blank"
                                                       class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">
                                                        {{ $jobTitle }}
                                                    </a>
                                                    <span class="text-gray-500 fw-semibold d-block fs-7">
                                                        <i class="bi bi-building me-1"></i>{{ $companyName }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-gray-600 fw-semibold fs-7">{{ $companyName }}</span></td>
                                        <td>
                                            <span class="text-gray-600 fw-semibold fs-7">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $location }}
                                            </span>
                                        </td>
                                        <td><span class="badge badge-light-primary fs-7 py-2 px-3">{{ $jobType }}</span></td>
                                        <td><span class="text-success fw-bold fs-6 salary-text">{{ $salary }}</span></td>
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                @if($isFeatured)
                                                    <span class="badge badge-light-warning fs-7 py-2 px-3">
                                                        <i class="bi bi-star me-1"></i>Featured
                                                    </span>
                                                @endif
                                                @if($isUrgent)
                                                    <span class="badge badge-light-danger fs-7 py-2 px-3">
                                                        <i class="bi bi-exclamation-triangle me-1"></i>Urgent
                                                    </span>
                                                @endif
                                                @if(!$isFeatured && !$isUrgent)
                                                    <span class="badge badge-light-secondary fs-7 py-2 px-3">Saved</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('jobs.show', $slug) }}" target="_blank"
                                                   class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px"
                                                   title="View Job">
                                                    <i class="bi bi-eye fs-5 text-gray-500"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger w-30px h-30px unsave-job-btn"
                                                        data-job-id="{{ $job['id'] }}"
                                                        data-job-title="{{ $jobTitle }}"
                                                        title="Unsave Job">
                                                    <i class="bi bi-trash3 fs-5 text-danger"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- No-match message for client search --}}
                    <div id="noResults" class="text-center py-10" style="display:none;">
                        <div class="symbol symbol-80px bg-light-secondary rounded-3 d-flex align-items-center justify-content-center mx-auto mb-4">
                            <i class="bi bi-search fs-3x text-muted"></i>
                        </div>
                        <h5 class="fw-bold mb-2">No matching jobs on this page</h5>
                        <p class="text-muted mb-4">Try a different search, or clear it to see all.</p>
                        <button class="btn btn-primary" id="clearSearchBtn2">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Clear Search
                        </button>
                    </div>

                    {{-- ── Pagination ────────────────────────── --}}
                    @if($lastPage > 1)
                        <div class="d-flex justify-content-between align-items-center mt-6 flex-wrap gap-2">
                            <div class="text-muted fs-7">
                                Page <span class="fw-bold">{{ $currentPage }}</span>
                                of <span class="fw-bold">{{ $lastPage }}</span>
                                — {{ $totalCount }} saved
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm m-0">
                                    <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => max(1, $currentPage - 1)]) }}">
                                            ‹ Prev
                                        </a>
                                    </li>

                                    @php
                                        $start = max(1, $currentPage - 2);
                                        $end   = min($lastPage, $currentPage + 2);
                                    @endphp

                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled"><span class="page-link">…</span></li>
                                        @endif
                                    @endif

                                    @for($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $i === $currentPage ? 'active' : '' }}">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                        </li>
                                    @endfor

                                    @if($end < $lastPage)
                                        @if($end < $lastPage - 1)
                                            <li class="page-item disabled"><span class="page-link">…</span></li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $lastPage]) }}">{{ $lastPage }}</a>
                                        </li>
                                    @endif

                                    <li class="page-item {{ $currentPage >= $lastPage ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => min($lastPage, $currentPage + 1)]) }}">
                                            Next ›
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    @endif
                @else
                    {{-- Empty state --}}
                    <div class="text-center py-10">
                        <div class="symbol symbol-80px bg-light-secondary rounded-3 d-flex align-items-center justify-content-center mx-auto mb-4">
                            <i class="bi bi-heart fs-3x text-muted"></i>
                        </div>
                        <h5 class="fw-bold mb-2">No Saved Jobs</h5>
                        <p class="text-muted mb-4">You haven't saved any jobs yet.</p>
                        <a href="{{ route('jobs.index') }}" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i> Browse Jobs
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf          = document.querySelector('meta[name="csrf-token"]')?.content;
    const searchInput   = document.getElementById('searchInput');
    const tableBody     = document.getElementById('jobsTableBody');
    const noResults     = document.getElementById('noResults');
    const totalBadge    = document.getElementById('totalBadge');

    let currentRows = [];

    function storeRows() {
        if (tableBody) {
            currentRows = Array.from(tableBody.querySelectorAll('.job-row'));
        }
    }

    // ── Client-side search within the current page ─────────
    function filterRows() {
        if (!tableBody) return;
        const term = searchInput ? searchInput.value.toLowerCase().trim() : '';

        let visible = 0;
        currentRows.forEach(row => {
            const title   = row.dataset.title   || '';
            const company = row.dataset.company || '';
            const match   = term === '' || title.includes(term) || company.includes(term);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        const empty = visible === 0 && currentRows.length > 0;
        if (noResults) noResults.style.display = empty ? 'block' : 'none';
        if (tableBody) tableBody.style.display = empty ? 'none' : '';
    }

    searchInput?.addEventListener('input', filterRows);

    function clearSearch() {
        if (searchInput) searchInput.value = '';
        filterRows();
    }
    document.getElementById('clearSearchBtn')?.addEventListener('click', clearSearch);
    document.getElementById('clearSearchBtn2')?.addEventListener('click', clearSearch);

    // ── Unsave ─────────────────────────────────────────────
    document.querySelectorAll('.unsave-job-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId    = this.dataset.jobId;
            const jobTitle = this.dataset.jobTitle;

            if (!confirm(`Remove "${jobTitle}" from saved jobs?`)) return;

            const originalHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`/jobs/${jobId}/unsave`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('success', 'Job removed from saved.', 'Unsaved');
                    }

                    const row = this.closest('tr');
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';

                        setTimeout(() => {
                            row.remove();
                            currentRows = currentRows.filter(r => r !== row);

                            // If this page is now empty but more pages exist, reload
                            if (currentRows.length === 0) {
                                location.reload();
                                return;
                            }

                            filterRows();

                            // Update badge count
                            const current = parseInt((totalBadge?.textContent || '0').match(/\d+/)?.[0] || '0', 10);
                            if (totalBadge && current > 0) {
                                totalBadge.textContent = (current - 1) + ' Saved';
                            }
                        }, 300);
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast('error', data.message || 'Failed to unsave job.', 'Error');
                    }
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                }
            })
            .catch(err => {
                console.error(err);
                if (typeof window.showToast === 'function') {
                    window.showToast('error', 'Something went wrong. Please try again.', 'Error');
                }
                this.disabled = false;
                this.innerHTML = originalHtml;
            });
        });
    });

    // Boot
    storeRows();
    filterRows();
});
</script>
@endpush