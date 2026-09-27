@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

@if($needsOnboarding ?? false)
    {{-- Onboarding nudge --}}
    <div class="card card-flush py-15">
        <div class="card-body text-center">
            <i class="ki-duotone ki-rocket fs-3x text-primary mb-4 d-block">
                <span class="path1"></span><span class="path2"></span>
            </i>
            <h2 class="fw-bold mb-2">Welcome, {{ $user['first_name'] ?? 'there' }}!</h2>
            <p class="text-muted mb-6">Let's get your account set up.</p>

            @if($role === 'employer')
                <a href="{{ route('employer.profile.index') }}" class="btn btn-primary btn-lg">
                    Complete Your Company Profile
                </a>
            @else
                <a href="{{ route('cv.edit') }}" class="btn btn-primary btn-lg">
                    Upload Your CV
                </a>
            @endif
        </div>
    </div>

@else

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- WELCOME CARD                                           --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="card card-flush p-6 mb-6">
        <div class="d-flex align-items-center flex-wrap gap-4">
            <div class="symbol symbol-60px">
                <img src="{{ $user['avatar'] ?? asset('assets/media/avatars/300-1.jpg') }}" alt="Profile" />
            </div>
            <div class="flex-grow-1">
                <h1 class="fw-bold fs-2x mb-2">
                    Welcome back, {{ $user['first_name'] ?? 'User' }}!
                </h1>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="badge badge-light-{{ $role === 'employer' ? 'primary' : 'success' }} fs-6 py-2 px-4">
                        <i class="ki-duotone ki-{{ $role === 'employer' ? 'briefcase' : 'profile-circle' }} fs-4 me-2">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        {{ ucfirst($role) }}
                    </span>
                    <span class="text-muted">{{ $user['email'] ?? '' }}</span>

                    @if($role === 'employer' && !empty($stats['tier_label']))
                        <span class="badge badge-light-{{ ($stats['tier'] ?? 'starter') === 'trusted' ? 'success' : (($stats['tier'] ?? 'starter') === 'verified' ? 'primary' : 'warning') }} fs-7">
                            {{ $stats['tier_label'] }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($role === 'employer')
        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- EMPLOYER STATS                                         --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <div class="row g-5 g-xl-8 mb-6">
            {{-- Active Jobs --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-duotone ki-briefcase fs-2x text-primary">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Active Jobs</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['active_jobs'] }}</span>
                            @if($stats['job_limit'] !== null)
                                <span class="text-muted fs-8">
                                    {{ $stats['job_slots_remaining'] }} of {{ $stats['job_limit'] }} slots left
                                </span>
                            @else
                                <span class="text-muted fs-8">Unlimited slots</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Applications This Month --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-user-tick fs-2x text-success">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Applications This Month</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['applications_this_month'] }}</span>
                            @if($stats['applications_today'] > 0)
                                <span class="text-success fs-8">+{{ $stats['applications_today'] }} today</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Action --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-warning">
                                <i class="ki-duotone ki-time fs-2x text-warning">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">New (Unscreened)</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['new_applications'] }}</span>
                            <span class="text-muted fs-8">{{ $stats['shortlisted'] }} shortlisted</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Profile Completion --}}
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-info">
                                <i class="ki-duotone ki-check-circle fs-2x text-info">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <span class="fw-semibold text-gray-600 fs-7">Profile</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['profile_completion'] }}%</span>
                            <div class="progress h-4px mt-2" style="min-width:80px;">
                                <div class="progress-bar bg-info" style="width: {{ $stats['profile_completion'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compliance banner --}}
        @if($stats['compliance_status'] !== 'verified')
            <div class="alert alert-light-{{ $stats['compliance_status'] === 'rejected' ? 'danger' : ($stats['compliance_status'] === 'submitted' ? 'info' : 'warning') }} d-flex align-items-center mb-6">
                <i class="ki-duotone ki-shield-tick fs-2x me-4">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <div class="flex-grow-1">
                    <div class="fw-bold">
                        @if($stats['compliance_status'] === 'submitted')
                            Compliance under review
                        @elseif($stats['compliance_status'] === 'rejected')
                            Compliance rejected — needs attention
                        @else
                            Complete your compliance documents
                        @endif
                    </div>
                    <div class="text-muted fs-7">
                        @if($stats['compliance_status'] === 'submitted')
                            Our team is reviewing your documents. We'll notify you shortly.
                        @else
                            Upload your compliance documents to unlock the Verified badge and more job slots.
                        @endif
                    </div>
                </div>
                <a href="{{ route('employer.compliance.index') }}" class="btn btn-sm btn-light-primary">
                    Go to Compliance
                </a>
            </div>
        @endif

    @else
        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- SEEKER STATS                                           --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <div class="row g-5 g-xl-8 mb-6">
            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-duotone ki-briefcase fs-2x text-primary">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Applications</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['applied_count'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-warning">
                                <i class="ki-duotone ki-calendar-tick fs-2x text-warning">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Interviews</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['interview_count'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-check-circle fs-2x text-success">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Hired</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['hired_count'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-flush h-100">
                    <div class="card-body d-flex align-items-center py-6">
                        <div class="symbol symbol-50px me-5">
                            <span class="symbol-label bg-light-info">
                                <i class="ki-duotone ki-bookmark fs-2x text-info">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-600 fs-7">Saved Jobs</span>
                            <span class="fw-bold fs-2x text-gray-900">{{ $stats['saved_count'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- RECENT ACTIVITY + QUICK ACTIONS                        --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="row g-6">
        {{-- Recent Activity --}}
        <div class="col-xl-7">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                </div>
                <div class="card-body pt-0">
                    @if(empty($recentActivity))
                        <div class="text-center py-8 text-muted">
                            <i class="ki-duotone ki-time fs-3x text-muted mb-3 d-block">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                            <p class="text-muted">
                                {{ $role === 'employer' ? 'No activity yet — post a job to get started.' : 'No activity yet — apply to jobs to see updates here.' }}
                            </p>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($recentActivity as $activity)
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                                    <div class="symbol symbol-40px">
                                        <span class="symbol-label bg-light-{{ $activity['color'] }}">
                                            <i class="ki-duotone {{ $activity['icon'] }} fs-4 text-{{ $activity['color'] }}">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold fs-7">{{ $activity['title'] }}</div>
                                        <div class="text-muted fs-7">{{ $activity['subtitle'] }}</div>
                                    </div>
                                    <span class="text-muted fs-8 text-nowrap">
                                        {{ \Carbon\Carbon::parse($activity['at'])->diffForHumans() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-xl-5">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3">
                        @if($role === 'employer')
                            <div class="col-6">
                                <a href="{{ route('employer.jobs.create') }}" class="btn btn-light-primary w-100 py-4">
                                    <i class="ki-duotone ki-plus-square fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                    </i>
                                    <span class="fw-bold">Post a Job</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('employer.jobs.index') }}" class="btn btn-light-success w-100 py-4">
                                    <i class="ki-duotone ki-briefcase fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">My Jobs</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('employer.analytics.index') }}" class="btn btn-light-warning w-100 py-4">
                                    <i class="ki-duotone ki-chart-simple fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                    </i>
                                    <span class="fw-bold">Analytics</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('employer.profile.index') }}" class="btn btn-light-info w-100 py-4">
                                    <i class="ki-duotone ki-building fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">Company Profile</span>
                                </a>
                            </div>
                        @else
                            <div class="col-6">
                                <a href="{{ route('jobs.index') }}" class="btn btn-light-primary w-100 py-4">
                                    <i class="ki-duotone ki-search fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">Find Jobs</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('cv.edit') }}" class="btn btn-light-success w-100 py-4">
                                    <i class="ki-duotone ki-file-up fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">Upload CV</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('jobs.applied') }}" class="btn btn-light-warning w-100 py-4">
                                    <i class="ki-duotone ki-briefcase fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">My Applications</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('jobs.saved') }}" class="btn btn-light-info w-100 py-4">
                                    <i class="ki-duotone ki-bookmark fs-2x mb-2 d-block">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-bold">Saved Jobs</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection