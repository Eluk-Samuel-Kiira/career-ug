@extends('layouts.admin')

@section('title', 'Saved Jobs')
@section('page_title', 'Saved Jobs')

@section('content')
<div class="container py-6">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-transparent border-0 pt-5">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title fw-bold">Saved Jobs</h3>
                    <p class="text-muted fs-7 mt-1">Jobs you have saved for later</p>
                </div>
                <span class="badge badge-light-primary fs-6 py-2 px-4">{{ $total }} saved</span>
            </div>
        </div>
        <div class="card-body pt-0">
            @if(!empty($savedJobs) && count($savedJobs) > 0)
                <div class="d-flex flex-column gap-4">
                    @foreach($savedJobs as $job)
                        @php
                            $jobTitle = $job['job_title'] ?? 'Job Title';
                            $companyName = $job['company']['name'] ?? 'Company';
                            $companyLogo = $job['company']['logo'] ?? null;
                            $location = $job['job_location']['name'] ?? $job['duty_station'] ?? 'Location not specified';
                            $salary = $job['formatted_salary'] ?? 'Negotiable';
                            $jobType = $job['job_type']['name'] ?? $job['employment_type'] ?? 'Full-time';
                            $slug = $job['slug'] ?? $job['id'];
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
                                        @if(!empty($job['is_featured']))
                                            <span class="badge badge-light-warning">Featured</span>
                                        @endif
                                        @if(!empty($job['is_urgent']))
                                            <span class="badge badge-light-danger">Urgent</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0 ms-3">
                                <a href="{{ route('jobs.show', $slug) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger unsave-job-btn" 
                                        data-job-id="{{ $job['id'] }}"
                                        data-job-title="{{ $jobTitle }}">
                                    <i class="bi bi-heart-slash me-1"></i> Unsave
                                </button>
                            </div>
                        </div>
                    @endforeach
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // Unsave Job
    document.querySelectorAll('.unsave-job-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.dataset.jobId;
            const jobTitle = this.dataset.jobTitle;
            
            if (!confirm(`Remove "${jobTitle}" from saved jobs?`)) {
                return;
            }
            
            const originalText = this.innerHTML;
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
                    // Remove the job card with animation
                    const card = this.closest('.d-flex.align-items-start');
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        card.remove();
                        // Update count if any
                        const countBadge = document.querySelector('.badge');
                        if (countBadge) {
                            const currentCount = parseInt(countBadge.textContent);
                            if (currentCount > 0) {
                                countBadge.textContent = currentCount - 1;
                            }
                        }
                        // Show empty state if no jobs left
                        const remainingCards = document.querySelectorAll('.d-flex.align-items-start.p-4');
                        if (remainingCards.length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast('error', data.message || 'Failed to unsave job.', 'Error');
                    }
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('error', 'Something went wrong. Please try again.', 'Error');
                }
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });
});
</script>
@endpush