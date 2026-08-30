@extends('layouts.admin')

@section('title', 'Saved Jobs')
@section('page_title', 'Saved Jobs')

@section('content')
<div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
    <div class="col-xl-12">
        <div class="card card-flush h-md-100">
            <!--begin::Header-->
            <div class="card-header pt-7">
                <div class="d-flex align-items-center">
                    <div>
                        <h3 class="card-title fw-bold text-gray-800">Saved Jobs</h3>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Jobs you have saved for later</span>
                    </div>
                </div>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary fs-6 py-3 px-5" id="totalBadge">{{ $total ?? 0 }} Saved</span>
                </div>
            </div>
            <!--end::Header-->
            
            <!--begin::Body-->
            <div class="card-body pt-6">
                <!-- Search & Filters -->
                <div class="d-flex flex-wrap align-items-center gap-4 mb-7">
                    <!-- Search -->
                    <div class="position-relative flex-grow-1 min-w-200px">
                        <i class="bi bi-search fs-3 text-gray-500 position-absolute top-50 translate-middle ms-6"></i>
                        <input type="text" class="form-control form-control-solid ps-10" 
                               id="searchInput" 
                               placeholder="Search saved jobs..." />
                    </div>

                    <!-- Sort By -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-7 fw-bold text-gray-700 pe-2 text-nowrap d-none d-md-block">Sort By:</span>
                        <select class="form-select form-select-sm form-select-solid w-150px" id="sortSelect">
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                            <option value="company">Company A-Z</option>
                            <option value="salary_high">Highest Salary</option>
                            <option value="salary_low">Lowest Salary</option>
                        </select>
                    </div>

                    <!-- Filter by Status -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-7 fw-bold text-gray-700 pe-2 d-none d-md-block">Filter:</span>
                        <select class="form-select form-select-sm form-select-solid w-140px" id="filterSelect">
                            <option value="all">All Jobs</option>
                            <option value="featured">Featured</option>
                            <option value="urgent">Urgent</option>
                            <option value="normal">Normal</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <button class="btn btn-sm btn-light-primary" id="clearFiltersBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>

                <!-- Jobs Table -->
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
                                        $jobTitle = $job['job_title'] ?? 'Job Title';
                                        $companyName = $job['company']['name'] ?? 'Company';
                                        $companyLogo = $job['company']['logo_url'] ?? null;
                                        $location = $job['job_location']['name'] ?? $job['duty_station'] ?? 'Not specified';
                                        $salary = $job['formatted_salary'] ?? 'Negotiable';
                                        $jobType = $job['job_type']['name'] ?? $job['employment_type'] ?? 'Full-time';
                                        $slug = $job['slug'] ?? $job['id'];
                                        $isFeatured = $job['is_featured'] ?? false;
                                        $isUrgent = $job['is_urgent'] ?? false;
                                    @endphp
                                    <tr class="job-row" 
                                        data-id="{{ $job['id'] }}"
                                        data-title="{{ strtolower($jobTitle) }}"
                                        data-company="{{ strtolower($companyName) }}"
                                        data-featured="{{ $isFeatured ? '1' : '0' }}"
                                        data-urgent="{{ $isUrgent ? '1' : '0' }}"
                                        data-salary="{{ $job['salary_amount'] ?? 0 }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    @if($companyLogo)
                                                        <img src="{{ $companyLogo }}" class="" alt="{{ $companyName }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
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
                                                    <a href="{{ route('jobs.show', $slug) }}" target="_blank" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">
                                                        {{ $jobTitle }}
                                                    </a>
                                                    <span class="text-gray-500 fw-semibold d-block fs-7">
                                                        <i class="bi bi-building me-1"></i>{{ $companyName }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-600 fw-semibold fs-7">{{ $companyName }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-600 fw-semibold fs-7">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $location }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-primary fs-7 py-2 px-3">{{ $jobType }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold fs-6 salary-text">{{ $salary }}</span>
                                        </td>
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
                                                <a href="{{ route('jobs.show', $slug) }}" target="_blank" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px" title="View Job">
                                                    <i class="bi bi-eye fs-5 text-gray-500"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger w-30px h-30px unsave-job-btn" 
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
                    
                    <!-- No results message -->
                    <div id="noResults" class="text-center py-10" style="display: none;">
                        <div class="symbol symbol-80px bg-light-secondary rounded-3 d-flex align-items-center justify-content-center mx-auto mb-4">
                            <i class="bi bi-search fs-3x text-muted"></i>
                        </div>
                        <h5 class="fw-bold mb-2">No matching jobs found</h5>
                        <p class="text-muted mb-4">Try adjusting your search or filters.</p>
                        <button class="btn btn-primary" id="clearFiltersBtn2">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Clear Filters
                        </button>
                    </div>
                @else
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
            <!--end: Card Body-->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const filterSelect = document.getElementById('filterSelect');
    const tableBody = document.getElementById('jobsTableBody');
    const noResults = document.getElementById('noResults');
    const totalBadge = document.getElementById('totalBadge');
    let currentRows = [];

    // Store all rows initially
    function storeRows() {
        if (tableBody) {
            currentRows = Array.from(tableBody.querySelectorAll('.job-row'));
        }
    }

    // Filter and sort function
    function filterAndSort() {
        if (!tableBody) return;

        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const sortValue = sortSelect ? sortSelect.value : 'newest';
        const filterValue = filterSelect ? filterSelect.value : 'all';

        // Filter
        let visibleRows = currentRows.filter(row => {
            const title = row.dataset.title || '';
            const company = row.dataset.company || '';
            const isFeatured = row.dataset.featured === '1';
            const isUrgent = row.dataset.urgent === '1';

            // Search filter
            const matchesSearch = searchTerm === '' || 
                title.includes(searchTerm) || 
                company.includes(searchTerm);

            // Status filter
            let matchesFilter = true;
            if (filterValue === 'featured') {
                matchesFilter = isFeatured;
            } else if (filterValue === 'urgent') {
                matchesFilter = isUrgent;
            } else if (filterValue === 'normal') {
                matchesFilter = !isFeatured && !isUrgent;
            }

            return matchesSearch && matchesFilter;
        });

        // Sort
        visibleRows.sort((a, b) => {
            const titleA = a.dataset.title || '';
            const titleB = b.dataset.title || '';
            const companyA = a.dataset.company || '';
            const companyB = b.dataset.company || '';
            const salaryA = parseFloat(a.dataset.salary) || 0;
            const salaryB = parseFloat(b.dataset.salary) || 0;

            switch (sortValue) {
                case 'newest':
                    return a.dataset.id - b.dataset.id;
                case 'oldest':
                    return b.dataset.id - a.dataset.id;
                case 'company':
                    return companyA.localeCompare(companyB);
                case 'salary_high':
                    return salaryB - salaryA;
                case 'salary_low':
                    return salaryA - salaryB;
                default:
                    return 0;
            }
        });

        // Update table
        tableBody.innerHTML = '';
        visibleRows.forEach(row => {
            tableBody.appendChild(row);
        });

        // Show/hide no results
        if (noResults) {
            if (visibleRows.length === 0 && currentRows.length > 0) {
                noResults.style.display = 'block';
                tableBody.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                tableBody.style.display = '';
            }
        }

        // Update count
        if (totalBadge) {
            totalBadge.textContent = visibleRows.length + ' Saved';
        }
    }

    // Event listeners
    if (searchInput) {
        searchInput.addEventListener('keyup', filterAndSort);
        searchInput.addEventListener('search', filterAndSort);
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', filterAndSort);
    }

    if (filterSelect) {
        filterSelect.addEventListener('change', filterAndSort);
    }

    // Clear filters
    function clearFilters() {
        if (searchInput) searchInput.value = '';
        if (sortSelect) sortSelect.value = 'newest';
        if (filterSelect) filterSelect.value = 'all';
        filterAndSort();
    }

    document.getElementById('clearFiltersBtn')?.addEventListener('click', clearFilters);
    document.getElementById('clearFiltersBtn2')?.addEventListener('click', clearFilters);

    // Initialize
    storeRows();
    filterAndSort();

    // Unsave Job
    document.querySelectorAll('.unsave-job-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.dataset.jobId;
            const jobTitle = this.dataset.jobTitle;
            
            if (!confirm(`Remove "${jobTitle}" from saved jobs?`)) {
                return;
            }
            
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
            .then(response => response.json())
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
                            filterAndSort();
                            // Update count
                            if (totalBadge) {
                                const currentCount = parseInt(totalBadge.textContent) || 0;
                                if (currentCount > 0) {
                                    totalBadge.textContent = (currentCount - 1) + ' Saved';
                                }
                            }
                            if (currentRows.length === 0) {
                                location.reload();
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
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('error', 'Something went wrong. Please try again.', 'Error');
                }
                this.disabled = false;
                this.innerHTML = originalHtml;
            });
        });
    });
});
</script>
@endpush