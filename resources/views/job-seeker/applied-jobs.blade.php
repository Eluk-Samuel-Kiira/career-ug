@extends('layouts.admin')

@section('title', 'My Applications')
@section('page_title', 'My Applications')

@section('content')
<div class="container py-6">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-transparent border-0 pt-5">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title fw-bold">My Applications</h3>
                    <p class="text-muted fs-7 mt-1">Jobs you have applied for</p>
                </div>
                <span class="badge badge-light-primary fs-6 py-2 px-4">{{ $total }} applications</span>
            </div>
        </div>
        <div class="card-body pt-0">
            @if(!empty($appliedJobs) && count($appliedJobs) > 0)
                <div class="d-flex flex-column gap-4">
                    @foreach($appliedJobs as $item)
                        @php
                            $job = $item['job'] ?? $item;
                            $jobTitle = $job['job_title'] ?? 'Job Title';
                            $companyName = $job['company']['name'] ?? 'Company';
                            $companyLogo = $job['company']['logo'] ?? null;
                            $location = $job['job_location']['name'] ?? $job['duty_station'] ?? 'Location not specified';
                            $salary = $job['formatted_salary'] ?? 'Negotiable';
                            $jobType = $job['job_type']['name'] ?? $job['employment_type'] ?? 'Full-time';
                            $slug = $job['slug'] ?? $job['id'];
                            $appliedAt = $item['applied_at'] ?? null;
                            $status = $item['status'] ?? 'applied';
                            
                            $statusColors = [
                                'applied' => 'primary',
                                'interviewing' => 'warning',
                                'hired' => 'success',
                                'rejected' => 'danger',
                            ];
                            $statusLabels = [
                                'applied' => 'Applied',
                                'interviewing' => 'Interviewing',
                                'hired' => 'Hired! 🎉',
                                'rejected' => 'Rejected',
                            ];
                            $statusColor = $statusColors[$status] ?? 'secondary';
                            $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                        @endphp
                        <div class="d-flex align-items-start justify-content-between p-4 bg-light rounded-3 border border-gray-200 hover-shadow transition">
                            <div class="d-flex gap-4 flex-grow-1">
                                <!-- Logo -->
                                <div class="jp-logo-sq jp-logo-sq-sm flex-shrink-0">
                                    @if($companyLogo)
                                        <img src="{{ $companyLogo }}" alt="{{ $companyName }}">
                                    @else
                                        {{ strtoupper(substr($companyName, 0, 2)) }}
                                    @endif
                                </div>
                                
                                <!-- Job Info -->
                                <div class="flex-grow-1 min-w-0">
                                    <a href="{{ route('jobs.show', $slug) }}" class="text-decoration-none">
                                        <h5 class="fw-bold text-gray-900 mb-1 hover-text-primary">{{ $jobTitle }}</h5>
                                    </a>
                                    <div class="fw-semibold text-gray-700 mb-2">{{ $companyName }}</div>
                                    <div class="d-flex flex-wrap gap-3 text-muted fs-7">
                                        <span><i class="bi bi-geo-alt me-1"></i>{{ $location }}</span>
                                        <span><i class="bi bi-briefcase me-1"></i>{{ $jobType }}</span>
                                        <span class="text-success fw-bold">{{ $salary }}</span>
                                        @if($appliedAt)
                                            <span><i class="bi bi-clock me-1"></i>Applied {{ \Carbon\Carbon::parse($appliedAt)->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status & Actions -->
                            <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0 ms-3">
                                <span class="badge badge-light-{{ $statusColor }} fs-7 py-2 px-3">
                                    <i class="bi bi-{{ $status === 'hired' ? 'check-circle' : ($status === 'rejected' ? 'x-circle' : 'clock') }} me-1"></i>
                                    {{ $statusLabel }}
                                </span>
                                <a href="{{ route('jobs.show', $slug) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View Job
                                </a>
                            </div>
                        </div>
                    @endforeach
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
    </div>
</div>

<style>
.hover-shadow:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
.hover-text-primary:hover {
    color: var(--jp-teal) !important;
}
.transition {
    transition: all 0.3s ease;
}
</style>
@endsection