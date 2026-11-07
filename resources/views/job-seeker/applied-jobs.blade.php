@extends('layouts.admin')

@section('title', 'My Applications')
@section('page_title', 'My Applications')

@section('content')
@php
    $pagination  = $pagination ?? [
        'current_page' => 1, 'last_page' => 1, 'per_page' => 10, 'total' => $total ?? 0,
    ];
    $currentPage = $pagination['current_page'];
    $lastPage    = $pagination['last_page'];
    $totalCount  = $pagination['total'];
    $currentStatus = request('status', 'all');
@endphp

<div class="row g-5 gx-xl-10 mb-5 mb-xl-10">
    <div class="col-xl-12">
        <div class="card card-flush h-md-100">

            {{-- ── Header ──────────────────────────────────── --}}
            <div class="card-header pt-7">
                <div class="d-flex align-items-center">
                    <div>
                        <h3 class="card-title fw-bold text-gray-800">My Applications</h3>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Track your job application progress</span>
                    </div>
                </div>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary fs-6 py-3 px-5" id="totalBadge">
                        {{ $totalCount }} Applications
                    </span>
                </div>
            </div>

            {{-- ── Status filter tabs ──────────────────────── --}}
            <div class="card-body pt-4 pb-0">
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @php
                        $tabs = [
                            'all'          => 'All',
                            'applied'      => 'Applied',
                            'interviewing' => 'Interviewing',
                            'hired'        => 'Hired',
                            'rejected'     => 'Rejected',
                        ];
                    @endphp
                    @foreach($tabs as $key => $label)
                        @php
                            $isActive = $currentStatus === $key;
                            $url = $key === 'all'
                                ? route('jobs.applied')
                                : route('jobs.applied', ['status' => $key]);
                        @endphp
                        <a href="{{ $url }}"
                           class="btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-light-primary' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ── Body ────────────────────────────────────── --}}
            <div class="card-body pt-0">
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
                                        $job         = $item['job'] ?? $item;
                                        $jobTitle    = $job['job_title'] ?? 'Job Title';
                                        $companyName = $job['company']['name'] ?? 'Company';
                                        $companyLogo = $job['company']['logo_url'] ?? null;
                                        $location    = $job['job_location']['name'] ?? $job['duty_station'] ?? 'Not specified';
                                        $slug        = $job['slug'] ?? $job['id'];
                                        $appliedAt   = $item['applied_at'] ?? null;
                                        $status      = $item['status'] ?? 'applied';
                                        $isExpired   = $item['is_expired']  ?? false;
                                        $isInactive  = $item['is_inactive'] ?? false;
                                        $isLive      = !$isExpired && !$isInactive;

                                        $statusColors = [
                                            'applied'      => 'primary',
                                            'interviewing' => 'warning',
                                            'hired'        => 'success',
                                            'rejected'     => 'danger',
                                        ];
                                        $statusIcons = [
                                            'applied'      => 'bi-clock',
                                            'interviewing' => 'bi-calendar-check',
                                            'hired'        => 'bi-check-circle',
                                            'rejected'     => 'bi-x-circle',
                                        ];
                                        $statusLabels = [
                                            'applied'      => 'Applied',
                                            'interviewing' => 'Interviewing',
                                            'hired'        => 'Hired 🎉',
                                            'rejected'     => 'Rejected',
                                        ];
                                        $statusDescriptions = [
                                            'applied'      => 'Your application has been submitted',
                                            'interviewing' => 'You have been called for an interview',
                                            'hired'        => 'Congratulations! You got the job!',
                                            'rejected'     => 'Unfortunately, you were not selected',
                                        ];
                                        $statusColor = $statusColors[$status] ?? 'secondary';
                                        $statusIcon  = $statusIcons[$status] ?? 'bi-clock';
                                        $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                                        $statusDescription = $statusDescriptions[$status] ?? '';
                                    @endphp
                                    <tr class="job-row"
                                        data-id="{{ $job['id'] }}"
                                        data-slug="{{ $slug }}"
                                        data-status="{{ $status }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    @if($companyLogo)
                                                        <img src="{{ $companyLogo }}" alt="{{ $companyName }}"
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
                                                    <a href="{{ route('jobs.show', $slug) }}" target="_blank"
                                                       class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 {{ !$isLive ? 'text-muted' : '' }}">
                                                        {{ $jobTitle }}
                                                    </a>
                                                    <span class="text-gray-500 fw-semibold d-block fs-7">
                                                        <i class="bi bi-building me-1"></i>{{ $companyName }}
                                                    </span>
                                                    @if(!$isLive)
                                                        <span class="badge badge-light-secondary fs-9 mt-1 align-self-start">
                                                            <i class="bi bi-exclamation-circle me-1"></i>
                                                            {{ $isExpired ? 'Expired' : 'No longer active' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="text-gray-600 fw-semibold fs-7">{{ $companyName }}</span></td>
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
                                                    —
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
                                            <div class="d-flex justify-content-end gap-2 flex-wrap"
                                                 data-job-id="{{ $job['id'] }}" data-slug="{{ $slug }}">
                                                <a href="{{ route('jobs.show', $slug) }}" target="_blank"
                                                   class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px"
                                                   title="View Job">
                                                    <i class="bi bi-eye fs-5 text-gray-500"></i>
                                                </a>

                                                @if($isLive && $status === 'applied')
                                                    <button type="button"
                                                            class="btn btn-sm btn-icon btn-bg-light btn-active-color-warning w-30px h-30px update-status-btn"
                                                            data-job-id="{{ $job['id'] }}"
                                                            data-status="interviewing"
                                                            title="Mark as Interviewing">
                                                        <i class="bi bi-calendar-check fs-5 text-warning"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger w-30px h-30px update-status-btn"
                                                            data-job-id="{{ $job['id'] }}"
                                                            data-status="rejected"
                                                            title="Mark as Rejected">
                                                        <i class="bi bi-x-circle fs-5 text-danger"></i>
                                                    </button>
                                                @endif

                                                @if($isLive && $status === 'interviewing')
                                                    <button type="button"
                                                            class="btn btn-sm btn-icon btn-bg-light btn-active-color-success w-30px h-30px update-status-btn"
                                                            data-job-id="{{ $job['id'] }}"
                                                            data-status="hired"
                                                            title="Mark as Hired">
                                                        <i class="bi bi-check-circle fs-5 text-success"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger w-30px h-30px update-status-btn"
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

                    {{-- ── Pagination ──────────────────────── --}}
                    @if($lastPage > 1)
                        <div class="d-flex justify-content-between align-items-center mt-6 flex-wrap gap-2">
                            <div class="text-muted fs-7">
                                Page <span class="fw-bold">{{ $currentPage }}</span>
                                of <span class="fw-bold">{{ $lastPage }}</span>
                                — {{ $totalCount }} applications
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm m-0">
                                    <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                        <a class="page-link"
                                           href="{{ request()->fullUrlWithQuery(['page' => max(1, $currentPage - 1)]) }}">
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
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $lastPage]) }}">
                                                {{ $lastPage }}
                                            </a>
                                        </li>
                                    @endif

                                    <li class="page-item {{ $currentPage >= $lastPage ? 'disabled' : '' }}">
                                        <a class="page-link"
                                           href="{{ request()->fullUrlWithQuery(['page' => min($lastPage, $currentPage + 1)]) }}">
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
                            <i class="bi bi-briefcase fs-3x text-muted"></i>
                        </div>
                        <h5 class="fw-bold mb-2">
                            {{ $currentStatus === 'all' ? 'No Applications Yet' : 'No applications in this category' }}
                        </h5>
                        <p class="text-muted mb-4">
                            {{ $currentStatus === 'all' ? "You haven't applied to any jobs yet." : 'Try switching the filter above.' }}
                        </p>
                        @if($currentStatus === 'all')
                            <a href="{{ route('jobs.index') }}" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i> Find Jobs
                            </a>
                        @else
                            <a href="{{ route('jobs.applied') }}" class="btn btn-light-primary">
                                View all applications
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    // ── Inline status update ─────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.update-status-btn');
        if (!btn) return;

        const jobId     = btn.dataset.jobId;
        const newStatus = btn.dataset.status;
        const row       = btn.closest('tr');
        if (!row) return;

        const labels = {
            hired:        'HIRED - Congratulations! 🎉',
            rejected:     'REJECTED',
            interviewing: 'CALLED FOR INTERVIEW',
        };

        if (!confirm(`Mark this application as ${labels[newStatus] || newStatus}?`)) {
            return;
        }

        btn.disabled = true;
        const original = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(`/jobs/${jobId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: newStatus }),
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast('error', data.message || 'Failed to update status.', 'Error');
                }
                btn.disabled = false;
                btn.innerHTML = original;
                return;
            }

            if (typeof window.showToast === 'function') {
                window.showToast('success', `Application marked as ${labels[newStatus] || newStatus}.`, 'Updated');
            }

            // Simplest, robust: reload so server re-renders with the correct
            // actions / badges without keeping DOM state in sync manually.
            window.location.reload();
        })
        .catch(err => {
            console.error(err);
            if (typeof window.showToast === 'function') {
                window.showToast('error', 'Something went wrong.', 'Error');
            }
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });
});
</script>
@endpush