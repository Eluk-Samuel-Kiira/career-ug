@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="row g-6 g-xl-9">
    <!-- Welcome Card -->
    <div class="col-12">
        <div class="card card-flush p-6">
            <div class="d-flex align-items-center">
                <div class="symbol symbol-60px me-5">
                    <img src="{{ $user['avatar'] ?? asset('assets/media/avatars/300-1.jpg') }}" alt="Profile" />
                </div>
                <div>
                    <h1 class="fw-bold fs-2x mb-2">
                        Welcome back, {{ $user['first_name'] ?? 'User' }}!
                    </h1>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge badge-light-{{ $role === 'employer' ? 'primary' : 'success' }} fs-6 py-2 px-4">
                            <i class="ki-duotone ki-{{ $role === 'employer' ? 'briefcase' : 'profile-circle' }} fs-4 me-2"></i>
                            {{ ucfirst($role) }}
                        </span>
                        <span class="text-muted">{{ $user['email'] ?? '' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-flush">
            <div class="card-body d-flex align-items-center py-6">
                <div class="d-flex flex-column flex-grow-1">
                    <span class="fw-semibold text-gray-600 fs-7">Total Views</span>
                    <span class="fw-bold fs-2x text-gray-900">15,234</span>
                </div>
                <span class="badge badge-light-success fs-2 fw-bold p-3">
                    <i class="ki-duotone ki-arrow-up fs-1"></i> 12%
                </span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-flush">
            <div class="card-body d-flex align-items-center py-6">
                <div class="d-flex flex-column flex-grow-1">
                    <span class="fw-semibold text-gray-600 fs-7">Applications</span>
                    <span class="fw-bold fs-2x text-gray-900">842</span>
                </div>
                <span class="badge badge-light-primary fs-2 fw-bold p-3">
                    <i class="ki-duotone ki-arrow-up fs-1"></i> 8%
                </span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-flush">
            <div class="card-body d-flex align-items-center py-6">
                <div class="d-flex flex-column flex-grow-1">
                    <span class="fw-semibold text-gray-600 fs-7">Messages</span>
                    <span class="fw-bold fs-2x text-gray-900">47</span>
                </div>
                <span class="badge badge-light-warning fs-2 fw-bold p-3">
                    <i class="ki-duotone ki-arrow-down fs-1"></i> 3%
                </span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-flush">
            <div class="card-body d-flex align-items-center py-6">
                <div class="d-flex flex-column flex-grow-1">
                    <span class="fw-semibold text-gray-600 fs-7">Saved Jobs</span>
                    <span class="fw-bold fs-2x text-gray-900">128</span>
                </div>
                <span class="badge badge-light-info fs-2 fw-bold p-3">
                    <i class="ki-duotone ki-arrow-up fs-1"></i> 5%
                </span>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-xl-6">
        <div class="card card-flush">
            <div class="card-header">
                <h3 class="card-title">Recent Activity</h3>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                        <span class="badge badge-success p-3">✓</span>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Application submitted</div>
                            <div class="text-muted fs-7">Software Engineer at Google</div>
                        </div>
                        <span class="text-muted fs-7">2 hours ago</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                        <span class="badge badge-primary p-3">⭐</span>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Job saved</div>
                            <div class="text-muted fs-7">Product Manager at Microsoft</div>
                        </div>
                        <span class="text-muted fs-7">5 hours ago</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                        <span class="badge badge-info p-3">💬</span>
                        <div class="flex-grow-1">
                            <div class="fw-bold">New message</div>
                            <div class="text-muted fs-7">HR from Amazon replied</div>
                        </div>
                        <span class="text-muted fs-7">1 day ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-xl-6">
        <div class="card card-flush">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body pt-0">
                <div class="row g-4">
                    <div class="col-6">
                        <a href="#" class="btn btn-light-primary w-100 py-4">
                            <i class="ki-duotone ki-pencil fs-2x mb-2 d-block"></i>
                            <span class="fw-bold">Post a Job</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#" class="btn btn-light-success w-100 py-4">
                            <i class="ki-duotone ki-search fs-2x mb-2 d-block"></i>
                            <span class="fw-bold">Find Jobs</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#" class="btn btn-light-warning w-100 py-4">
                            <i class="ki-duotone ki-profile-circle fs-2x mb-2 d-block"></i>
                            <span class="fw-bold">Update Profile</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#" class="btn btn-light-info w-100 py-4">
                            <i class="ki-duotone ki-message-text-2 fs-2x mb-2 d-block"></i>
                            <span class="fw-bold">Messages</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Dashboard specific scripts
    document.addEventListener('DOMContentLoaded', function() {
        // Any dashboard-specific JavaScript
        console.log('Dashboard loaded');
    });
</script>
@endpush