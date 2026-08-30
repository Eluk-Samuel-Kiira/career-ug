@extends('layouts.admin')

@section('title', 'My Applications')
@section('page_title', 'My Applications')

@section('content')
<div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
    <div class="col-xl-12">
        <div class="card card-flush h-md-100">
            <!--begin::Header-->
            <div class="card-header pt-7">
                <div class="d-flex align-items-center">
                    <div>
                        <h3 class="card-title fw-bold text-gray-800">My Applications</h3>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Track your job application progress</span>
                    </div>
                </div>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary fs-6 py-3 px-5" id="totalBadge">{{ $total ?? 0 }} Applications</span>
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
                               placeholder="Search applications by job title or company..." />
                    </div>

                    <!-- Sort By -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-7 fw-bold text-gray-700 pe-2 text-nowrap d-none d-md-block">Sort By:</span>
                        <select class="form-select form-select-sm form-select-solid w-150px" id="sortSelect">
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                            <option value="company">Company A-Z</option>
                            <option value="status">Status</option>
                        </select>
                    </div>

                    <!-- Filter by Status -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-7 fw-bold text-gray-700 pe-2 d-none d-md-block">Filter:</span>
                        <select class="form-select form-select-sm form-select-solid w-140px" id="filterSelect">
                            <option value="all">All Status</option>
                            <option value="applied">Applied</option>
                            <option value="interviewing">Interviewing</option>
                            <option value="hired">Hired</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <button class="btn btn-sm btn-light-primary" id="clearFiltersBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>

                <!-- Applications Table -->
                @if(!empty($appliedJobs) && count($appliedJobs) > 0)
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0" id="appliedJobsTable">
                            <thead>
                                <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                    <th class="p-0 pb-3 min-w-250px text-start">JOB</th>
                                    <th class="p-0 pb-3 min-w-150px text-start">COMPANY</th>
                                    <th class="p-0 pb-3 min-w-100px text-start">LOCATION</th>
                                    <th class="p-0 pb-3 min-w-120px text-start">APPLIED</th>
                                    <th class="p-0 pb-3 min-w-120px text-start">STATUS</th>
                                    <th class="p-0 pb-3 w-200px text-end">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="jobsTableBody">
                                @foreach($appliedJobs as $item)
                                    @php
                                        $job = $item['job'] ?? $item;
                                        $jobTitle = $job['job_title'] ?? 'Job Title';
                                        $companyName = $job['company']['name'] ?? 'Company';
                                        $companyLogo = $job['company']['logo_url'] ?? null;
                                        $location = $job['job_location']['name'] ?? $job['duty_station'] ?? 'Not specified';
                                        $slug = $job['slug'] ?? $job['id'];
                                        $appliedAt = $item['applied_at'] ?? null;
                                        $status = $item['status'] ?? 'applied';
                                        
                                        $statusColors = [
                                            'applied' => 'primary',
                                            'interviewing' => 'warning',
                                            'hired' => 'success',
                                            'rejected' => 'danger',
                                        ];
                                        $statusIcons = [
                                            'applied' => 'bi-clock',
                                            'interviewing' => 'bi-calendar-check',
                                            'hired' => 'bi-check-circle',
                                            'rejected' => 'bi-x-circle',
                                        ];
                                        $statusLabels = [
                                            'applied' => 'Applied',
                                            'interviewing' => 'Interviewing',
                                            'hired' => 'Hired 🎉',
                                            'rejected' => 'Rejected',
                                        ];
                                        $statusDescriptions = [
                                            'applied' => 'Your application has been submitted',
                                            'interviewing' => 'You have been called for an interview',
                                            'hired' => 'Congratulations! You got the job!',
                                            'rejected' => 'Unfortunately, you were not selected',
                                        ];
                                        $statusColor = $statusColors[$status] ?? 'secondary';
                                        $statusIcon = $statusIcons[$status] ?? 'bi-clock';
                                        $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                                        $statusDescription = $statusDescriptions[$status] ?? '';
                                    @endphp
                                    <tr class="job-row" 
                                        data-id="{{ $job['id'] }}"
                                        data-slug="{{ $slug }}"
                                        data-title="{{ strtolower($jobTitle) }}"
                                        data-company="{{ strtolower($companyName) }}"
                                        data-status="{{ $status }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    @if($companyLogo)
                                                        <img src="{{ $companyLogo }}" class="" alt="{{ $companyName }}" 
                                                             onerror="this.style.display='none'; this.parentElement.querySelector('.symbol-label').style.display='flex';" />
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
                                            <span class="text-gray-600 fw-semibold fs-7">
                                                @if($appliedAt)
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($appliedAt)->format('M d, Y') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column align-items-start">
                                                <span class="badge badge-light-{{ $statusColor }} fs-7 py-2 px-3 status-badge">
                                                    <i class="{{ $statusIcon }} me-1"></i>
                                                    {{ $statusLabel }}
                                                </span>
                                                <span class="text-muted fs-8 mt-1 status-description">{{ $statusDescription }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2 flex-wrap" data-job-id="{{ $job['id'] }}" data-slug="{{ $slug }}">
                                                <a href="{{ route('jobs.show', $slug) }}" target="_blank" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px" title="View Job">
                                                    <i class="bi bi-eye fs-5 text-gray-500"></i>
                                                </a>
                                                
                                                @if($status === 'applied')
                                                    <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-warning w-30px h-30px update-status-btn" 
                                                            data-job-id="{{ $job['id'] }}"
                                                            data-status="interviewing"
                                                            title="Mark as Interviewing">
                                                        <i class="bi bi-calendar-check fs-5 text-warning"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger w-30px h-30px update-status-btn" 
                                                            data-job-id="{{ $job['id'] }}"
                                                            data-status="rejected"
                                                            title="Mark as Rejected">
                                                        <i class="bi bi-x-circle fs-5 text-danger"></i>
                                                    </button>
                                                @endif
                                                
                                                @if($status === 'interviewing')
                                                    <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-success w-30px h-30px update-status-btn" 
                                                            data-job-id="{{ $job['id'] }}"
                                                            data-status="hired"
                                                            title="Mark as Hired">
                                                        <i class="bi bi-check-circle fs-5 text-success"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger w-30px h-30px update-status-btn" 
                                                            data-job-id="{{ $job['id'] }}"
                                                            data-status="rejected"
                                                            title="Mark as Rejected">
                                                        <i class="bi bi-x-circle fs-5 text-danger"></i>
                                                    </button>
                                                @endif
                                                
                                                @if($status === 'hired' || $status === 'rejected')
                                                    <span class="text-muted fs-7 d-flex align-items-center">
                                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                        Final
                                                    </span>
                                                @endif
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
                        <h5 class="fw-bold mb-2">No matching applications found</h5>
                        <p class="text-muted mb-4">Try adjusting your search or filters.</p>
                        <button class="btn btn-primary" id="clearFiltersBtn2">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Clear Filters
                        </button>
                    </div>
                @else
                    <div class="text-center py-10">
                        <div class="symbol symbol-80px bg-light-secondary rounded-3 d-flex align-items-center justify-content-center mx-auto mb-4">
                            <i class="bi bi-briefcase fs-3x text-muted"></i>
                        </div>
                        <h5 class="fw-bold mb-2">No Applications Yet</h5>
                        <p class="text-muted mb-4">You haven't applied to any jobs yet.</p>
                        <a href="{{ route('jobs.index') }}" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i> Find Jobs
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

        let visibleRows = currentRows.filter(row => {
            const title = row.dataset.title || '';
            const company = row.dataset.company || '';
            const status = row.dataset.status || '';

            const matchesSearch = searchTerm === '' || 
                title.includes(searchTerm) || 
                company.includes(searchTerm);

            const matchesFilter = filterValue === 'all' || status === filterValue;

            return matchesSearch && matchesFilter;
        });

        // Sort
        const statusOrder = { 'applied': 0, 'interviewing': 1, 'hired': 2, 'rejected': 3 };
        
        visibleRows.sort((a, b) => {
            const titleA = a.dataset.title || '';
            const titleB = b.dataset.title || '';
            const companyA = a.dataset.company || '';
            const companyB = b.dataset.company || '';
            const statusA = a.dataset.status || '';
            const statusB = b.dataset.status || '';

            switch (sortValue) {
                case 'newest':
                    return b.dataset.id - a.dataset.id;
                case 'oldest':
                    return a.dataset.id - b.dataset.id;
                case 'company':
                    return companyA.localeCompare(companyB);
                case 'status':
                    return (statusOrder[statusA] || 0) - (statusOrder[statusB] || 0);
                default:
                    return 0;
            }
        });

        tableBody.innerHTML = '';
        visibleRows.forEach(row => {
            tableBody.appendChild(row);
        });

        if (noResults) {
            if (visibleRows.length === 0 && currentRows.length > 0) {
                noResults.style.display = 'block';
                tableBody.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                tableBody.style.display = '';
            }
        }

        if (totalBadge) {
            totalBadge.textContent = visibleRows.length + ' Applications';
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

    function clearFilters() {
        if (searchInput) searchInput.value = '';
        if (sortSelect) sortSelect.value = 'newest';
        if (filterSelect) filterSelect.value = 'all';
        filterAndSort();
    }

    document.getElementById('clearFiltersBtn')?.addEventListener('click', clearFilters);
    document.getElementById('clearFiltersBtn2')?.addEventListener('click', clearFilters);

    storeRows();
    filterAndSort();

    // Update Application Status
    document.querySelectorAll('.update-status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.dataset.jobId;
            const newStatus = this.dataset.status;
            const row = this.closest('tr');
            const slug = row ? row.dataset.slug : '';
            
            const statusLabels = {
                'hired': 'HIRED - Congratulations! 🎉',
                'rejected': 'REJECTED',
                'interviewing': 'CALLED FOR INTERVIEW'
            };
            
            if (!confirm(`Are you sure you want to mark this application as ${statusLabels[newStatus] || newStatus}?`)) {
                return;
            }
            
            const originalHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            fetch(`/jobs/${jobId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast('success', `✅ Application marked as ${statusLabels[newStatus] || newStatus}!`, 'Status Updated');
                    }
                    
                    if (row) {
                        const statusBadge = row.querySelector('.status-badge');
                        const statusDesc = row.querySelector('.status-description');
                        const actionsCell = row.querySelector('td:last-child');
                        
                        const statusMap = {
                            'hired': { color: 'success', icon: 'bi-check-circle', label: 'Hired 🎉', desc: 'Congratulations! You got the job!' },
                            'rejected': { color: 'danger', icon: 'bi-x-circle', label: 'Rejected', desc: 'Unfortunately, you were not selected' },
                            'interviewing': { color: 'warning', icon: 'bi-calendar-check', label: 'Interviewing', desc: 'You have been called for an interview' }
                        };
                        const info = statusMap[newStatus] || { color: 'primary', icon: 'bi-clock', label: 'Applied', desc: 'Your application has been submitted' };
                        row.dataset.status = newStatus;
                        
                        if (statusBadge) {
                            statusBadge.className = `badge badge-light-${info.color} fs-7 py-2 px-3 status-badge`;
                            statusBadge.innerHTML = `<i class="${info.icon} me-1"></i> ${info.label}`;
                        }
                        if (statusDesc) {
                            statusDesc.textContent = info.desc;
                        }
                        
                        if (actionsCell) {
                            let newHtml = `<div class="d-flex justify-content-end gap-2 flex-wrap">`;
                            newHtml += `<a href="/jobs/${slug}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px" title="View Job">
                                <i class="bi bi-eye fs-5 text-gray-500"></i>
                            </a>`;
                            
                            if (newStatus === 'hired' || newStatus === 'rejected') {
                                newHtml += `<span class="text-muted fs-7 d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    Final
                                </span>`;
                            } else if (newStatus === 'applied') {
                                newHtml += `
                                    <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-warning w-30px h-30px update-status-btn" 
                                            data-job-id="${jobId}" data-status="interviewing" title="Mark as Interviewing">
                                        <i class="bi bi-calendar-check fs-5 text-warning"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger w-30px h-30px update-status-btn" 
                                            data-job-id="${jobId}" data-status="rejected" title="Mark as Rejected">
                                        <i class="bi bi-x-circle fs-5 text-danger"></i>
                                    </button>`;
                            } else if (newStatus === 'interviewing') {
                                newHtml += `
                                    <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-success w-30px h-30px update-status-btn" 
                                            data-job-id="${jobId}" data-status="hired" title="Mark as Hired">
                                        <i class="bi bi-check-circle fs-5 text-success"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger w-30px h-30px update-status-btn" 
                                            data-job-id="${jobId}" data-status="rejected" title="Mark as Rejected">
                                        <i class="bi bi-x-circle fs-5 text-danger"></i>
                                    </button>`;
                            }
                            newHtml += `</div>`;
                            actionsCell.innerHTML = newHtml;
                            
                            // Re-bind event listeners
                            actionsCell.querySelectorAll('.update-status-btn').forEach(newBtn => {
                                newBtn.addEventListener('click', function() {
                                    this.dispatchEvent(new Event('click'));
                                });
                            });
                        }
                    }
                    
                    filterAndSort();
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast('error', data.message || 'Failed to update status.', 'Error');
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